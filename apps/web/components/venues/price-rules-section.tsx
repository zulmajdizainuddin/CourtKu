"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import Link from "next/link";
import { useState } from "react";
import { useForm, useWatch } from "react-hook-form";
import { FormAlert, FormField, SelectField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { type Court, useCourts } from "@/features/venues/courts";
import { useAddPriceRule, useDeletePriceRule, usePriceRules, useUpdatePriceRule } from "@/features/venues/price-rules";
import { ApiError } from "@/lib/api";
import { applyApiErrors } from "@/lib/form-errors";
import { senToInput } from "@/lib/money";
import { closesNextDay, DAY_NAMES, formatTime, HALF_HOURS, WEEK_ORDER } from "@/lib/opening-hours";
import { formatRuleTimes, formatWeekdays, type PriceRule } from "@/lib/price-rules";
import { type PriceRuleInput, priceRuleSchema, toPriceRulePayload } from "@/lib/schemas/price-rule";

/**
 * Peak and off-peak prices (FR-10). Each half hour costs, from the first that matches:
 * a price for that court, a price for all courts, or the court's normal price (ADR-020).
 * Owners add, edit and remove them; staff can only read them.
 */
export function PriceRulesSection({ venueId, canEdit }: { venueId: string; canEdit: boolean }) {
  const rules = usePriceRules(venueId);
  const courts = useCourts(venueId);
  const remove = useDeletePriceRule(venueId);
  const [editingId, setEditingId] = useState<number | "new" | null>(null);
  const courtList = courts.data ?? [];
  const ruleList = rules.data ?? [];

  // All courts first, then one group per court that has its own prices, in the courts' order.
  const groups = [
    { key: "all", title: "All courts", rules: ruleList.filter((r) => r.court_id === null) },
    ...courtList.map((court) => ({
      key: String(court.id),
      title: court.name,
      rules: ruleList.filter((r) => r.court_id === court.id),
    })),
  ].filter((group) => group.rules.length > 0);

  const onDelete = (rule: PriceRule) => {
    if (window.confirm(`Delete ${rule.name ?? "this price"}? Those hours go back to the normal price.`)) {
      remove.mutate(rule.id);
    }
  };

  const ruleForm = (rule?: PriceRule) => (
    <PriceRuleForm venueId={venueId} courts={courtList} rule={rule} onDone={() => setEditingId(null)} />
  );

  return (
    <div className="grid gap-6">
      {(rules.isPending || courts.isPending) && <p className="text-sm text-muted-foreground">Loading...</p>}
      {(rules.isError || courts.isError) && (
        <p className="text-sm text-destructive">Could not load the prices. Please refresh the page.</p>
      )}

      {courts.isSuccess && (
        <section className="grid gap-2">
          <h2 className="font-medium">Normal prices</h2>
          {courtList.length === 0 ? (
            <p className="text-sm text-muted-foreground">No courts yet.</p>
          ) : (
            <ul className="grid gap-1 text-sm">
              {courtList.map((court) => (
                <li key={court.id} className="flex flex-wrap justify-between gap-x-4">
                  <span>{court.name}</span>
                  {court.base_price ? (
                    <span>{court.base_price} an hour</span>
                  ) : (
                    <span className="text-amber-800 dark:text-amber-300">No price yet: hidden from players</span>
                  )}
                </li>
              ))}
            </ul>
          )}
          <p className="text-sm text-muted-foreground">
            Change these on the{" "}
            <Link href={`/dashboard/${venueId}/courts`} className="underline">
              Courts
            </Link>{" "}
            page. They apply whenever no peak or off-peak price below does.
          </p>
        </section>
      )}

      {rules.isSuccess && courts.isSuccess && (
        <section className="grid gap-3">
          <h2 className="font-medium">Peak and off-peak prices</h2>
          {ruleList.length === 0 && editingId !== "new" && (
            <p className="text-sm text-muted-foreground">
              None yet: every court costs its normal price all day.
              {canEdit && " Add one below, e.g. a higher price on weekday evenings."}
            </p>
          )}

          {groups.map((group) => (
            <div key={group.key} className="grid gap-2">
              <h3 className="text-sm text-muted-foreground">{group.title}</h3>
              <ul className="grid gap-2">
                {group.rules.map((rule) =>
                  editingId === rule.id ? (
                    <li key={rule.id} className="rounded-lg p-3 ring-1 ring-foreground/10">
                      {ruleForm(rule)}
                    </li>
                  ) : (
                    <li
                      key={rule.id}
                      className="flex flex-wrap items-center gap-2 rounded-lg px-3 py-2 ring-1 ring-foreground/10"
                    >
                      <div className="min-w-40 flex-1">
                        {rule.name && <p className="font-medium">{rule.name}</p>}
                        <p className="text-sm">
                          {formatWeekdays(rule.weekdays)} · {formatRuleTimes(rule)}
                        </p>
                        <p className="text-sm">{rule.price} an hour</p>
                      </div>
                      {canEdit && (
                        <div className="flex items-center gap-1">
                          <Button
                            variant="ghost"
                            aria-label={`Edit ${rule.name ?? formatWeekdays(rule.weekdays)}`}
                            onClick={() => setEditingId(rule.id)}
                          >
                            Edit
                          </Button>
                          <Button
                            variant="ghost"
                            className="text-destructive"
                            aria-label={`Delete ${rule.name ?? formatWeekdays(rule.weekdays)}`}
                            disabled={remove.isPending}
                            onClick={() => onDelete(rule)}
                          >
                            Delete
                          </Button>
                        </div>
                      )}
                    </li>
                  ),
                )}
              </ul>
            </div>
          ))}
          <FormAlert message={remove.error instanceof ApiError ? remove.error.message : undefined} />

          {canEdit &&
            (editingId === "new" ? (
              <div className="rounded-lg p-3 ring-1 ring-foreground/10">{ruleForm()}</div>
            ) : (
              <Button variant="outline" className="h-11 justify-self-start px-6" onClick={() => setEditingId("new")}>
                Add a price
              </Button>
            ))}
        </section>
      )}
    </div>
  );
}

const QUICK_DAYS = [
  { label: "Weekdays", days: [1, 2, 3, 4, 5] },
  { label: "Weekends", days: [6, 0] },
  { label: "Every day", days: [0, 1, 2, 3, 4, 5, 6] },
];

/** Adds a peak or off-peak price (no rule given) or changes one. */
function PriceRuleForm({
  venueId,
  courts,
  rule,
  onDone,
}: {
  venueId: string;
  courts: Court[];
  rule?: PriceRule;
  onDone: () => void;
}) {
  const add = useAddPriceRule(venueId);
  const update = useUpdatePriceRule(venueId);
  const [formError, setFormError] = useState<string>();
  const form = useForm<PriceRuleInput>({
    resolver: zodResolver(priceRuleSchema),
    defaultValues: rule
      ? {
          name: rule.name ?? "",
          court_id: rule.court_id === null ? "" : String(rule.court_id),
          weekdays: rule.weekdays.map(String),
          starts_at: rule.starts_at,
          ends_at: rule.ends_at,
          price: senToInput(rule.price_sen),
        }
      : { name: "", court_id: "", weekdays: [], starts_at: "18:00", ends_at: "00:00", price: "" },
  });
  const { errors, isSubmitting } = form.formState;
  const startsAt = useWatch({ control: form.control, name: "starts_at" });

  const onSubmit = form.handleSubmit(async (values) => {
    setFormError(undefined);
    const input = toPriceRulePayload(values);
    try {
      if (rule) await update.mutateAsync({ ruleId: rule.id, input });
      else await add.mutateAsync(input);
      onDone();
    } catch (e) {
      setFormError(applyApiErrors(e, form.setError));
    }
  });

  // The end times start just after the start time, e.g. from 6:00 pm: 6:30 pm ... midnight, 12:30 am (next day) ...
  const startIndex = HALF_HOURS.indexOf(startsAt);
  const endOptions = HALF_HOURS.map((_, i) => HALF_HOURS[(startIndex + 1 + i) % HALF_HOURS.length])
    .filter((time) => time !== startsAt)
    .map((time) => ({
      value: time,
      label: time === "00:00" ? "Midnight" : `${formatTime(time)}${closesNextDay(startsAt, time) ? " (next day)" : ""}`,
    }));

  return (
    <form onSubmit={onSubmit} className="grid gap-4" noValidate>
      <FormField
        label="Name (optional)"
        placeholder="Weekday peak"
        hint="Only you and your staff see it."
        error={errors.name?.message}
        {...form.register("name")}
      />
      <SelectField
        label="Courts"
        options={[
          { value: "", label: "All courts" },
          ...courts.map((court) => ({ value: String(court.id), label: court.name })),
        ]}
        hint="A price for one court wins over a price for all courts."
        error={errors.court_id?.message}
        {...form.register("court_id")}
      />

      <fieldset className="grid gap-2">
        <legend className="mb-1.5 text-sm font-medium">Days</legend>
        <div className="flex flex-wrap gap-2">
          {WEEK_ORDER.map((day) => (
            <label
              key={day}
              className="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg px-3 text-sm ring-1 ring-foreground/10 has-checked:bg-muted"
            >
              <input type="checkbox" value={String(day)} className="size-4 accent-foreground" {...form.register("weekdays")} />
              <span aria-hidden>{DAY_NAMES[day].slice(0, 3)}</span>
              <span className="sr-only">{DAY_NAMES[day]}</span>
            </label>
          ))}
        </div>
        <div className="flex flex-wrap gap-2">
          {QUICK_DAYS.map((quick) => (
            <Button
              key={quick.label}
              type="button"
              variant="ghost"
              size="sm"
              onClick={() => form.setValue("weekdays", quick.days.map(String), { shouldValidate: form.formState.isSubmitted })}
            >
              {quick.label}
            </Button>
          ))}
        </div>
        {errors.weekdays && <p className="text-sm text-destructive">{errors.weekdays.message}</p>}
      </fieldset>

      <div className="grid gap-4 sm:grid-cols-2">
        <SelectField
          label="From"
          options={HALF_HOURS.map((time) => ({ value: time, label: formatTime(time) }))}
          error={errors.starts_at?.message}
          {...form.register("starts_at", {
            // A price can't start and end at the same time, so the end moves on half an hour.
            onChange: (e: React.ChangeEvent<HTMLSelectElement>) => {
              if (form.getValues("ends_at") === e.target.value) {
                form.setValue("ends_at", HALF_HOURS[(HALF_HOURS.indexOf(e.target.value) + 1) % HALF_HOURS.length]);
              }
            },
          })}
        />
        <SelectField
          label="To"
          options={endOptions}
          hint="After midnight still counts as the day it started, e.g. Friday 6:00 pm – 2:00 am."
          error={errors.ends_at?.message}
          {...form.register("ends_at")}
        />
      </div>

      <FormField
        label="Price per hour (RM)"
        inputMode="decimal"
        placeholder="30.00"
        hint="In 10-sen steps, e.g. 30.00 or 30.50."
        error={errors.price?.message}
        {...form.register("price")}
      />

      <FormAlert message={formError} />
      <div className="flex flex-wrap gap-2">
        <Button type="submit" className="h-11 px-6" disabled={isSubmitting}>
          {isSubmitting ? "Saving..." : rule ? "Save price" : "Add price"}
        </Button>
        <Button type="button" variant="outline" className="h-11" onClick={onDone}>
          Cancel
        </Button>
      </div>
    </form>
  );
}
