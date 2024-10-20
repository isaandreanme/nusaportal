<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use TomatoPHP\FilamentUsers\Facades\FilamentUser;
use TomatoPHP\FilamentSettingsHub\Facades\FilamentSettingsHub;
use TomatoPHP\FilamentSettingsHub\Services\Contracts\SettingHold;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentUser::registerAction(\Filament\Actions\Action::make('update'));
        FilamentUser::registerCreateAction(\Filament\Actions\Action::make('update'));
        FilamentUser::registerEditAction(\Filament\Actions\Action::make('update'));
        FilamentUser::registerFormInput(\Filament\Forms\Components\TextInput::make('text'));
        FilamentUser::registerTableAction(\Filament\Tables\Actions\Action::make('update'));
        FilamentUser::registerTableColumn(\Filament\Tables\Columns\Column::make('text'));
        FilamentUser::registerTableFilter(\Filament\Tables\Filters\Filter::make('text'));

        // FilamentSettingsHub::register([
        //     SettingHold::make()
        //         ->order(2)
        //         ->label('Site Settings') // to translate label just use direct translation path like `messages.text.name`
        //         ->icon('heroicon-o-globe-alt')
        //         ->route('filament.admin.pages.site-settings') // use page / route
        //         ->page(\TomatoPHP\FilamentSettingsHub\Pages\SiteSettings::class) // use page / route
        //         ->description('Name, Logo, Site Profile') // to translate label just use direct translation path like `messages.text.name`
        //         ->group('General') // to translate label just use direct translation path like `messages.text.name`,
        // ]);
        // Menggunakan hook untuk memeriksa login dan melakukan redirect
        // Pastikan redirect hanya terjadi setelah login dan bukan pada setiap request
        Route::middleware(['auth'])->group(function () {
            Route::get('/home', function () {
                $user = Auth::user();

                // Cek session apakah redirect sudah dilakukan
                if (session('has_redirected', false) === false) {
                    // Jika user is_agency, redirect ke /admin/workers
                    if ($user->is_agency) {
                        session(['has_redirected' => true]);  // Set session
                        return redirect('/admin/workers');
                    }

                    // Jika user bukan is_admin dan bukan is_agency, redirect ke /admin/proses
                    if (!$user->is_admin && !$user->is_agency) {
                        session(['has_redirected' => true]);  // Set session
                        return redirect('/admin/proses');
                    }

                    // Jika user adalah is_admin, biarkan ke /admin
                    session(['has_redirected' => true]);  // Set session
                    return redirect('/admin');
                }

                // Jika sudah di-redirect, biarkan mereka tetap di halaman apapun yang diakses
                return view('home');
            });
        });
    }
}
