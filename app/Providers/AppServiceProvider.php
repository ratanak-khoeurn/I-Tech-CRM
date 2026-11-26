<?php

namespace App\Providers;

use App\Models\FollowUpTask;
use App\Models\Notification;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('pagination::bootstrap-5');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-5');

        View::composer('layouts.app', function ($view) {
            $user = Auth::user();
            $view->with('__notifCount', 0)
                ->with('__recentNotifications', [])
                ->with('__taskCount', 0);
            if ($user) {
                $view->with('__notifCount', Notification::where('user_id', $user->id)->where('is_read', false)->count())
                    ->with('__recentNotifications', Notification::where('user_id', $user->id)->latest()->limit(5)->get())
                    ->with('__taskCount', FollowUpTask::where('assigned_to', $user->id)
                        ->whereIn('status', ['pending', 'in_progress'])
                        ->whereDate('due_date', '<=', now()->addDays(7))
                        ->count());
            }
        });
    }
}
