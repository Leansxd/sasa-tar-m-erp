<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $personnel = null;
        $permissions = [];
        $isAdmin = false;

        if ($user) {
            $isAdmin = !!$user->is_admin;
            $personnel = \App\Models\Personnel::where('user_id', $user->id)->first();

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
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
