<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        VerifyEmail::toMailUsing(function ($notifiable, $url) {
            $user = $notifiable->name;
            return (new MailMessage())
                ->subject(__(config('app.name').' - ACTIVA TU CUENTA'))
                ->action('Verify Email Address', $url)
                ->view('mails.activation-email', compact(['url', 'user']));
        });

        ResetPassword::toMailUsing(function ($notifiable, $token) {

            $user = $notifiable->name;
            $url = env('APP_URL_FRONT')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
            return (new MailMessage())
                ->subject(__(config('app.name').' - RESTABLECER CONTRASEÑA'))
                ->view('mails.password-reset', compact(['url', 'user']));
        });
    }
}
