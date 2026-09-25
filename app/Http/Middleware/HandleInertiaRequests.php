<?php

namespace App\Http\Middleware;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'app' => [
                'name' => config('app.name'),
            ],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->getKey(),
                    'name' => $user->name,
                    'role' => [
                        'value' => $user->role->value,
                        'label' => $user->role->label(),
                    ],
                ] : null,
            ],
            'can' => [
                'viewAnyProducts' => $user?->can('viewAny', Product::class) ?? false,
                'viewAnyPurchases' => $user?->can('viewAny', Purchase::class) ?? false,
                'viewAnySales' => $user?->can('viewAny', Sale::class) ?? false,
                'viewReports' => $user?->can('viewReports') ?? false,
            ],
            'flash' => $request->hasSession() ? [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ] : [],
        ];
    }
}
