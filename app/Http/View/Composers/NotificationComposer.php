<?php

namespace App\Http\View\Composers;

use App\Models\ProcurementNotification;
use App\Models\ProcurementOrder;
use App\Models\SalesDepartment;
use App\Models\SaleNotification;
use Illuminate\View\View;

class NotificationComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();
        if (!$user) {
            $view->with([
                'navUnreadNotifications' => collect(),
                'navNotificationCount' => 0,
                'navPendingVerifications' => 0,
            ]);
            return;
        }

        // Procurement notifications for current user
        $notifications = ProcurementNotification::with(['order', 'fromUser'])
            ->where('to_user_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->take(20)
            ->get();

        // Pending verifications for managers
        $managedDeptIds = SalesDepartment::where('manager_id', $user->id)->pluck('id');
        $pendingCount = ProcurementOrder::where('status', 'for_verification')
            ->whereIn('department_id', $managedDeptIds)
            ->count();

        // Unread payment verification requests (from agents) for this user
        $saleVerificationNotifs = SaleNotification::with(['sale', 'fromUser'])
            ->where('to_user_id', $user->id)
            ->where('type', 'verification_request')
            ->where('is_read', false)
            ->latest()
            ->take(10)
            ->get();

        $view->with([
            'navUnreadNotifications' => $notifications,
            'navNotificationCount' => $notifications->count(),
            'navPendingVerifications' => $pendingCount,
            'navSaleVerificationNotifs' => $saleVerificationNotifs,
            'navSaleVerificationCount' => $saleVerificationNotifs->count(),
        ]);
    }
}
