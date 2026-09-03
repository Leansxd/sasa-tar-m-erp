<?php

namespace App\Http\Middleware;

use App\Models\Personnel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePermission
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->is_admin) {
            return $next($request);
        }

        $personnel = Personnel::where('user_id', $user->id)->first();

        if (!$personnel || !$personnel->is_active) {
            abort(403, 'Hesabınız pasif durumdadır veya personel kaydınız bulunmamaktadır.');
        }

        $permissions = $personnel->permissions ?? [];

        if (!in_array($module, $permissions)) {
            abort(403, 'Bu modüle veya sayfaya erişim yetkiniz bulunmamaktadır.');
        }

        return $next($request);
    }
}
