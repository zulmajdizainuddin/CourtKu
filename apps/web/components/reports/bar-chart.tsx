"use client";

import { useState } from "react";
import { formatRinggit } from "@/lib/money";
import { type ChartBar, niceMax } from "@/lib/report";

export type ChartMeasure = "bookings" | "money";

const valueOf = (bar: ChartBar, measure: ChartMeasure) => (measure === "bookings" ? bar.bookings : bar.booked_sen);

/** "2 bookings" / "RM 60.00" */
export function formatMeasure(value: number, measure: ChartMeasure): string {
  return measure === "bookings" ? `${value} ${value === 1 ? "booking" : "bookings"}` : formatRinggit(value);
}

/** Axis numbers: whole ringgit, "RM 1.2K" from 1,000 up, so they stay short. */
function axisLabel(value: number, measure: ChartMeasure): string {
  if (measure === "bookings") return String(value);
  const ringgit = value / 100;
  return ringgit >= 1000 ? `RM ${(ringgit / 1000).toFixed(ringgit % 1000 === 0 ? 0 : 1)}K` : `RM ${ringgit}`;
}

/**
 * One series of bars (bookings or money per day/month) for the Reports tab (FR-32).
 * Thin bars with rounded tops and a 2px gap between them, faint gridlines at 0, half and the top,
 * and a tooltip on hover or keyboard focus. Every value is also in the page's table view.
 */
export function BarChart({ bars, measure }: { bars: ChartBar[]; measure: ChartMeasure }) {
  const [active, setActive] = useState<number | null>(null);
  const max = niceMax(Math.max(0, ...bars.map((bar) => valueOf(bar, measure))));
  // At most about 8 dates under the chart, always the first and the last.
  const every = Math.max(1, Math.ceil(bars.length / 8));
  const shown = (i: number) => i === 0 || i === bars.length - 1 || (i % every === 0 && bars.length - 1 - i >= every / 2);
  const activeBar = active !== null ? bars[active] : undefined;

  return (
    <div className="grid grid-cols-[auto_1fr] gap-x-2">
      {/* The scale: the top, half way and zero. */}
      <div className="relative h-48 w-14 text-right text-xs tabular-nums text-muted-foreground" aria-hidden>
        <span className="absolute right-0 top-0 -translate-y-1/2">{axisLabel(max, measure)}</span>
        <span className="absolute right-0 top-1/2 -translate-y-1/2">{axisLabel(max / 2, measure)}</span>
        <span className="absolute bottom-0 right-0 translate-y-1/2">{axisLabel(0, measure)}</span>
      </div>

      <div className="relative h-48">
        {/* Hairline gridlines behind the bars. */}
        <div className="absolute inset-x-0 top-0 border-t border-border" aria-hidden />
        <div className="absolute inset-x-0 top-1/2 border-t border-border" aria-hidden />
        <div className="absolute inset-x-0 bottom-0 border-t border-border" aria-hidden />

        <div className="relative flex h-full items-end gap-[2px]" onMouseLeave={() => setActive(null)}>
          {bars.map((bar, i) => {
            const value = valueOf(bar, measure);
            return (
              // The whole column is the hover target, not just the bar, so short bars are easy to point at.
              <button
                key={bar.key}
                type="button"
                className="flex h-full min-w-0 flex-1 cursor-default items-end justify-center rounded-sm outline-none focus-visible:ring-2 focus-visible:ring-ring"
                aria-label={`${bar.longLabel}: ${formatMeasure(bar.bookings, "bookings")}, ${formatRinggit(bar.booked_sen)}`}
                onMouseEnter={() => setActive(i)}
                onFocus={() => setActive(i)}
                onBlur={() => setActive(null)}
              >
                <span
                  className={`block w-full max-w-6 rounded-t-[4px] bg-series-1 transition-opacity ${active !== null && active !== i ? "opacity-60" : ""}`}
                  style={{ height: `${(value / max) * 100}%` }}
                />
              </button>
            );
          })}
        </div>

        {activeBar && active !== null && (
          // Inside the chart (cards cut off anything sticking out), beside the bar rather than over it:
          // to its right on the left half, to its left on the right half. The buttons already say the
          // same to screen readers, so this box is for the eyes only.
          <div
            aria-hidden
            className="pointer-events-none absolute top-0 z-10 whitespace-nowrap rounded-md border bg-popover px-3 py-2 text-xs shadow-md"
            style={{
              left: `${((active + 0.5) / bars.length) * 100}%`,
              transform: (active + 0.5) / bars.length > 0.5 ? "translateX(calc(-100% - 12px))" : "translateX(12px)",
            }}
          >
            <p className="text-sm font-semibold tabular-nums text-popover-foreground">{formatMeasure(valueOf(activeBar, measure), measure)}</p>
            <p className="tabular-nums text-muted-foreground">
              {measure === "bookings" ? formatRinggit(activeBar.booked_sen) : formatMeasure(activeBar.bookings, "bookings")}
            </p>
            <p className="text-muted-foreground">{activeBar.longLabel}</p>
          </div>
        )}
      </div>

      {/* Dates under the bars, lined up with their columns. */}
      <div />
      <div className="relative mt-2 h-4 text-xs text-muted-foreground" aria-hidden>
        {bars.map((bar, i) =>
          shown(i) ? (
            <span
              key={bar.key}
              className="absolute -translate-x-1/2 whitespace-nowrap"
              style={{ left: `${((i + 0.5) / bars.length) * 100}%` }}
            >
              {bar.label}
            </span>
          ) : null,
        )}
      </div>
    </div>
  );
}
