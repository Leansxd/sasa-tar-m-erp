<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\DailyWorkSheet;
use App\Models\Personnel;
use App\Models\ProductionLocation;
use App\Models\Tenant;
use App\Models\UnitDefinition;
use App\Models\User;
use App\Services\CaptchaService;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SuperAdminController extends Controller
{
    private function checkMasterAuth(Request $request): bool
    {
        if ($request->session()->get('master_control_authenticated') !== true) {
            return false;
        }

        $lastActivity = $request->session()->get('master_control_last_activity');
        if ($lastActivity && (time() - $lastActivity > 1800)) {
            $request->session()->forget(['master_control_authenticated', 'master_control_fingerprint', 'master_control_last_activity']);
            return false;
        }

        $fingerprint = $request->session()->get('master_control_fingerprint');
        $currentFingerprint = hash('sha256', $request->ip() . '|' . ($request->userAgent() ?? ''));
        if ($fingerprint !== $currentFingerprint) {
            $request->session()->forget(['master_control_authenticated', 'master_control_fingerprint', 'master_control_last_activity']);
            return false;
        }

        $request->session()->put('master_control_last_activity', time());
        return true;
    }

    private function checkIpRestriction(Request $request): void
    {
        $allowedIps = env('MASTER_ALLOWED_IPS');
        if (!empty($allowedIps)) {
            $ipList = array_map('trim', explode(',', $allowedIps));
            if (!in_array($request->ip(), $ipList)) {
                Log::warning('Master control IP access rejected', ['ip' => $request->ip()]);
                abort(403, 'Bu IP adresinden Master panele erişim yetkiniz bulunmuyor.');
            }
        }
    }

    public function refreshCaptcha(): JsonResponse
    {
        return response()->json(CaptchaService::generate());
    }

    public function index(Request $request): Response
    {
        $this->checkIpRestriction($request);

        if (!$this->checkMasterAuth($request)) {
            $secret = env('MASTER_2FA_SECRET', 'SASASUPERADMIN26');
            $qrUrl = \App\Services\TotpService::getQrCodeUrl('SASA ERP Master', 'SuperAdmin', $secret);
            $captcha = CaptchaService::generate();

            return Inertia::render('SuperAdmin/Login', [
                'qrUrl' => $qrUrl,
                'secretKey' => $secret,
                'captcha' => $captcha,
            ]);
        }

        $locationStats = ProductionLocation::withoutGlobalScope('tenant')
            ->select('tenant_id', DB::raw('COUNT(*) as location_count'), DB::raw('SUM(total_area_dekar) as total_dekar'))
            ->groupBy('tenant_id')
            ->get()
            ->keyBy('tenant_id');

        $workSheetStats = DailyWorkSheet::withoutGlobalScope('tenant')
            ->select('tenant_id', DB::raw('COUNT(*) as worksheet_count'))
            ->groupBy('tenant_id')
            ->get()
            ->keyBy('tenant_id');

        $tenants = Tenant::with(['users'])->latest()->get()->map(function ($tenant) use ($locationStats, $workSheetStats) {
            $locStat = $locationStats->get($tenant->id);
            $sheetStat = $workSheetStats->get($tenant->id);

            $userCount = $tenant->users->count();
            $locationCount = $locStat ? (int) $locStat->location_count : 0;
            $workSheetCount = $sheetStat ? (int) $sheetStat->worksheet_count : 0;
            $totalDekar = $locStat ? (float) $locStat->total_dekar : 0.0;

            $ownerUser = $tenant->users->firstWhere('email', $tenant->owner_email) 
                ?? $tenant->users->firstWhere('is_admin', true) 
                ?? $tenant->users->first();

            $expiresAt = $tenant->expires_at;
            $isExpired = $expiresAt ? $expiresAt->isPast() : false;
            $remainingDays = $expiresAt ? (int) ceil(now()->diffInDays($expiresAt, false)) : null;

            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'plan' => $tenant->plan ?? 'enterprise',
                'is_active' => (bool) $tenant->is_active,
                'created_at' => $tenant->created_at?->format('d.m.Y H:i'),
                'expires_at' => $expiresAt?->format('d.m.Y'),
                'expires_at_raw' => $expiresAt?->format('Y-m-d'),
                'is_expired' => $isExpired,
                'remaining_days' => $remainingDays,
                'owner' => $ownerUser ? [
                    'id' => $ownerUser->id,
                    'name' => $ownerUser->name,
                    'email' => $ownerUser->email,
                ] : [
                    'id' => null,
                    'name' => $tenant->owner_name ?? 'Kurucu',
                    'email' => $tenant->owner_email ?? '-',
                ],
                'stats' => [
                    'users_count' => $userCount,
                    'locations_count' => $locationCount,
                    'work_sheets_count' => $workSheetCount,
                    'total_dekar' => round($totalDekar, 1),
                ]
            ];
        });

        $totalTenants = $tenants->count();
        $activeTenants = $tenants->where('is_active', true)->count();
        $totalUsers = User::count();
        $totalAreaDekar = round((float) ProductionLocation::withoutGlobalScope('tenant')->sum('total_area_dekar'), 1);

        return Inertia::render('SuperAdmin/Index', [
            'tenants' => $tenants,
            'summaryStats' => [
                'total_tenants' => $totalTenants,
                'active_tenants' => $activeTenants,
                'total_users' => $totalUsers,
                'total_area_dekar' => $totalAreaDekar,
            ],
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $this->checkIpRestriction($request);

        $throttleKey = 'master_login_throttle:' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            Log::warning('Master control rate limit exceeded', ['ip' => $request->ip()]);
            return redirect()->back()->withErrors([
                'security' => "Çok fazla başarısız deneme yapıldı. Lütfen {$seconds} saniye sonra tekrar deneyin."
            ]);
        }

        $validated = $request->validate([
            'master_key' => 'required|string',
            'totp_code' => 'required|string|size:6',
            'captcha_token' => 'required|string',
            'captcha_answer' => 'required|string',
        ]);

        if (!CaptchaService::verify($validated['captcha_answer'], $validated['captcha_token'])) {
            RateLimiter::hit($throttleKey, 900);
            return redirect()->back()->withErrors(['captcha_answer' => 'Güvenlik doğrulaması (Captcha) hatalı veya süresi doldu.']);
        }

        $validMasterKey = env('MASTER_CONTROL_KEY', 'SasaMaster2026!*');
        $secret = env('MASTER_2FA_SECRET', 'SASASUPERADMIN26');

        $isKeyValid = hash_equals((string) $validMasterKey, (string) $validated['master_key']);

        if (!$isKeyValid) {
            RateLimiter::hit($throttleKey, 900);
            Log::warning('Master control invalid key attempt', ['ip' => $request->ip()]);
            return redirect()->back()->withErrors(['master_key' => 'Geçersiz Master Güvenlik Anahtarı.']);
        }

        $replayKey = 'master_totp_used_' . md5($validated['totp_code'] . date('YmdHi'));
        if (Cache::has($replayKey)) {
            RateLimiter::hit($throttleKey, 900);
            return redirect()->back()->withErrors(['totp_code' => 'Bu Authenticator kodu daha önce kullanıldı. Lütfen yeni kodu bekleyin.']);
        }

        $isTotpValid = \App\Services\TotpService::verifyCode($secret, $validated['totp_code']);

        if (!$isTotpValid) {
            RateLimiter::hit($throttleKey, 900);
            Log::warning('Master control invalid totp attempt', ['ip' => $request->ip()]);
            return redirect()->back()->withErrors(['totp_code' => 'Geçersiz veya süresi dolmuş Authenticator kodu (6 hane).']);
        }

        RateLimiter::clear($throttleKey);
        Cache::put($replayKey, true, 90);

        $request->session()->put('master_control_authenticated', true);
        $request->session()->put('master_control_fingerprint', hash('sha256', $request->ip() . '|' . ($request->userAgent() ?? '')));
        $request->session()->put('master_control_last_activity', time());

        Log::info('Master control successful login', ['ip' => $request->ip()]);

        return redirect()->route('master-control.index')->with('success', 'Master kontrol erişimi doğrulandı.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['master_control_authenticated', 'master_control_fingerprint', 'master_control_last_activity']);
        return redirect()->route('master-control.index')->with('success', 'Master oturumu kapatıldı.');
    }

    public function storeTenant(Request $request): RedirectResponse
    {
        $this->checkIpRestriction($request);

        if (!$this->checkMasterAuth($request)) {
            abort(403);
        }

        $validated = $request->validate([
            'tenant_name' => 'required|string|max:255',
            'plan' => 'required|in:starter,growth,enterprise,unlimited',
            'owner_name' => 'required|string|max:150',
            'owner_email' => 'required|email|max:255|unique:users,email',
            'owner_password' => 'required|string|min:4',
            'license_months' => 'nullable|string',
        ]);

        $expiresAt = null;
        if (!empty($validated['license_months']) && $validated['license_months'] !== 'unlimited') {
            $months = (int) $validated['license_months'];
            if ($months > 0) {
                $expiresAt = now()->addMonths($months)->endOfDay();
            }
        }

        DB::transaction(function () use ($validated, $expiresAt) {
            $baseSlug = Str::slug($validated['tenant_name']) ?: 'tenant';
            $slug = $baseSlug;
            $counter = 1;
            while (Tenant::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $tenant = Tenant::create([
                'name' => $validated['tenant_name'],
                'slug' => $slug,
                'owner_name' => $validated['owner_name'],
                'owner_email' => $validated['owner_email'],
                'plan' => $validated['plan'],
                'is_active' => true,
                'expires_at' => $expiresAt,
            ]);

            $ownerUser = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => $validated['owner_password'],
                'email_verified_at' => now(),
                'is_admin' => true,
                'is_super_admin' => false,
            ]);

            $nameParts = explode(' ', $validated['owner_name'], 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? 'Yönetici';

            Personnel::withoutGlobalScope('tenant')->create([
                'tenant_id' => $tenant->id,
                'user_id' => $ownerUser->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => null,
                'role_title' => 'Şirket Kurucusu / Genel Müdür',
                'company_ids' => [],
                'permissions' => ['tesis', 'uretim', 'teknik', 'operasyon', 'raporlar', 'tanimlamalar'],
                'can_enter_backdated_data' => true,
                'is_active' => true,
            ]);
        });

        return redirect()->back()->with('success', "'{$validated['tenant_name']}' müşterisi ve bağımsız ERP hesabı ({$validated['owner_email']}) oluşturuldu.");
    }

    public function toggleTenantStatus(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->checkIpRestriction($request);

        if (!$this->checkMasterAuth($request)) {
            abort(403);
        }

        $tenant->update([
            'is_active' => !$tenant->is_active
        ]);

        $statusText = $tenant->is_active ? 'aktif edildi' : 'donduruldu / erişimi durduruldu';
        return redirect()->back()->with('success', "'{$tenant->name}' müşterisinin lisansı {$statusText}.");
    }

    public function updateTenantAdmin(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->checkIpRestriction($request);

        if (!$this->checkMasterAuth($request)) {
            abort(403);
        }

        $validated = $request->validate([
            'owner_email' => 'required|email|max:255',
            'owner_password' => 'nullable|string|min:4',
        ]);

        $owner = User::where('tenant_id', $tenant->id)->where('email', $tenant->owner_email)->first()
            ?? User::where('tenant_id', $tenant->id)->where('is_admin', true)->first()
            ?? User::where('tenant_id', $tenant->id)->first();

        if (!$owner) {
            $owner = User::create([
                'tenant_id' => $tenant->id,
                'name' => $tenant->owner_name ?? 'Yönetici',
                'email' => $validated['owner_email'],
                'password' => !empty($validated['owner_password']) ? $validated['owner_password'] : '123456',
                'is_admin' => true,
            ]);
        } else {
            $existingWithEmail = User::where('email', $validated['owner_email'])->where('id', '!=', $owner->id)->first();
            if ($existingWithEmail) {
                return redirect()->back()->withErrors(['owner_email' => 'Bu e-posta adresi başka bir kullanıcı tarafından kullanılmaktadır.']);
            }

            $owner->email = $validated['owner_email'];
            if (!empty($validated['owner_password'])) {
                $owner->password = $validated['owner_password'];
            }
            $owner->save();
        }

        $tenant->update([
            'owner_email' => $validated['owner_email']
        ]);

        return redirect()->back()->with('success', "'{$tenant->name}' müşteri yöneticisinin giriş bilgileri güncellendi.");
    }

    public function destroyTenant(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->checkIpRestriction($request);

        if (!$this->checkMasterAuth($request)) {
            abort(403);
        }

        if ($tenant->id === 1) {
            return redirect()->back()->withErrors(['error' => 'Ana sistem hesabı (#1) silinemez.']);
        }

        DB::transaction(function () use ($tenant) {
            $tenantTables = [
                'gunluk_form_hasat_kalemleri',
                'gunluk_form_isci_atamalari',
                'gunluk_form_cavuslar',
                'gunluk_isci_formlari',
                'musteri_siparis_kalemleri',
                'musteri_siparisleri',
                'sevkiyatlar',
                'gubre_tank_loglari',
                'gubre_tank_icerikleri',
                'gubre_tanklari',
                'gubre_uygulamalari',
                'gubre_receteleri',
                'ilac_recete_icerikleri',
                'ilac_receteleri',
                'ilac_uygulamalari',
                'sulama_program_vanalari',
                'sulama_programlari',
                'kaynak_suyu_kontrolleri',
                'aritma_suyu_kontrolleri',
                'su_analiz_loglari',
                'is_plani_notlari',
                'is_planlari',
                'uretim_vanalari',
                'uretim_bolumleri',
                'uretim_yerleri',
                'filtreler',
                'su_kaynaklari',
                'paketleme_tanimlari',
                'birim_tanimlari',
                'is_tanimlari',
                'teslimat_sekilleri',
                'urun_alt_tipleri',
                'urunler',
                'hal_piyasa_fiyatlari',
                'cari_taraflar',
                'yemek_tedarikcileri',
                'isciler',
                'cavuslar',
                'personeller',
                'firmalar',
                'users'
            ];

            foreach ($tenantTables as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                    DB::table($table)->where('tenant_id', $tenant->id)->delete();
                }
            }

            $tenant->delete();
        });

        return redirect()->back()->with('success', "'{$tenant->name}' müşterisi ve bağlı tüm veriler başarıyla silindi.");
    }

    public function impersonate(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->checkIpRestriction($request);

        if (!$this->checkMasterAuth($request)) {
            abort(403);
        }

        $user = User::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenant->id)
            ->where('is_admin', true)
            ->first()
            ?? User::withoutGlobalScope('tenant')->where('tenant_id', $tenant->id)->first();

        if (!$user) {
            return redirect()->back()->withErrors(['error' => "'{$tenant->name}' için giriş yapılabilecek kullanıcı bulunamadı."]);
        }

        Auth::login($user);

        $request->session()->put('impersonated_by_master', true);
        $request->session()->put('impersonated_tenant_id', $tenant->id);
        $request->session()->put('impersonated_tenant_name', $tenant->name);

        return redirect()->route('dashboard')->with('success', "'{$tenant->name}' ERP paneline Süper Yönetici olarak geçiş yapıldı.");
    }

    public function stopImpersonate(Request $request): RedirectResponse
    {
        $request->session()->forget(['impersonated_by_master', 'impersonated_tenant_id', 'impersonated_tenant_name']);
        Auth::guard('web')->logout();

        $request->session()->put('master_control_authenticated', true);
        $request->session()->put('master_control_fingerprint', hash('sha256', $request->ip() . '|' . ($request->userAgent() ?? '')));
        $request->session()->put('master_control_last_activity', time());

        return redirect()->route('master-control.index')->with('success', 'Master Yönetim Paneline geri dönüldü.');
    }

    public function updateTenantLicense(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->checkIpRestriction($request);

        if (!$this->checkMasterAuth($request)) {
            abort(403);
        }

        $validated = $request->validate([
            'action' => 'required|in:unlimited,add_days,custom_date',
            'days' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ]);

        if ($validated['action'] === 'unlimited') {
            $tenant->update(['expires_at' => null]);
            $msg = "'{$tenant->name}' lisansı sınırsız (ömür boyu) olarak ayarlandı.";
        } elseif ($validated['action'] === 'add_days' && !empty($validated['days'])) {
            $currentExpiry = ($tenant->expires_at && $tenant->expires_at->isFuture()) 
                ? $tenant->expires_at 
                : now();
            $newExpiry = $currentExpiry->copy()->addDays((int) $validated['days']);
            $tenant->update(['expires_at' => $newExpiry]);
            $msg = "'{$tenant->name}' lisansı +{$validated['days']} gün uzatıldı (Yeni Bitiş: {$newExpiry->format('d.m.Y')}).";
        } elseif ($validated['action'] === 'custom_date' && !empty($validated['expires_at'])) {
            $newExpiry = \Carbon\Carbon::parse($validated['expires_at'])->endOfDay();
            $tenant->update(['expires_at' => $newExpiry]);
            $msg = "'{$tenant->name}' lisans bitiş tarihi {$newExpiry->format('d.m.Y')} olarak güncellendi.";
        }

        return redirect()->back()->with('success', $msg ?? 'Lisans güncellendi.');
    }

    public function backupTenant(Request $request, Tenant $tenant)
    {
        $this->checkIpRestriction($request);

        if (!$this->checkMasterAuth($request)) {
            abort(403);
        }

        $tenantTables = [
            'firmalar',
            'personeller',
            'uretim_yerleri',
            'urunler',
            'is_tanimlari',
            'birim_tanimlari',
            'paketleme_tanimlari',
            'cavuslar',
            'isciler',
            'yemek_tedarikcileri',
            'gubre_receteleri',
            'ilac_receteleri',
            'su_kaynaklari',
            'filtreler',
            'cari_taraflar',
            'teslimat_sekilleri',
            'gunluk_isci_formlari',
            'gunluk_form_cavuslar',
            'gunluk_form_isci_atamalari',
            'gunluk_form_hasat_kalemleri',
            'musteri_siparisleri',
            'musteri_siparis_kalemleri',
            'sevkiyatlar',
            'is_planlari',
            'hal_piyasa_fiyatlari',
            'gubre_uygulamalari',
            'sulama_programlari',
            'ilaclama_uygulamalari',
            'su_analiz_loglari',
            'kaynak_suyu_kontrolleri',
            'aritma_suyu_kontrolleri',
            'users',
        ];

        $data = [];
        $totalRows = 0;

        foreach ($tenantTables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                $rows = DB::table($table)->where('tenant_id', $tenant->id)->get();
                $data[$table] = $rows;
                $totalRows += $rows->count();
            }
        }

        $backupPayload = [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'plan' => $tenant->plan,
                'owner_name' => $tenant->owner_name,
                'owner_email' => $tenant->owner_email,
                'expires_at' => $tenant->expires_at?->toIso8601String(),
            ],
            'metadata' => [
                'exported_at' => now()->toIso8601String(),
                'system' => 'SASA ERP Enterprise SaaS',
                'version' => '1.0',
                'total_records' => $totalRows,
            ],
            'tables' => $data,
        ];

        $filename = "sasa_backup_{$tenant->slug}_" . date('Y-m-d_His') . ".json";
        $jsonContent = json_encode($backupPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response()->streamDownload(function () use ($jsonContent) {
            echo $jsonContent;
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }
}
