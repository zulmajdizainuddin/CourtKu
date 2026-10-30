"use client";

import { useState } from "react";
import { FormAlert, SelectField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { VenuePage } from "@/components/venues/venue-page";
import { type BookingSettings, type ManageVenue, useUpdateSettings } from "@/features/venues/hooks";
import { ApiError } from "@/lib/api";
import { durationOptions, formatDuration } from "@/lib/duration";

/** How players may book (FR-13): slot length, shortest and longest booking, days ahead, last-minute notice. */
export default function VenueSettingsPage() {
  return (
    <VenuePage>
      {(venue) => (
        <Card>
          <CardHeader>
            <CardTitle>Booking settings</CardTitle>
            <CardDescription>
              {venue.role === "owner"
                ? "How players book your courts. You can change these any time."
                : "Only the venue owner can change these."}
            </CardDescription>
          </CardHeader>
          <CardContent>
            {venue.role === "owner" ? <SettingsForm venue={venue} /> : <SettingsView venue={venue} />}
          </CardContent>
        </Card>
      )}
    </VenuePage>
  );
}

const WINDOW_OPTIONS = [7, 14, 30, 60, 90].map((days) => ({ value: String(days), label: `${days} days ahead` }));

/** How close to the start players may still book (the same choices the API accepts). */
const NOTICE_OPTIONS = [
  { value: "0", label: "Until the start time" },
  { value: "30", label: "Until 30 minutes before" },
  { value: "60", label: "Until 1 hour before" },
  { value: "120", label: "Until 2 hours before" },
  { value: "1440", label: "Until 1 day before" },
];

function noticeLabel(minutes: number): string {
  return NOTICE_OPTIONS.find((o) => o.value === String(minutes))?.label ?? `Until ${formatDuration(minutes)} before`;
}

/** Rounds a length up to a whole number of slots: 90 minutes with 60-minute slots -> 120. */
function fitToSlots(minutes: number, slot: number): number {
  return Math.ceil(minutes / slot) * slot;
}

function SettingsForm({ venue }: { venue: ManageVenue }) {
  const save = useUpdateSettings(String(venue.id));
  const [settings, setSettings] = useState<BookingSettings>({
    slot_minutes: venue.slot_minutes,
    min_minutes: venue.min_minutes,
    max_minutes: venue.max_minutes,
    booking_window_days: venue.booking_window_days,
    min_notice_minutes: venue.min_notice_minutes,
  });
  const [saved, setSaved] = useState(false);
  const [error, setError] = useState<string>();

  const change = (next: Partial<BookingSettings>) => {
    setSaved(false);
    setSettings((current) => {
      const merged = { ...current, ...next };
      // Changing the slot length keeps the lengths valid: whole slots only.
      return {
        ...merged,
        min_minutes: fitToSlots(merged.min_minutes, merged.slot_minutes),
        max_minutes: Math.max(fitToSlots(merged.max_minutes, merged.slot_minutes), fitToSlots(merged.min_minutes, merged.slot_minutes)),
      };
    });
  };

  const durations = (max: number) =>
    durationOptions(settings.slot_minutes, max).map((m) => ({ value: String(m), label: formatDuration(m) }));

  const onSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(undefined);
    try {
      await save.mutateAsync(settings);
      setSaved(true);
    } catch (err) {
      setError(
        err instanceof ApiError
          ? (Object.values(err.fields).flat()[0] ?? err.message)
          : "Could not reach CourtKu. Check your connection and try again.",
      );
    }
  };

  return (
    <form onSubmit={onSubmit} className="grid gap-4" noValidate>
      <SelectField
        label="Slot length"
        name="slot_minutes"
        hint="The steps players choose times in. Bookings start on the hour or half hour either way."
        options={[
          { value: "30", label: "30 minutes" },
          { value: "60", label: "1 hour" },
        ]}
        value={String(settings.slot_minutes)}
        onChange={(e) => change({ slot_minutes: Number(e.target.value) as 30 | 60 })}
      />
      <div className="grid gap-4 sm:grid-cols-2">
        <SelectField
          label="Shortest booking"
          name="min_minutes"
          options={durations(240)}
          value={String(settings.min_minutes)}
          onChange={(e) => change({ min_minutes: Number(e.target.value) })}
        />
        <SelectField
          label="Longest booking"
          name="max_minutes"
          options={durations(720).filter((o) => Number(o.value) >= settings.min_minutes)}
          value={String(settings.max_minutes)}
          onChange={(e) => change({ max_minutes: Number(e.target.value) })}
        />
      </div>
      <SelectField
        label="How far ahead players can book"
        name="booking_window_days"
        options={WINDOW_OPTIONS}
        value={String(settings.booking_window_days)}
        onChange={(e) => change({ booking_window_days: Number(e.target.value) })}
      />
      <SelectField
        label="Players can book"
        name="min_notice_minutes"
        hint="For last-minute players: with 'until the start time', someone can book at 7:50 pm for 8:00 pm if the court is free. Choose more notice if you need time to open up."
        options={NOTICE_OPTIONS}
        value={String(settings.min_notice_minutes)}
        onChange={(e) => change({ min_notice_minutes: Number(e.target.value) })}
      />
      <FormAlert message={error} />
      {saved && <FormAlert tone="success" message="Booking settings saved." />}
      <Button type="submit" size="lg" className="h-11 justify-self-start px-6" disabled={save.isPending}>
        {save.isPending ? "Saving..." : "Save settings"}
      </Button>
    </form>
  );
}

function SettingsView({ venue }: { venue: ManageVenue }) {
  const rows: [string, string][] = [
    ["Slot length", formatDuration(venue.slot_minutes)],
    ["Shortest booking", formatDuration(venue.min_minutes)],
    ["Longest booking", formatDuration(venue.max_minutes)],
    ["Book ahead", `${venue.booking_window_days} days`],
    ["Players can book", noticeLabel(venue.min_notice_minutes)],
  ];
  return (
    <dl className="grid gap-2 text-sm sm:grid-cols-[12rem_1fr]">
      {rows.map(([label, value]) => (
        <div key={label} className="contents">
          <dt className="text-muted-foreground">{label}</dt>
          <dd>{value}</dd>
        </div>
      ))}
    </dl>
  );
}
