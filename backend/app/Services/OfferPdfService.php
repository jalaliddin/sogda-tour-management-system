<?php

namespace App\Services;

use App\Models\Offer;
use Barryvdh\DomPDF\Facade\Pdf;

class OfferPdfService
{
    /** @var array<string, string> */
    private array $t = [];

    public function generate(int $offerId, string $lang = 'ru')
    {
        $offer = Offer::with(['counterparty', 'reviewedBy'])->findOrFail($offerId);
        $this->t = $this->labels($lang);

        $pdf = Pdf::loadHTML($this->buildHtml($offer));
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('defaultFont', 'DejaVu Sans');

        return $pdf;
    }

    /** @return array<string, string> */
    private function labels(string $lang): array
    {
        $ru = [
            'title'         => 'КОММЕРЧЕСКОЕ ПРЕДЛОЖЕНИЕ',
            'offer_no'      => 'Предложение №',
            'date'          => 'Дата',
            'valid_until'   => 'Действительно до',
            'to'            => 'Кому',
            'from'          => 'От',
            'offer_type'    => 'Тип предложения',
            'destinations'  => 'Направления',
            'period'        => 'Период',
            'pax'           => 'Туристы',
            'pax_range'     => 'от %s до %s чел.',
            'price_pp'      => 'Цена / чел.',
            'total'         => 'Итого',
            'currency'      => 'Валюта',
            'includes'      => 'ВКЛЮЧЕНО В СТОИМОСТЬ',
            'excludes'      => 'НЕ ВКЛЮЧЕНО',
            'itinerary'     => 'ПРОГРАММА ТУРА',
            'notes'         => 'ПРИМЕЧАНИЯ',
            'status'        => 'Статус',
            'footer_note'   => 'Данное предложение носит информационный характер и не является публичной офертой.',
            'contact'       => 'По вопросам: info@sogdatour.uz | sogdatour.uz',
            'type_inbound'  => 'Въездной туризм (Inbound)',
            'type_outbound' => 'Выездной туризм (Outbound)',
            'type_package'  => 'Пакетный тур',
            'type_custom'   => 'Индивидуальный тур',
            'status_new'       => 'Новый',
            'status_reviewing' => 'На рассмотрении',
            'status_accepted'  => 'Принят',
            'status_rejected'  => 'Отклонён',
        ];

        $en = [
            'title'         => 'COMMERCIAL OFFER',
            'offer_no'      => 'Offer No.',
            'date'          => 'Date',
            'valid_until'   => 'Valid Until',
            'to'            => 'To',
            'from'          => 'From',
            'offer_type'    => 'Offer Type',
            'destinations'  => 'Destinations',
            'period'        => 'Period',
            'pax'           => 'Tourists',
            'pax_range'     => 'from %s to %s pax',
            'price_pp'      => 'Price / pax',
            'total'         => 'Total',
            'currency'      => 'Currency',
            'includes'      => 'INCLUDED IN PRICE',
            'excludes'      => 'NOT INCLUDED',
            'itinerary'     => 'TOUR PROGRAM',
            'notes'         => 'NOTES',
            'status'        => 'Status',
            'footer_note'   => 'This offer is for information purposes only and does not constitute a public offer.',
            'contact'       => 'Inquiries: info@sogdatour.uz | sogdatour.uz',
            'type_inbound'  => 'Inbound Tourism',
            'type_outbound' => 'Outbound Tourism',
            'type_package'  => 'Package Tour',
            'type_custom'   => 'Custom Tour',
            'status_new'       => 'New',
            'status_reviewing' => 'Under Review',
            'status_accepted'  => 'Accepted',
            'status_rejected'  => 'Rejected',
        ];

        return $lang === 'en' ? $en : $ru;
    }

    private function l(string $key): string
    {
        return $this->t[$key] ?? $key;
    }

    private function offerTypeLabel(string $type): string
    {
        return $this->l('type_' . $type) ?: ucfirst($type);
    }

