"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { useState } from "react";
import { useForm, useWatch } from "react-hook-form";
import { FormAlert, FormField, SelectField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { type DaySchedule, useAddBlock, useAddCounterBooking } from "@/features/venues/schedule";
import { lengthOptions } from "@/lib/booking";
import { formatDuration } from "@/lib/duration";
import { applyApiErrors } from "@/lib/form-errors";
import { formatTime } from "@/lib/opening-hours";
import {
  type BlockInput,
  blockSchema,
  type CounterBookingInput,
  counterBookingSchema,
  toBlockPayload,
  toCounterBookingPayload,
} from "@/lib/schemas/schedule";
import { sportLabel } from "@/lib/sports";

type BookingRules = { slot_minutes: number; min_minutes: number; max_minutes: number };

/** The day's half hours as start-time choices: "6:00 pm", "12:30 am (next day)". */
function timeOptions(schedule: DaySchedule) {
  return schedule.times.map((t) => ({ value: t.starts_at, label: `${formatTime(t.time)}${t.next_day ? " (next day)" : ""}` }));
}

const choiceClass =
  "flex min-h-11 cursor-pointer items-center gap-2 rounded-lg px-3 text-sm ring-1 ring-foreground/10 has-checked:bg-muted";

/**
 * A walk-in or phone booking (FR-20). The same booking engine as online bookings, so it can never
 * clash with one, and the same time rules as players (the owner's choice).
 */
export function CounterBookingForm({
  venueId,
  schedule,
  rules,
  onDone,
}: {
  venueId: string;
  schedule: DaySchedule;
  rules: BookingRules;
  onDone: () => void;
}) {
  const add = useAddCounterBooking(venueId);
  const [formError, setFormError] = useState<string>();
  const courts = schedule.courts.filter((c) => c.bookable);
  const lengths = lengthOptions(rules);
  const form = useForm<CounterBookingInput>({
    resolver: zodResolver(counterBookingSchema),
    defaultValues: {
      source: "walk_in",
      court_id: courts[0] ? String(courts[0].id) : "",
      sport: courts[0]?.sports[0] ?? "",
      starts_at: schedule.times[0]?.starts_at ?? "",
      minutes: String(lengths[0]),
      customer_name: "",
      customer_phone: "",
    },
  });
  const { errors, isSubmitting } = form.formState;
  const courtId = useWatch({ control: form.control, name: "court_id" });
  const sports = courts.find((c) => String(c.id) === courtId)?.sports ?? [];

  const onSubmit = form.handleSubmit(async (values) => {
    setFormError(undefined);
    try {
      await add.mutateAsync(toCounterBookingPayload(values));
      onDone();
    } catch (e) {
      setFormError(applyApiErrors(e, form.setError));
    }
  });

  if (courts.length === 0) {
    return <p className="text-sm text-muted-foreground">No court can be booked yet: give a court a price on the Courts page.</p>;
  }

  return (
    <form onSubmit={onSubmit} className="grid gap-4" noValidate>
      <h2 className="font-medium">Add a booking</h2>
      <fieldset className="grid gap-2">
        <legend className="mb-1.5 text-sm font-medium">Booked</legend>
        <div className="flex flex-wrap gap-2">
          <label className={choiceClass}>
            <input type="radio" value="walk_in" className="size-4 accent-foreground" {...form.register("source")} />
            At the counter (walk-in)
          </label>
          <label className={choiceClass}>
            <input type="radio" value="phone" className="size-4 accent-foreground" {...form.register("source")} />
            By phone
          </label>
        </div>
      </fieldset>

      <div className="grid gap-4 sm:grid-cols-2">
        <SelectField
          label="Court"
          options={courts.map((c) => ({ value: String(c.id), label: c.name }))}
          error={errors.court_id?.message}
          {...form.register("court_id", {
            // Another court may offer other sports: start from its first one.
            onChange: (e: React.ChangeEvent<HTMLSelectElement>) =>
              form.setValue("sport", courts.find((c) => String(c.id) === e.target.value)?.sports[0] ?? ""),
          })}
        />
        <SelectField
          label="Sport"
          options={sports.map((s) => ({ value: s, label: sportLabel(s) }))}
          error={errors.sport?.message}
          {...form.register("sport")}
        />
        <SelectField
          label="Start time"
          options={timeOptions(schedule)}
          error={errors.starts_at?.message}
          {...form.register("starts_at")}
        />
        <SelectField
          label="How long"
          options={lengths.map((m) => ({ value: String(m), label: formatDuration(m) }))}
          error={errors.minutes?.message}
          {...form.register("minutes")}
        />
        <FormField label="Customer's name" autoComplete="off" error={errors.customer_name?.message} {...form.register("customer_name")} />
        <FormField
          label="Customer's phone"
          type="tel"
          inputMode="tel"
          placeholder="012-345 6789"
          autoComplete="off"
          error={errors.customer_phone?.message}
          {...form.register("customer_phone")}
        />
      </div>

      <FormAlert message={formError} />
      <div className="flex flex-wrap gap-2">
        <Button type="submit" className="h-11 px-6" disabled={isSubmitting}>
          {isSubmitting ? "Booking..." : "Book, paid at the counter"}
        </Button>
        <Button type="button" variant="outline" className="h-11" onClick={onDone}>
          Cancel
        </Button>
      </div>
    </form>
  );
}

/** Half-hour steps up to 12 hours; a whole day or longer is a closure on the Hours page. */
const BLOCK_LENGTHS = Array.from({ length: 24 }, (_, i) => (i + 1) * 30);

/** Closes one or more courts for repairs or a private event (FR-21). All or nothing. */
export function BlockForm({ venueId, schedule, onDone }: { venueId: string; schedule: DaySchedule; onDone: () => void }) {
  const add = useAddBlock(venueId);
  const [formError, setFormError] = useState<string>();
  const form = useForm<BlockInput>({
    resolver: zodResolver(blockSchema),
    defaultValues: { court_ids: [], starts_at: schedule.times[0]?.starts_at ?? "", minutes: "60", reason: "" },
  });
  const { errors, isSubmitting } = form.formState;

  const onSubmit = form.handleSubmit(async (values) => {
    setFormError(undefined);
    try {
      await add.mutateAsync(toBlockPayload(values));
      onDone();
    } catch (e) {
      setFormError(applyApiErrors(e, form.setError));
    }
  });

  return (
    <form onSubmit={onSubmit} className="grid gap-4" noValidate>
      <h2 className="font-medium">Block a time</h2>
      <fieldset className="grid gap-2">
        <legend className="mb-1.5 text-sm font-medium">Courts</legend>
        <div className="flex flex-wrap gap-2">
          {schedule.courts.map((court) => (
            <label key={court.id} className={choiceClass}>
              <input type="checkbox" value={String(court.id)} className="size-4 accent-foreground" {...form.register("court_ids")} />
              {court.name}
            </label>
          ))}
        </div>
        {errors.court_ids && <p className="text-sm text-destructive">{errors.court_ids.message}</p>}
      </fieldset>

      <div className="grid gap-4 sm:grid-cols-2">
        <SelectField label="From" options={timeOptions(schedule)} error={errors.starts_at?.message} {...form.register("starts_at")} />
        <SelectField
          label="How long"
          options={BLOCK_LENGTHS.map((m) => ({ value: String(m), label: formatDuration(m) }))}
          error={errors.minutes?.message}
          {...form.register("minutes")}
        />
      </div>
      <FormField
        label="Reason"
        placeholder="Floor repair"
        hint="Only you and your staff see it. Players just see the time as taken."
        error={errors.reason?.message}
        {...form.register("reason")}
      />

      <FormAlert message={formError} />
      <div className="flex flex-wrap gap-2">
        <Button type="submit" className="h-11 px-6" disabled={isSubmitting}>
          {isSubmitting ? "Blocking..." : "Block this time"}
        </Button>
        <Button type="button" variant="outline" className="h-11" onClick={onDone}>
          Cancel
        </Button>
      </div>
    </form>
  );
}
