<?php

namespace App\Http\Middleware;

use App\Models\Personnel;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();
        $personnel = null;
        $permissions = [];
        $isAdmin = false;

        if ($user) {
            if ($user->tenant_id) {
                $tenant = Tenant::find($user->tenant_id);
                $isImpersonating = (bool) $request->session()->get('impersonated_by_master');

                if ($tenant && !$isImpersonating) {
                    if (!$tenant->is_active) {
                        Auth::guard('web')->logout();
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();
                        return [
                            ...parent::share($request),
                            'auth' => ['user' => null],
                            'flash' => ['error' => 'Şirketinizin erişim lisansı durdurulmuştur.'],
                        ];
                    }

                    if ($tenant->expires_at && $tenant->expires_at->isPast()) {
                        Auth::guard('web')->logout();
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();
                        return [
                            ...parent::share($request),
                            'auth' => ['user' => null],
                            'flash' => ['error' => 'Şirketinizin lisans kullanım süresi sona ermiştir.'],
                        ];
                    }
                }
            }

            $isAdmin = !!$user->is_admin;
            $personnel = Personnel::where('user_id', $user->id)->first();

            if ($isAdmin) {
                $permissions = ['tesis', 'uretim', 'teknik', 'operasyon', 'raporlar', 'tanimlamalar'];
            } else if ($personnel) {
                $permissions = $personnel->permissions ?? [];
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'is_admin' => $isAdmin,
                'personnel' => $personnel,
                'permissions' => $permissions,
            ],
            'is_impersonating' => (bool) $request->session()->get('impersonated_by_master'),
            'impersonated_tenant_name' => $request->session()->get('impersonated_tenant_name'),
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
            ],
        ];
    }
}
