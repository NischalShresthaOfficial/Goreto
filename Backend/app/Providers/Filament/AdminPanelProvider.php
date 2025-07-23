<?php

namespace App\Providers\Filament;

use App\Filament\Resources\Pages\ActivityLog;
use App\Filament\Widgets\GroupsByUserBarChart;
use App\Filament\Widgets\GroupsByUserPieChart;
use App\Filament\Widgets\PostsByUserBarChart;
use App\Filament\Widgets\PostsByUserPieChart;
use App\Filament\Widgets\SubscriptionBarChart;
use App\Filament\Widgets\SubscriptionPieChart;
use App\Http\Middleware\SuperAdminMiddleware;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->font('Plus Jakarta Sans')
            ->brandLogoHeight('5.0rem')
            ->colors([
                'primary' => '#FFB23F',
                'secondary' => '#F7F8F9',
                'danger' => Color::Rose,
                'gray' => Color::Gray,
                'info' => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'critical' => Color::Red,
            ])
            ->brandLogo(asset('assets/logo.png'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                ActivityLog::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                GroupsByUserPieChart::class,
                GroupsByUserBarChart::class,
                SubscriptionPieChart::class,
                SubscriptionbarChart::class,
                PostsByUserPieChart::class,
                PostsByUserBarChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SuperAdminMiddleware::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
            ])
            ->authMiddleware([
                Authenticate::class,
                SuperAdminMiddleware::class,
            ]);
    }
}
