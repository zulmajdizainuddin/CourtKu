"use client";

import { useState } from "react";
import { FormAlert } from "@/components/forms/form-field";
import { BarChart, type ChartMeasure, formatMeasure } from "@/components/reports/bar-chart";
import { Button, buttonVariants } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { VenuePage } from "@/components/venues/venue-page";
import { bookingsCsvUrl, useVenueReport, type VenueReport } from "@/features/reports/hooks";
import type { ManageVenue } from "@/features/venues/hooks";
import { ApiError } from "@/lib/api";
import { formatDay, todayInMalaysia } from "@/lib/format";
import { formatRinggit } from "@/lib/money";
import { chartBars, MAX_DAILY_BARS, RANGE_PRESETS, type RangePreset, presetRange, rangeProblem, type ReportRange } from "@/lib/report";

/** Reports (FR-32): what was booked and what came in, for a range of days, and a CSV of every booking. Owners only. */
export default function VenueReportsPage() {
  return (
    <VenuePage>
      {(venue) =>
        venue.role === "owner" ? (
          <Reports venue={venue} />
        ) : (
          <Card>
            <CardHeader>
              <CardTitle>Reports</CardTitle>
              <CardDescription>Only the venue owner can see reports.</CardDescription>
            </CardHeader>
          </Card>
        )
      }
    </VenuePage>
  );
}

function Reports({ venue }: { venue: ManageVenue }) {
  const venueId = String(venue.id);
  const [preset, setPreset] = useState<RangePreset | "custom">("last-30");
  const [range, setRange] = useState<ReportRange>(() => presetRange("last-30", todayInMalaysia()));
  const problem = rangeProblem(range);
  const report = useVenueReport(venueId, range, problem === undefined);

  const choose = (next: RangePreset) => {
    setPreset(next);
    setRange(presetRange(next, todayInMalaysia()));
  };

  return (
    <>
      <Card>
        <CardHeader>
          <CardTitle>Reports</CardTitle>
          <CardDescription>
            Bookings and money for the days you choose. Days are your business days: a game after midnight counts for the
            evening it started.
          </CardDescription>
        </CardHeader>
        <CardContent className="grid gap-4">
          <div className="flex flex-wrap gap-2" role="group" aria-label="Days to show">
            {RANGE_PRESETS.map((option) => (
              <Button
                key={option.value}
                variant={preset === option.value ? "default" : "outline"}
                aria-pressed={preset === option.value}
                onClick={() => choose(option.value)}
              >
                {option.label}
              </Button>
            ))}
            <Button variant={preset === "custom" ? "default" : "outline"} aria-pressed={preset === "custom"} onClick={() => setPreset("custom")}>
              Choose dates
            </Button>
          </div>

          {preset === "custom" && (
            <div className="flex flex-wrap items-end gap-3">
              <div className="grid gap-1.5">
                <Label htmlFor="report-from">From</Label>
                <Input id="report-from" type="date" value={range.from} onChange={(e) => setRange({ ...range, from: e.target.value })} className="h-11" />
              </div>
              <div className="grid gap-1.5">
                <Label htmlFor="report-to">To</Label>
                <Input id="report-to" type="date" value={range.to} onChange={(e) => setRange({ ...range, to: e.target.value })} className="h-11" />
              </div>
            </div>
          )}

          <FormAlert message={problem} />

          {!problem && (
            <div className="flex flex-wrap items-center justify-between gap-3">
              <p className="text-sm text-muted-foreground">
                {formatDay(range.from)} to {formatDay(range.to)}
              </p>
              {/* A plain link, not a page: the browser downloads the file with the login cookie. */}
              <a href={bookingsCsvUrl(venueId, range)} download className={buttonVariants({ variant: "outline", className: "h-11" })}>
                Download bookings (CSV)
              </a>
            </div>
          )}
        </CardContent>
      </Card>

      {report.isPending && !problem && <p className="text-sm text-muted-foreground">Loading...</p>}
      {report.isError && (
        <FormAlert message={report.error instanceof ApiError ? report.error.message : "Could not load the report. Please try again."} />
      )}
      {report.data && !problem && <ReportBody report={report.data} />}
    </>
  );
}

function ReportBody({ report }: { report: VenueReport }) {
  const { totals } = report;
  return (
    <>
      <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <Stat label="Bookings" value={String(totals.bookings)} note={`${totals.hours_booked} hours of court time`} />
        <Stat label="Booked" value={formatRinggit(totals.booked_sen)} note={`${totals.online_bookings} made online, ${totals.counter_bookings} at the counter`} />
        <Stat
          label="Money in"
          value={formatRinggit(totals.collected_sen)}
          note={`${formatRinggit(totals.paid_online_sen)} online, ${formatRinggit(totals.paid_at_venue_sen)} at the venue${
            totals.refunded_sen > 0 ? `, less ${formatRinggit(totals.refunded_sen)} refunded` : ""
          }`}
        />
        <Stat label="Still to collect" value={formatRinggit(totals.to_collect_sen)} note="Booked to pay at the venue, not marked paid yet" />
        <Stat label="Cancelled" value={String(totals.cancelled)} note="Not counted in the bookings above" />
        <Stat label="No-shows" value={String(totals.no_shows)} note="Counted in the bookings: the court was kept for them" />
      </div>

      <DaysCard report={report} />
      <CourtsCard report={report} />
    </>
  );
}

