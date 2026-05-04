<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tour;
use App\Models\TourHotel;
use App\Models\Visa;
use App\Models\Counterparty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function financialSummary(Request $request)
    {
        $from = $request->date_from ?? now()->startOfYear()->toDateString();
        $to = $request->date_to ?? now()->toDateString();

        $tours = Tour::whereBetween('start_date', [$from, $to])
            ->whereIn('status', ['confirmed', 'in_progress', 'completed'])
            ->select(
                DB::raw('YEAR(start_date) as year'),
                DB::raw('MONTH(start_date) as month'),
                DB::raw('SUM(total_price_usd) as revenue'),
                DB::raw('COUNT(*) as tours_count'),
                DB::raw('SUM(pax_count) as total_pax')
            )
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get();

        $summary = Tour::whereBetween('start_date', [$from, $to])
            ->whereIn('status', ['confirmed', 'in_progress', 'completed'])
            ->selectRaw('SUM(total_price_usd) as total_revenue, COUNT(*) as total_tours, SUM(pax_count) as total_pax, AVG(total_price_usd) as avg_revenue')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'monthly_data' => $tours,
            ],
        ]);
    }

    public function tourStatistics(Request $request)
    {
        $period = $request->period ?? 'year';
        $from = $period === 'month' ? now()->startOfMonth() : ($period === 'year' ? now()->startOfYear() : now()->subYears(3));

        $byStatus = Tour::where('created_at', '>=', $from)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $byCountry = Tour::where('created_at', '>=', $from)
            ->select('country', DB::raw('count(*) as count'), DB::raw('SUM(pax_count) as total_pax'))
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $avgPax = Tour::where('created_at', '>=', $from)->avg('pax_count');

        return response()->json([
            'success' => true,
            'data' => [
                'by_status' => $byStatus,
                'by_country' => $byCountry,
                'avg_pax' => round($avgPax, 1),
                'total_tours' => Tour::where('created_at', '>=', $from)->count(),
            ],
        ]);
    }

    public function hotelOccupancy(Request $request)
    {
        $from = $request->date_from ?? now()->startOfMonth()->toDateString();
        $to = $request->date_to ?? now()->endOfMonth()->toDateString();

        $occupancy = TourHotel::with('hotel')
            ->whereBetween('check_in_date', [$from, $to])
            ->whereIn('status', ['confirmed', 'ok'])
            ->select('hotel_id', DB::raw('SUM(room_count) as rooms_booked'), DB::raw('COUNT(*) as bookings'))
            ->groupBy('hotel_id')
            ->get();

        return response()->json(['success' => true, 'data' => $occupancy]);
    }

    public function visaReport(Request $request)
    {
        $period = $request->period ?? 'year';
        $from = $period === 'month' ? now()->startOfMonth() : now()->startOfYear();

        $byStatus = Visa::where('created_at', '>=', $from)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $byCountry = Visa::where('created_at', '>=', $from)
            ->select('country_from', DB::raw('count(*) as count'))
            ->groupBy('country_from')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'by_status' => $byStatus,
                'by_country' => $byCountry,
                'total' => Visa::where('created_at', '>=', $from)->count(),
            ],
        ]);
    }

    public function counterpartyReport(Request $request)
    {
        $counterparties = Counterparty::withCount('tours')
            ->with(['tours' => fn($q) => $q->whereIn('status', ['confirmed', 'in_progress', 'completed'])->select('id', 'counterparty_id', 'total_price_usd', 'pax_count')])
            ->orderByDesc('tours_count')
            ->limit(20)
            ->get()
            ->map(fn($cp) => [
                'id' => $cp->id,
                'company_name' => $cp->company_name,
                'type' => $cp->type,
                'country' => $cp->country,
                'tours_count' => $cp->tours_count,
                'total_revenue' => $cp->tours->sum('total_price_usd'),
                'total_pax' => $cp->tours->sum('pax_count'),
                'rating' => $cp->rating,
            ]);

        return response()->json(['success' => true, 'data' => $counterparties]);
    }

    public function staffPerformance(Request $request)
    {
        $from = $request->date_from ?? now()->startOfYear()->toDateString();
        $to = $request->date_to ?? now()->toDateString();

        $staff = User::whereHas('roles', fn($q) => $q->whereIn('name', ['manager', 'sales', 'staff']))
            ->withCount(['tours as tours_count' => fn($q) => $q->whereBetween('start_date', [$from, $to])])
            ->get()
            ->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'department' => $u->department,
                'tours_count' => $u->tours_count,
                'total_pax' => $u->tours()->whereBetween('start_date', [$from, $to])->sum('pax_count'),
                'total_revenue' => $u->tours()->whereBetween('start_date', [$from, $to])->whereIn('status', ['confirmed', 'in_progress', 'completed'])->sum('total_price_usd'),
            ]);

        return response()->json(['success' => true, 'data' => $staff]);
    }

    public function export(Request $request, string $type)
    {
        return response()->json(['success' => false, 'message' => 'Export funksiyasi keyinroq qo\'shiladi.'], 501);
    }
}
