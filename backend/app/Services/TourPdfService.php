<?php

namespace App\Services;

use App\Models\Tour;
use Barryvdh\DomPDF\Facade\Pdf;

class TourPdfService
{
    /** @var array<string, string> */
    private array $t = [];

    public function generateTourDocument(int $tourId, string $lang = 'ru')
    {
        $tour = Tour::with([
            'counterparty', 'assignedStaff', 'createdBy',
            'destinations',
            'hotels.hotel', 'hotels.destination',
            'transports',
            'meals.restaurant', 'meals.destination',
            'entranceTickets.destination',
            'visas',
        ])->findOrFail($tourId);

        $this->t = $this->labels($lang);

        $pdf = Pdf::loadHTML($this->buildHtml($tour));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('defaultFont', 'DejaVu Sans');

        return $pdf;
    }

    /** @return array<string, string> */
    private function labels(string $lang): array
    {
        $ru = [
            'doc_title' => 'ТУР ДОКУМЕНТ',
            'generated' => 'Документ сгенерирован автоматически',
            'general' => 'ОБЩАЯ ИНФОРМАЦИЯ О ТУРЕ',
            'tour_name' => 'Название тура',
            'country' => 'Страна',
            'start' => 'Начало',
            'end' => 'Конец',
            'duration' => 'Продолжительность',
            'days' => 'дней',
            'tourists' => 'Туристы',
            'adults' => 'Взрослых',
            'children' => 'Детей',
            'partner' => 'Партнёр',
            'status' => 'Статус',
            'responsible' => 'Ответственный',
            'arrival_flight' => 'Рейс прибытия',
            'departure_flight' => 'Рейс отправления',
            'notes' => 'Примечания',
            'itinerary' => 'МАРШРУТ',
            'day' => 'День',
            'city' => 'Город',
            'arrival' => 'Прибытие',
            'departure' => 'Отправление',
            'nights' => 'Ночей',
            'note' => 'Примечание',
            'hotels' => 'ОТЕЛИ',
            'hotel' => 'Отель',
            'room_type' => 'Тип номера',
            'rooms' => 'Номера',
            'check_in' => 'Check-in',
            'check_out' => 'Check-out',
            'transport' => 'ТРАНСПОРТ',
            'date' => 'Дата',
            'route' => 'Маршрут',
            'type' => 'Тип',
            'dep_time' => 'Отправление',
            'arr_time' => 'Прибытие',
            'meals' => 'ПИТАНИЕ',
            'time' => 'Время',
            'restaurant' => 'Ресторан',
            'menu' => 'Меню',
            'meal_breakfast' => 'Завтрак',
            'meal_lunch' => 'Обед',
            'meal_dinner' => 'Ужин',
            'meal_snack' => 'Перекус',
            'tickets' => 'ВХОДНЫЕ БИЛЕТЫ',
            'attraction' => 'Достопримечательность',
            'pax' => 'Чел.',
            'visas' => 'ВИЗЫ',
            'applicant' => 'Заявитель',
            'passport' => 'Паспорт',
            'nationality' => 'Гражданство',
            'visa_type' => 'Тип визы',
            'other_city' => 'Другой',
            'own_transport' => 'Собственный',
            'hired_transport' => 'Аренда',
        ];

        $en = [
            'doc_title' => 'TOUR VOUCHER',
            'generated' => 'Document generated automatically',
            'general' => 'GENERAL TOUR INFORMATION',
            'tour_name' => 'Tour Name',
            'country' => 'Country',
            'start' => 'Start',
            'end' => 'End',
            'duration' => 'Duration',
            'days' => 'days',
            'tourists' => 'Tourists',
            'adults' => 'Adults',
            'children' => 'Children',
            'partner' => 'Partner',
            'status' => 'Status',
            'responsible' => 'Responsible',
            'arrival_flight' => 'Arrival Flight',
            'departure_flight' => 'Departure Flight',
            'notes' => 'Notes',
            'itinerary' => 'ITINERARY',
            'day' => 'Day',
            'city' => 'City',
            'arrival' => 'Arrival',
            'departure' => 'Departure',
            'nights' => 'Nights',
            'note' => 'Note',
            'hotels' => 'HOTELS',
            'hotel' => 'Hotel',
            'room_type' => 'Room Type',
            'rooms' => 'Rooms',
            'check_in' => 'Check-in',
            'check_out' => 'Check-out',
            'transport' => 'TRANSPORT',
            'date' => 'Date',
            'route' => 'Route',
            'type' => 'Type',
            'dep_time' => 'Departure',
            'arr_time' => 'Arrival',
            'meals' => 'MEALS',
            'time' => 'Time',
            'restaurant' => 'Restaurant',
            'menu' => 'Menu',
            'meal_breakfast' => 'Breakfast',
            'meal_lunch' => 'Lunch',
            'meal_dinner' => 'Dinner',
            'meal_snack' => 'Snack',
            'tickets' => 'ENTRANCE TICKETS',
            'attraction' => 'Attraction',
            'pax' => 'Pax',
            'visas' => 'VISAS',
            'applicant' => 'Applicant',
            'passport' => 'Passport No.',
            'nationality' => 'Nationality',
            'visa_type' => 'Visa Type',
            'other_city' => 'Other',
            'own_transport' => 'Own Fleet',
            'hired_transport' => 'Hired',
        ];

        return $lang === 'en' ? $en : $ru;
    }