function Stat({ label, value, note }: { label: string; value: string; note: string }) {
  return (
    <Card size="sm">
      <CardContent className="grid gap-1">
        <p className="text-sm text-muted-foreground">{label}</p>
        <p className="text-2xl font-semibold tabular-nums">{value}</p>
        <p className="text-xs text-muted-foreground">{note}</p>
      </CardContent>
    </Card>
  );
}

function DaysCard({ report }: { report: VenueReport }) {
  const [measure, setMeasure] = useState<ChartMeasure>("bookings");
  const [asTable, setAsTable] = useState(false);
  const bars = chartBars(report.days);
  const perMonth = report.days.length > MAX_DAILY_BARS;

  return (
    <Card>
      <CardHeader>
        <CardTitle>{measure === "bookings" ? "Bookings" : "Money booked"} per {perMonth ? "month" : "day"}</CardTitle>
      </CardHeader>
      <CardContent className="grid gap-4">
        <div className="flex flex-wrap justify-between gap-2">
          <div className="flex gap-2" role="group" aria-label="Show">
            <Button size="sm" variant={measure === "bookings" ? "default" : "outline"} aria-pressed={measure === "bookings"} onClick={() => setMeasure("bookings")}>
              Bookings
            </Button>
            <Button size="sm" variant={measure === "money" ? "default" : "outline"} aria-pressed={measure === "money"} onClick={() => setMeasure("money")}>
              Money
            </Button>
          </div>
          <Button size="sm" variant="ghost" aria-pressed={asTable} onClick={() => setAsTable(!asTable)}>
            {asTable ? "Show as chart" : "Show as table"}
          </Button>
        </div>

        {asTable ? (
          <div className="max-h-96 overflow-auto">
            <table className="w-full text-sm">
              <thead className="text-left text-muted-foreground">
                <tr>
                  <th className="py-2 font-medium">{perMonth ? "Month" : "Day"}</th>
                  <th className="py-2 text-right font-medium">Bookings</th>
                  <th className="py-2 text-right font-medium">Booked</th>
                </tr>
              </thead>
              <tbody className="tabular-nums">
                {bars.map((bar) => (
                  <tr key={bar.key} className="border-t">
                    <td className="py-2">{bar.longLabel}</td>
                    <td className="py-2 text-right">{bar.bookings}</td>
                    <td className="py-2 text-right">{formatRinggit(bar.booked_sen)}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <div className="pt-2">
            <BarChart bars={bars} measure={measure} />
          </div>
        )}
        <p className="sr-only" aria-live="polite">
          {`Total: ${formatMeasure(report.totals.bookings, "bookings")}, ${formatRinggit(report.totals.booked_sen)}`}
        </p>
      </CardContent>
    </Card>
  );
}

function CourtsCard({ report }: { report: VenueReport }) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>How full each court was</CardTitle>
        <CardDescription>
          Hours booked out of the {report.open_hours_per_court} hours you were open in these days. Closed dates are left out;
          times blocked for repairs count as open.
        </CardDescription>
      </CardHeader>
      <CardContent className="overflow-x-auto">
        <table className="w-full min-w-[32rem] text-sm">
          <thead className="text-left text-muted-foreground">
            <tr>
              <th className="py-2 font-medium">Court</th>
              <th className="py-2 text-right font-medium">Bookings</th>
              <th className="py-2 text-right font-medium">Hours</th>
              <th className="py-2 text-right font-medium">Booked</th>
              <th className="py-2 pl-6 font-medium">How full</th>
            </tr>
          </thead>
          <tbody className="tabular-nums">
            {report.courts.map((court) => (
              <tr key={court.id} className="border-t">
                <td className="py-2">
                  {court.name}
                  {!court.is_active && <span className="text-muted-foreground"> (switched off)</span>}
                </td>
                <td className="py-2 text-right">{court.bookings}</td>
                <td className="py-2 text-right">{court.hours_booked}</td>
                <td className="py-2 text-right">{formatRinggit(court.booked_sen)}</td>
                <td className="py-2 pl-6">
                  {court.full_percent === null ? (
                    <span className="text-muted-foreground">Not open</span>
                  ) : (
                    <div className="flex items-center gap-2">
                      {/* A meter: the blue part is the share of open hours that was booked. */}
                      <div
                        className="h-2 w-24 overflow-hidden rounded-full bg-muted"
                        role="meter"
                        aria-label={`${court.name} booked`}
                        aria-valuemin={0}
                        aria-valuemax={100}
                        aria-valuenow={court.full_percent}
                      >
                        <div className="h-full rounded-full bg-series-1" style={{ width: `${court.full_percent}%` }} />
                      </div>
                      {/* 1 hour out of 480 rounds to 0%, which would look like nothing was booked. */}
                      <span>{court.full_percent === 0 && court.hours_booked > 0 ? "under 1%" : `${court.full_percent}%`}</span>
                    </div>
                  )}
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </CardContent>
    </Card>
  );
}