    private function statusLabel(string $status): string
    {
        return $this->l('status_' . $status) ?: ucfirst($status);
    }

    private function buildHtml(Offer $offer): string
    {
        $logoPath = public_path('logo.jpeg');
        $logoHtml = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoHtml = '<img src="data:image/jpeg;base64,' . $logoData . '" style="width:52px;height:52px;border-radius:8px;object-fit:cover;" />';
        }

        $statusColors = [
            'new' => '#4A90D9', 'reviewing' => '#F5A623',
            'accepted' => '#27AE60', 'rejected' => '#E74C3C',
        ];
        $statusColor = $statusColors[$offer->status] ?? '#666';

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a2e; margin: 0; padding: 24px; }
.section-title { background: #1A2744; color: white; padding: 7px 12px; font-size: 11px; font-weight: bold; margin: 18px 0 8px; }
table { width: 100%; border-collapse: collapse; }
th { background: #0D1B2A; color: white; padding: 6px 8px; font-size: 9px; text-align: left; }
td { padding: 6px 8px; font-size: 9px; border-bottom: 1px solid #e8ecf0; vertical-align: top; }
tr:nth-child(even) td { background: #F5F8FC; }
.info-table td { border-bottom: none; padding: 4px 8px; }
.info-table tr:nth-child(even) td { background: none; }
.highlight { background: #EBF5FB; border-left: 4px solid #1A2744; padding: 10px 14px; margin: 10px 0; font-size: 11px; }
.price-box { background: #1A2744; color: white; padding: 12px 16px; margin: 4px 0; }
.price-box .amount { font-size: 20px; font-weight: bold; color: #4A90D9; }
.price-box .label { font-size: 9px; color: #aac4e0; }
.list-item { padding: 3px 0; font-size: 9px; }
.list-item:before { content: "• "; color: #4A90D9; font-weight: bold; }
.footer { margin-top: 30px; border-top: 2px solid #1A2744; padding-top: 10px; text-align: center; font-size: 8px; color: #888; }
.badge { display: inline-block; padding: 3px 10px; border-radius: 3px; font-size: 9px; font-weight: bold; color: white; }
</style></head><body>';

        // ── Header ─────────────────────────────────────────────────────────
        $html .= '<table style="width:100%; margin-bottom:20px; border-bottom:3px solid #1A2744; padding-bottom:14px;">
<tr>
  <td style="width:38%; vertical-align:middle;">
    <table><tr>
      <td style="vertical-align:middle; padding-right:10px;">' . $logoHtml . '</td>
      <td style="vertical-align:middle;">
        <div style="font-size:20px; font-weight:bold; color:#1A2744; letter-spacing:2px;">SOGDA TOUR</div>
        <div style="font-size:10px; color:#4A90D9;">Travel Management System</div>
      </td>
    </tr></table>
  </td>
  <td style="text-align:center; vertical-align:middle;">
    <div style="font-size:20px; font-weight:bold; color:#1A2744;">' . $this->l('title') . '</div>
    <div style="margin-top:6px;">
      <span class="badge" style="background:' . $statusColor . ';">' . $this->statusLabel($offer->status) . '</span>
    </div>
  </td>
  <td style="text-align:right; vertical-align:middle; font-size:9px; color:#666;">
    ' . $this->l('offer_no') . ' <strong>#' . $offer->id . '</strong><br>
    ' . $this->l('date') . ': ' . now()->format('d.m.Y') . '<br>
    ' . ($offer->validity_date ? $this->l('valid_until') . ': ' . $offer->validity_date->format('d.m.Y') : '') . '
  </td>
</tr></table>';

        // ── Info block ─────────────────────────────────────────────────────
        $countries = is_array($offer->destination_countries)
            ? implode(', ', $offer->destination_countries)
            : ($offer->destination_countries ?? '—');

        $html .= '<table class="info-table" style="background:#F5F8FC; border:1px solid #dce6f0;">
<tr>
  <td style="width:15%; color:#666;">' . $this->l('to') . ':</td>
  <td style="width:35%; font-weight:bold;">' . e($offer->counterparty?->company_name ?? '—') . '</td>
  <td style="width:15%; color:#666;">' . $this->l('offer_type') . ':</td>
  <td style="width:35%;">' . $this->offerTypeLabel($offer->offer_type) . '</td>
</tr>
<tr>
  <td style="color:#666;">' . $this->l('destinations') . ':</td>
  <td><strong>' . e($countries) . '</strong></td>
  <td style="color:#666;">' . $this->l('period') . ':</td>
  <td>' . ($offer->start_date?->format('d.m.Y') ?? '—') . ' – ' . ($offer->end_date?->format('d.m.Y') ?? '—') . '</td>
</tr>
<tr>
  <td style="color:#666;">' . $this->l('pax') . ':</td>
  <td>' . sprintf($this->l('pax_range'), $offer->pax_min, $offer->pax_max) . '</td>
  <td style="color:#666;">' . $this->l('currency') . ':</td>
  <td>' . ($offer->currency ?? 'USD') . '</td>
</tr></table>';

        // ── Price boxes ─────────────────────────────────────────────────────
        $html .= '<table style="width:100%; margin:14px 0;"><tr>';
        if ($offer->price_per_person_usd > 0) {
            $html .= '<td style="width:50%; padding-right:6px;">
  <div class="price-box">
    <div class="label">' . $this->l('price_pp') . '</div>
    <div class="amount">$ ' . number_format($offer->price_per_person_usd, 2) . '</div>
  </div>
</td>';
        }
        if ($offer->total_price_usd > 0) {
            $html .= '<td style="width:50%; padding-left:6px;">
  <div class="price-box">
    <div class="label">' . $this->l('total') . '</div>
    <div class="amount">$ ' . number_format($offer->total_price_usd, 2) . '</div>
  </div>
</td>';
        }
        $html .= '</tr></table>';

        // ── Includes ────────────────────────────────────────────────────────
        $includes = is_array($offer->includes) ? $offer->includes : [];
        $excludes = is_array($offer->excludes) ? $offer->excludes : [];

        if (!empty($includes) || !empty($excludes)) {
            $html .= '<table style="width:100%; margin-bottom:14px;"><tr>';
            if (!empty($includes)) {
                $html .= '<td style="width:50%; vertical-align:top; padding-right:8px;">
  <div class="section-title" style="margin-top:0;">' . $this->l('includes') . '</div>';
                foreach ($includes as $item) {
                    $html .= '<div class="list-item">' . e($item) . '</div>';
                }
                $html .= '</td>';
            }
            if (!empty($excludes)) {
                $html .= '<td style="width:50%; vertical-align:top; padding-left:8px;">
  <div class="section-title" style="margin-top:0;">' . $this->l('excludes') . '</div>';
                foreach ($excludes as $item) {
                    $html .= '<div class="list-item">' . e($item) . '</div>';
                }
                $html .= '</td>';
            }
            $html .= '</tr></table>';
        }

        // ── Itinerary ───────────────────────────────────────────────────────
        if ($offer->itinerary) {
            $html .= '<div class="section-title">' . $this->l('itinerary') . '</div>';
            $html .= '<div style="padding:10px; background:#FAFBFC; border:1px solid #dce6f0; font-size:9px; line-height:1.6;">'
                . nl2br(e($offer->itinerary)) . '</div>';
        }

        // ── Notes ───────────────────────────────────────────────────────────
        if ($offer->notes) {
            $html .= '<div class="section-title">' . $this->l('notes') . '</div>';
            $html .= '<div class="highlight">' . nl2br(e($offer->notes)) . '</div>';
        }

        // ── Footer ───────────────────────────────────────────────────────────
        $html .= '<div class="footer">
  ' . $this->l('footer_note') . '<br>
  ' . $this->l('contact') . ' &nbsp;|&nbsp; ' . now()->format('d.m.Y H:i') . '
</div>';

        $html .= '</body></html>';
        return $html;
    }
}