    private function l(string $key): string
    {
        return $this->t[$key] ?? $key;
    }

    private function buildHtml(Tour $tour): string
    {
        $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; margin: 0; padding: 20px; }
  .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #1A2744; padding-bottom: 15px; margin-bottom: 20px; }
  .logo-text { font-size: 22px; font-weight: bold; color: #1A2744; letter-spacing: 2px; }
  .logo-sub { font-size: 11px; color: #4A90D9; }
  .title { text-align: center; }
  .title h1 { font-size: 18px; color: #1A2744; margin: 0; }
  .title .code { font-size: 12px; color: #666; margin-top: 5px; }
  .section { margin-bottom: 20px; }
  .section-title { background: #1A2744; color: white; padding: 6px 10px; font-size: 11px; font-weight: bold; margin-bottom: 8px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
  th { background: #0D1B2A; color: white; padding: 6px 8px; font-size: 9px; text-align: left; }
  td { padding: 5px 8px; font-size: 9px; border-bottom: 1px solid #e0e0e0; }
  tr:nth-child(even) { background: #F0F4F8; }
  .footer { margin-top: 30px; border-top: 2px solid #1A2744; padding-top: 10px; text-align: center; font-size: 9px; color: #666; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 8px; font-weight: bold; }
</style>
</head>
<body>';

        // ── Header ────────────────────────────────────────────────────────
        $logoPath = public_path('logo.jpeg');
        $logoHtml = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoHtml = '<img src="data:image/jpeg;base64,'.$logoData.'" style="width:52px; height:52px; border-radius:8px; object-fit:cover;" />';
        }

        $html .= '<table style="width:100%; border-bottom:3px solid #1A2744; padding-bottom:12px; margin-bottom:18px;">
  <tr>
    <td style="width:40%; vertical-align:middle;">
      <table><tr>
        <td style="vertical-align:middle; padding-right:10px;">'.$logoHtml.'</td>
        <td style="vertical-align:middle;">
          <div class="logo-text">SOGDA TOUR</div>
          <div class="logo-sub">Travel Management System</div>
        </td>
      </tr></table>
    </td>
    <td style="text-align:center; vertical-align:middle;">
      <div style="font-size:18px; font-weight:bold; color:#1A2744;">'.$this->l('doc_title').'</div>
      <div style="font-size:11px; color:#666; margin-top:4px;">'.$tour->tour_code.' &nbsp;|&nbsp; '.now()->format('d.m.Y').'</div>
    </td>
    <td style="text-align:right; vertical-align:middle; font-size:9px; color:#666;">
      sogdatour.uz<br>info@sogdatour.uz
    </td>
  </tr>
</table>';

        // ── General info ──────────────────────────────────────────────────
        $html .= '<div class="section">
  <div class="section-title">'.$this->l('general').'</div>
  <table>
    <tr>
      <td><strong>'.$this->l('tour_name').':</strong></td>
      <td>'.e($tour->tour_name).'</td>
      <td><strong>'.$this->l('country').':</strong></td>
      <td>'.e($tour->country).'</td>
    </tr>
    <tr>
      <td><strong>'.$this->l('start').':</strong></td>
      <td>'.($tour->start_date?->format('d.m.Y') ?? '-').'</td>
      <td><strong>'.$this->l('end').':</strong></td>
      <td>'.($tour->end_date?->format('d.m.Y') ?? '-').'</td>
    </tr>
    <tr>
      <td><strong>'.$this->l('duration').':</strong></td>
      <td>'.$tour->duration_days.' '.$this->l('days').'</td>
      <td><strong>'.$this->l('tourists').':</strong></td>
      <td>'.$tour->pax_count.' ('.$this->l('adults').': '.$tour->pax_adults.', '.$this->l('children').': '.$tour->pax_children.')</td>
    </tr>
    <tr>
      <td><strong>'.$this->l('partner').':</strong></td>
      <td>'.($tour->counterparty?->company_name ?? '-').'</td>
      <td><strong>'.$this->l('responsible').':</strong></td>
      <td>'.($tour->assignedStaff?->name ?? '-').'</td>
    </tr>';

        if ($tour->arrival_flight_number || $tour->departure_flight_number) {
            $html .= '<tr>
      <td><strong>'.$this->l('arrival_flight').':</strong></td>
      <td>'.e($tour->arrival_flight_number ?? '-').' '.($tour->arrival_flight_time ?? '').($tour->arrival_terminal ? ' / T'.$tour->arrival_terminal : '').'</td>
      <td><strong>'.$this->l('departure_flight').':</strong></td>
      <td>'.e($tour->departure_flight_number ?? '-').' '.($tour->departure_flight_time ?? '').'</td>
    </tr>';
        }

        if ($tour->notes) {
            $html .= '<tr>
      <td><strong>'.$this->l('notes').':</strong></td>
      <td colspan="3">'.e($tour->notes).'</td>
    </tr>';
        }

        $html .= '</table></div>';

        // ── Destinations ──────────────────────────────────────────────────
        if ($tour->destinations->count() > 0) {
            $html .= '<div class="section">
  <div class="section-title">'.$this->l('itinerary').'</div>
  <table>
    <thead>
      <tr>
        <th>'.$this->l('day').'</th>
        <th>'.$this->l('city').'</th>
        <th>'.$this->l('arrival').'</th>
        <th>'.$this->l('departure').'</th>
        <th>'.$this->l('nights').'</th>
        <th>'.$this->l('note').'</th>
      </tr>
    </thead>
    <tbody>';
            foreach ($tour->destinations as $dest) {
                $city = ($dest->custom_city_name) ? $dest->custom_city_name
                    : ($dest->city === 'other' ? $this->l('other_city') : ucfirst($dest->city));
                $html .= '<tr>
      <td>'.$dest->day_number.'</td>
      <td><strong>'.e($city).'</strong></td>
      <td>'.($dest->arrival_date?->format('d.m.Y') ?? '-').'</td>
      <td>'.($dest->departure_date?->format('d.m.Y') ?? '-').'</td>
      <td>'.$dest->nights_count.'</td>
      <td>'.e($dest->notes ?? '').'</td>
    </tr>';
            }
            $html .= '</tbody></table></div>';
        }

        // ── Hotels ────────────────────────────────────────────────────────
        if ($tour->hotels->count() > 0) {
            $html .= '<div class="section">
  <div class="section-title">'.$this->l('hotels').'</div>
  <table>
    <thead>
      <tr>
        <th>'.$this->l('city').'</th>
        <th>'.$this->l('hotel').'</th>
        <th>'.$this->l('room_type').'</th>
        <th>'.$this->l('rooms').'</th>
        <th>'.$this->l('check_in').'</th>
        <th>'.$this->l('check_out').'</th>
        <th>'.$this->l('status').'</th>
      </tr>
    </thead>
    <tbody>';
            foreach ($tour->hotels as $booking) {
                $html .= '<tr>
      <td>'.ucfirst($booking->hotel?->city ?? '').'</td>
      <td><strong>'.e($booking->hotel?->name ?? '-').'</strong></td>
      <td>'.e($booking->room_type ?? '-').'</td>
      <td>'.$booking->room_count.'</td>
      <td>'.($booking->check_in_date?->format('d.m.Y') ?? '-').($booking->check_in_time ? ' '.$booking->check_in_time : '').'</td>
      <td>'.($booking->check_out_date?->format('d.m.Y') ?? '-').($booking->check_out_time ? ' '.$booking->check_out_time : '').'</td>
      <td>'.strtoupper($booking->status).'</td>
    </tr>';
            }
            $html .= '</tbody></table></div>';
        }

        // ── Transport ─────────────────────────────────────────────────────
        if ($tour->transports->count() > 0) {
            $html .= '<div class="section">
  <div class="section-title">'.$this->l('transport').'</div>
  <table>
    <thead>
      <tr>
        <th>'.$this->l('date').'</th>
        <th>'.$this->l('route').'</th>
        <th>'.$this->l('type').'</th>
        <th>'.$this->l('dep_time').'</th>
        <th>'.$this->l('arr_time').'</th>
        <th>'.$this->l('status').'</th>
      </tr>
    </thead>
    <tbody>';
            foreach ($tour->transports as $tr) {
                $html .= '<tr>
      <td>'.($tr->transport_date?->format('d.m.Y') ?? '-').'</td>
      <td>'.e($tr->route_from).' → '.e($tr->route_to).'</td>
      <td>'.ucfirst($tr->transport_type ?? '').($tr->is_own_fleet ? ' ('.$this->l('own_transport').')' : '').'</td>
      <td>'.($tr->departure_time ?? '-').'</td>
      <td>'.($tr->arrival_time ?? '-').'</td>
      <td>'.strtoupper($tr->status ?? '').'</td>
    </tr>';
            }
            $html .= '</tbody></table></div>';
        }

        // ── Meals ─────────────────────────────────────────────────────────
        if ($tour->meals->count() > 0) {
            $html .= '<div class="section">
  <div class="section-title">'.$this->l('meals').'</div>
  <table>
    <thead>
      <tr>
        <th>'.$this->l('date').'</th>
        <th>'.$this->l('type').'</th>
        <th>'.$this->l('time').'</th>
        <th>'.$this->l('restaurant').'</th>
        <th>'.$this->l('menu').'</th>
        <th>'.$this->l('status').'</th>
      </tr>
    </thead>
    <tbody>';
            $mealTypeMap = [
                'breakfast' => $this->l('meal_breakfast'),
                'lunch' => $this->l('meal_lunch'),
                'dinner' => $this->l('meal_dinner'),
                'snack' => $this->l('meal_snack'),
            ];
            foreach ($tour->meals as $meal) {
                $html .= '<tr>
      <td>'.($meal->meal_date?->format('d.m.Y') ?? '-').'</td>
      <td>'.($mealTypeMap[$meal->meal_type] ?? $meal->meal_type).'</td>
      <td>'.($meal->meal_time ?? '-').'</td>
      <td>'.e($meal->restaurant?->company_name ?? '-').'</td>
      <td>'.ucfirst($meal->menu_type ?? '').'</td>
      <td>'.strtoupper($meal->status ?? '').'</td>
    </tr>';
            }
            $html .= '</tbody></table></div>';
        }

        // ── Entrance tickets ──────────────────────────────────────────────
        if ($tour->entranceTickets->count() > 0) {
            $html .= '<div class="section">
  <div class="section-title">'.$this->l('tickets').'</div>
  <table>
    <thead>
      <tr>
        <th>'.$this->l('city').'</th>
        <th>'.$this->l('attraction').'</th>
        <th>'.$this->l('date').'</th>
        <th>'.$this->l('time').'</th>
        <th>'.$this->l('pax').'</th>
        <th>'.$this->l('status').'</th>
      </tr>
    </thead>
    <tbody>';
            foreach ($tour->entranceTickets as $ticket) {
                $html .= '<tr>
      <td>'.e($ticket->city ?? '-').'</td>
      <td><strong>'.e($ticket->attraction_name).'</strong></td>
      <td>'.($ticket->visit_date?->format('d.m.Y') ?? '-').'</td>
      <td>'.($ticket->visit_time ?? '-').'</td>
      <td>'.$ticket->pax_count.'</td>
      <td>'.strtoupper($ticket->booking_status ?? '').'</td>
    </tr>';
            }
            $html .= '</tbody></table></div>';
        }

        // ── Visas ─────────────────────────────────────────────────────────
        if ($tour->visas->count() > 0) {
            $html .= '<div class="section">
  <div class="section-title">'.$this->l('visas').'</div>
  <table>
    <thead>
      <tr>
        <th>'.$this->l('applicant').'</th>
        <th>'.$this->l('passport').'</th>
        <th>'.$this->l('nationality').'</th>
        <th>'.$this->l('visa_type').'</th>
        <th>'.$this->l('status').'</th>
      </tr>
    </thead>
    <tbody>';
            foreach ($tour->visas as $visa) {
                $html .= '<tr>
      <td><strong>'.e($visa->applicant_name).'</strong></td>
      <td>'.e($visa->passport_number ?? '-').'</td>
      <td>'.e($visa->nationality ?? '-').'</td>
      <td>'.ucfirst($visa->visa_type ?? '-').'</td>
      <td>'.strtoupper($visa->status).'</td>
    </tr>';
            }
            $html .= '</tbody></table></div>';
        }

        // ── Footer ────────────────────────────────────────────────────────
        $html .= '<div class="footer">
  <strong>Sogda Tour</strong> &nbsp;|&nbsp; sogdatour.uz &nbsp;|&nbsp; info@sogdatour.uz<br>
  '.$this->l('generated').' — '.now()->format('d.m.Y H:i').'
</div>';

        $html .= '</body></html>';

        return $html;
    }
}
