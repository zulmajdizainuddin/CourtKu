<?php

namespace App\Http\Controllers\V1\Manage;

use App\Enums\BookingSource;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Venue;
use App\Services\AvailabilityService;
use App\Services\VenueReport;
use App\Support\MalaysiaTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The owner's reports (FR-32): totals for a range of days, and every booking as a CSV file.
 * Owners only (routes/api.php): it shows the venue's money.
 */
class ReportController extends Controller
{
    /** GET /api/v1/manage/venues/{venue}/reports?from=2026-11-01&to=2026-11-30 */
    public function show(Request $request, Venue $venue): JsonResponse
    {
        return response()->json(['data' => $this->report($request, $venue)->summary()]);
    }

    /** GET /api/v1/manage/venues/{venue}/reports/bookings.csv?from=2026-11-01&to=2026-11-30 */
    public function bookingsCsv(Request $request, Venue $venue): StreamedResponse
    {
        $report = $this->report($request, $venue);
        $filename = "courtku-{$venue->slug}-bookings-{$request->query('from')}-to-{$request->query('to')}.csv";

        return response()->streamDownload(function () use ($report) {
            $out = fopen('php://output', 'w');
            // Tells Excel the file is UTF-8, so names like "Chloé" or "陈" come out right.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Code', 'Date', 'Start', 'End', 'Court', 'Sport', 'Player', 'Phone', 'Email', 'Made', 'Status',
                'Price (RM)', 'Paid', 'Paid how', 'Paid at', 'Refunded (RM)',
            ], escape: '');
            foreach ($report->bookings() as $booking) {
                fputcsv($out, array_map($this->cell(...), $this->row($booking)), escape: '');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function report(Request $request, Venue $venue): VenueReport
    {
        $data = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ], [
            'to.after_or_equal' => 'The end date can\'t be before the start date.',
        ]);

        $from = AvailabilityService::date($data['from']);
        $to = AvailabilityService::date($data['to']);
        if ($from->diffInDays($to) + 1 > VenueReport::MAX_DAYS) {
            throw ValidationException::withMessages(['to' => 'Choose at most a year at once.']);
        }

        return new VenueReport($venue, $from, $to);
    }

    /**
     * One booking as a CSV row, times in Malaysia.
     *
     * @return list<string>
     */
    private function row(Booking $booking): array
    {
        $start = $booking->starts_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE);
        $end = $booking->ends_at->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE);
        $refunded = (int) $booking->payments->sum('refunded_sen');

        return [
            $booking->code,
            $booking->business_date->toDateString(),
            $start->format('H:i'),
            $end->format('H:i'),
            $booking->court->name,
            $booking->sport->name ?? '',
            $booking->user->name ?? $booking->customer_name ?? '',
            $booking->user->phone ?? $booking->customer_phone ?? '',
            $booking->user->email ?? '',
            match ($booking->source) {
                BookingSource::Online => 'Online',
                BookingSource::WalkIn => 'Walk-in',
                BookingSource::Phone => 'Phone',
            },
            ucfirst($booking->status->label()),
            number_format($booking->total_sen / 100, 2, '.', ''),
            $booking->paid_at !== null ? 'Yes' : 'No',
            match ($booking->payment_method) {
                PaymentMethod::Online => 'Online',
                PaymentMethod::Cash => 'Cash',
                PaymentMethod::Qr => 'QR',
                PaymentMethod::Card => 'Card',
                null => '',
            },
            $booking->paid_at?->toImmutable()->setTimezone(MalaysiaTime::TIMEZONE)->format('Y-m-d H:i') ?? '',
            $refunded > 0 ? number_format($refunded / 100, 2, '.', '') : '',
        ];
    }

    /**
     * Stops "CSV injection": a spreadsheet runs a cell starting with = + - @ as a formula, so a player
     * named "=HYPERLINK(...)" could trick the owner. Such cells get a ' in front, which keeps them text.
     * Phone numbers like "+60123456789" and plain numbers are left alone.
     */
    private function cell(string $value): string
    {
        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true) && ! preg_match('/^[+-]?[\d\s]+$/', $value)) {
            return "'".$value;
        }

        return $value;
    }
}
