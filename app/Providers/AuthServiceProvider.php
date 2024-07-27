<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
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
            return (new MailMessage)
                ->subject(Lang::get('Xác thực địa chỉ email'))
                ->greeting(Lang::get('Xin chào'))
                ->line(Lang::get('Hãy xác minh địa chỉ email qua liên kết sau.'))
                ->action(Lang::get('Xác nhận email'), str_replace('api/auth/email/', '', $url))
                ->line(Lang::get('Nếu bạn không gửi yêu cầu xác thực, hãy bỏ qua email này.'))
                ->salutation(Lang::get('Trân trọng cảm ơn Quý khách đã tin tưởng sử dụng dịch vụ!'));
        });
    }
}
