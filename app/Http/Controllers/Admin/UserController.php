<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\MailSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = Str::limit(trim((string) $request->query('q', '')), 100, '');
        $status = strtolower(trim((string) $request->query('status', '')));
        $verification = strtolower(trim((string) $request->query('verification', '')));

        if (! in_array($status, ['active', 'inactive'], true)) {
            $status = '';
        }

        if (! in_array($verification, ['verified', 'unverified'], true)) {
            $verification = '';
        }

        $users = User::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->when($verification === 'verified', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->when($verification === 'unverified', fn ($query) => $query->whereNull('email_verified_at'))
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'activeStatus' => $status,
            'activeVerification' => $verification,
            'totalCount' => User::query()->count(),
            'activeCount' => User::query()->where('is_active', true)->count(),
            'inactiveCount' => User::query()->where('is_active', false)->count(),
            'unverifiedCount' => User::query()->whereNull('email_verified_at')->count(),
        ]);
    }

    public function show(User $user, MailSettings $mailSettings): View
    {
        $activity = AuditLog::query()
            ->where(function ($query) use ($user): void {
                $query
                    ->where(function ($query) use ($user): void {
                        $query
                            ->where('actor_type', 'user')
                            ->where('actor_id', (string) $user->getKey());
                    })
                    ->orWhere(function ($query) use ($user): void {
                        $query
                            ->where('target_type', 'user')
                            ->where('target_id', (string) $user->getKey());
                    })
                    ->orWhere(function ($query) use ($user): void {
                        $query
                            ->where('actor_type', 'user')
                            ->whereNull('actor_id')
                            ->whereIn('actor_name', [$user->name, $user->email]);
                    });
            })
            ->latest('id')
            ->limit(25)
            ->get();

        return view('admin.users.show', [
            'user' => $user,
            'activity' => $activity,
            'mailReady' => $mailSettings->isReady(),
        ]);
    }

    public function updateStatus(Request $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $newStatus = (bool) $validated['is_active'];

        if ($user->is_active === $newStatus) {
            return back()->with('status', 'Статус пользователя не изменился.');
        }

        $oldStatus = $user->is_active;
        $values = ['is_active' => $newStatus];

        if (! $newStatus) {
            $values['remember_token'] = Str::random(60);
        }

        $user->forceFill($values)->save();

        if (! $newStatus && Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->getKey())->delete();
        }

        $auditLogger->success(
            category: 'user',
            action: $newStatus ? 'user.enabled' : 'user.disabled',
            target: $user,
            details: [
                'old' => $oldStatus,
                'new' => $newStatus,
            ],
        );

        return back()->with(
            'status',
            $newStatus ? 'Учётная запись пользователя включена.' : 'Учётная запись пользователя отключена.',
        );
    }

    public function resendVerification(
        User $user,
        MailSettings $mailSettings,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        if ($user->hasVerifiedEmail()) {
            return back()->with('status', 'Email пользователя уже подтверждён.');
        }

        if (! $mailSettings->isReady()) {
            return back()->withErrors([
                'mail' => 'Почтовая система не готова. Сначала настройте SMTP и выполните тестовую отправку.',
            ]);
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (Throwable $exception) {
            Log::warning('Unable to resend user verification email from administration panel.', [
                'user_id' => $user->getKey(),
                'exception' => $exception::class,
            ]);

            $auditLogger->failed(
                category: 'user',
                action: 'user.verification_resent',
                target: $user,
                details: ['exception_class' => $exception::class],
            );

            return back()->withErrors([
                'mail' => 'Письмо подтверждения отправить не удалось. Проверьте журнал и настройки почты.',
            ]);
        }

        $auditLogger->success(
            category: 'user',
            action: 'user.verification_resent',
            target: $user,
        );

        return back()->with('status', 'Письмо подтверждения отправлено повторно.');
    }

    public function sendPasswordReset(
        User $user,
        MailSettings $mailSettings,
        AuditLogger $auditLogger,
    ): RedirectResponse {
        if (! $mailSettings->isReady()) {
            return back()->withErrors([
                'mail' => 'Почтовая система не готова. Сначала настройте SMTP и выполните тестовую отправку.',
            ]);
        }

        try {
            $status = Password::broker('users')->sendResetLink([
                'email' => $user->email,
            ]);
        } catch (Throwable $exception) {
            Log::warning('Unable to send user password reset email from administration panel.', [
                'user_id' => $user->getKey(),
                'exception' => $exception::class,
            ]);

            $auditLogger->failed(
                category: 'user',
                action: 'user.password_reset_sent',
                target: $user,
                details: ['exception_class' => $exception::class],
            );

            return back()->withErrors([
                'mail' => 'Ссылку восстановления отправить не удалось. Проверьте журнал и настройки почты.',
            ]);
        }

        if ($status !== Password::RESET_LINK_SENT) {
            $auditLogger->failed(
                category: 'user',
                action: 'user.password_reset_sent',
                target: $user,
                details: ['reason' => $status],
            );

            $message = $status === Password::RESET_THROTTLED
                ? 'Ссылка уже отправлялась недавно. Повторите попытку позже.'
                : 'Ссылку восстановления отправить не удалось.';

            return back()->withErrors(['mail' => $message]);
        }

        $auditLogger->success(
            category: 'user',
            action: 'user.password_reset_sent',
            target: $user,
        );

        return back()->with('status', 'Ссылка восстановления пароля отправлена пользователю.');
    }
}
