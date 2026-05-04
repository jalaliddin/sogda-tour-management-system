<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourHotel;
use App\Models\Visa;
use App\Models\Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getData(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();
        $weekLater = now()->addDays(7)->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $toursQuery = Tour::query();
        if ($user->hasRole(['hotel_khiva', 'hotel_samarkand', 'hotel_bukhara'])) {
            $city = str_replace('hotel_', '', $user->getRoleNames()->first());
            $toursQuery->whereHas('hotels.hotel', fn($q) => $q->where('city', $city));
        } elseif (!$user->hasRole(['super_admin', 'manager', 'accountant'])) {
            $toursQuery->where('assigned_staff_id', $user->id);
        }

        $todayTours = (clone $toursQuery)->whereDate('start_date', $today)->orWhereDate('end_date', $today)->count();
        $activeTours = (clone $toursQuery)->whereIn('status', ['confirmed', 'in_progress'])->count();

        $upcomingCheckouts = TourHotel::whereDate('check_out_date', '<=', now()->addDays(2)->toDateString())
            ->whereDate('check_out_date', '>=', $today)
            ->whereIn('status', ['confirmed', 'ok'])
            ->count();

        $pendingVisas = Visa::where('status', 'pending')->count();
        $newOffers = Offer::where('status', 'new')->count();

        $monthlyRevenue = Tour::whereBetween('start_date', [$monthStart, $monthEnd])
            ->whereIn('status', ['confirmed', 'in_progress', 'completed'])
            ->sum('total_price_usd');

        $monthlyPax = Tour::whereBetween('start_date', [$monthStart, $monthEnd])
            ->whereIn('status', ['confirmed', 'in_progress', 'completed'])
            ->sum('pax_count');

        $recentTours = Tour::with(['counterparty', 'assignedStaff'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id, 'tour_code' => $t->tour_code, 'tour_name' => $t->tour_name,
                'country' => $t->country, 'status' => $t->status, 'pax_count' => $t->pax_count,
                'start_date' => $t->start_date, 'end_date' => $t->end_date,
                'counterparty' => $t->counterparty?->company_name,
            ]);

        $upcomingTours = Tour::whereDate('start_date', '>=', $today)
            ->whereDate('start_date', '<=', $weekLater)
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->with(['counterparty', 'assignedStaff'])
            ->orderBy('start_date')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id, 'tour_code' => $t->tour_code, 'tour_name' => $t->tour_name,
                'country' => $t->country, 'status' => $t->status, 'pax_count' => $t->pax_count,
                'start_date' => $t->start_date,
            ]);

        $tourStatusChart = Tour::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $revenueChart = Tour::select(
            DB::raw('YEAR(start_date) as year'),
            DB::raw('MONTH(start_date) as month'),
            DB::raw('SUM(total_price_usd) as revenue'),
            DB::raw('COUNT(*) as tours_count')
        )
            ->whereIn('status', ['confirmed', 'in_progress', 'completed'])
            ->where('start_date', '>=', now()->subMonths(6)->toDateString())
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get();

        $destinationChart = Tour::select('country', DB::raw('count(*) as count'))
            ->whereIn('status', ['confirmed', 'in_progress', 'completed'])
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(8)
            ->pluck('count', 'country');

        $notifications = [];

        $expiringVisas = Visa::where('status', 'pending')
            ->whereDate('expected_date', '<=', now()->addDays(3)->toDateString())
            ->whereNotNull('expected_date')
            ->count();
        if ($expiringVisas > 0) {
            $notifications[] = ['type' => 'warning', 'message' => "$expiringVisas ta viza muddati yaqinlashmoqda", 'icon' => 'mdi-passport'];
        }

        if ($newOffers > 0) {
            $notifications[] = ['type' => 'info', 'message' => "$newOffers ta yangi offer ko'rib chiqilishi kerak", 'icon' => 'mdi-email-open'];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'today_tours' => $todayTours,
                'active_tours' => $activeTours,
                'pending_visas' => $pendingVisas,
                'upcoming_checkouts' => $upcomingCheckouts,
                'new_offers' => $newOffers,
                'monthly_revenue_usd' => (float)$monthlyRevenue,
                'monthly_pax_count' => (int)$monthlyPax,
                'recent_tours' => $recentTours,
                'upcoming_tours' => $upcomingTours,
                'notifications' => $notifications,
                'tour_status_chart' => $tourStatusChart,
                'revenue_chart' => $revenueChart,
                'destination_chart' => $destinationChart,
            ],
        ]);
    }
}
