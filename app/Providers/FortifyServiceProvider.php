<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\AuditLogAcesso;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
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
        Fortify::authenticateUsing(function (HttpRequest $request): ?User {
            $user = User::query()->where('email', $request->string('email')->toString())->first();
            $credentialsValid = $user instanceof User
                && Hash::check((string) $request->input('password'), $user->password);
            $authenticated = $credentialsValid && (bool) $user->ativo;

            $this->recordAccessAudit(
                userId: $user?->id,
                ip: $request->ip(),
                userAgent: $request->userAgent(),
                sucesso: $authenticated,
            );

            if (! $authenticated) {
                event(new Failed('web', $user, [
                    'email' => $request->input('email'),
                ]));

                return null;
            }

            if (Schema::hasColumn('users', 'ultimo_login') && Schema::hasColumn('users', 'ultimo_ip')) {
                $user->forceFill([
                    'ultimo_login' => now(),
                    'ultimo_ip' => $request->ip(),
                ])->save();
            }

            return $user;
        });

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }

    protected function recordAccessAudit(?int $userId, ?string $ip, ?string $userAgent, bool $sucesso): void
    {
        if (! Schema::hasTable('audit_logs_acesso')) {
            return;
        }

        AuditLogAcesso::query()->create([
            'user_id' => $userId,
            'ip' => $ip ?? '127.0.0.1',
            'user_agent' => $userAgent,
            'sucesso' => $sucesso,
            'created_at' => now(),
        ]);
    }
}
