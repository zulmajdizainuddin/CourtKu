"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { FormAlert, FormField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { type Closure, useAddClosure, useClosures, useRemoveClosure } from "@/features/venues/hours";
import { ApiError } from "@/lib/api";
import { applyApiErrors } from "@/lib/form-errors";
import { formatDay, todayInMalaysia } from "@/lib/format";
import { type ClosureInput, closureSchema, toClosurePayload } from "@/lib/schemas/closure";

function formatRange(closure: Closure): string {
  return closure.starts_on === closure.ends_on
    ? formatDay(closure.starts_on)
    : `${formatDay(closure.starts_on)} – ${formatDay(closure.ends_on)}`;
}

/** Closed dates (FR-07), e.g. Hari Raya: today's and future ones. Owners add and remove them. */
export function ClosuresSection({ venueId, canEdit }: { venueId: string; canEdit: boolean }) {
  const closures = useClosures(venueId);
  const remove = useRemoveClosure(venueId);

  const onRemove = (closure: Closure) => {
    if (window.confirm(`Remove "${closure.reason}" (${formatRange(closure)})? The venue will be open on those days again.`)) {
      remove.mutate(closure.id);
    }
  };

  return (
    <div className="grid gap-4">
      {closures.isPending && <p className="text-sm text-muted-foreground">Loading...</p>}
      {closures.isError && <p className="text-sm text-destructive">Could not load closed dates. Please refresh the page.</p>}
      {closures.data?.length === 0 && <p className="text-sm text-muted-foreground">No closed dates coming up.</p>}

      {closures.data && closures.data.length > 0 && (
        <ul className="grid gap-2">
          {closures.data.map((closure) => (
            <li
              key={closure.id}
              className="flex flex-wrap items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm ring-1 ring-foreground/10"
            >
              <span>
                <span className="font-medium">{formatRange(closure)}</span>
                <span className="text-muted-foreground"> · {closure.reason}</span>
              </span>
              {canEdit && (
                <Button
                  variant="ghost"
                  disabled={remove.isPending}
                  aria-label={`Remove closure: ${closure.reason}, ${formatRange(closure)}`}
                  onClick={() => onRemove(closure)}
                >
                  Remove
                </Button>
              )}
            </li>
          ))}
        </ul>
      )}
      <FormAlert message={remove.error instanceof ApiError ? remove.error.message : undefined} />

      {canEdit && <AddClosureForm venueId={venueId} />}
    </div>
  );
}

function AddClosureForm({ venueId }: { venueId: string }) {
  const add = useAddClosure(venueId);
  const [formError, setFormError] = useState<string>();
  const [added, setAdded] = useState(false);
  const form = useForm<ClosureInput>({
    resolver: zodResolver(closureSchema),
    defaultValues: { starts_on: "", ends_on: "", reason: "" },
  });
  const { errors, isSubmitting } = form.formState;
  const today = todayInMalaysia();

  const onSubmit = form.handleSubmit(async (values) => {
    setFormError(undefined);
    setAdded(false);
    try {
      await add.mutateAsync(toClosurePayload(values));
      form.reset();
      setAdded(true);
    } catch (e) {
      setFormError(applyApiErrors(e, form.setError));
    }
  });

  return (
    <form onSubmit={onSubmit} className="grid gap-4 border-t pt-4" noValidate>
      <p className="text-sm font-medium">Add closed dates</p>
      <div className="grid gap-4 sm:grid-cols-2">
        <FormField label="First day" type="date" min={today} error={errors.starts_on?.message} {...form.register("starts_on")} />
        <FormField
          label="Last day (optional)"
          type="date"
          min={today}
          hint="Leave empty for one day."
          error={errors.ends_on?.message}
          {...form.register("ends_on")}
        />
      </div>
      <FormField
        label="Reason"
        placeholder="Hari Raya Aidilfitri"
        maxLength={100}
        hint="Players see this."
        error={errors.reason?.message}
        {...form.register("reason")}
      />
      <FormAlert message={formError} />
      {added && <FormAlert tone="success" message="Closed dates added." />}
      <Button type="submit" variant="outline" className="h-11 justify-self-start px-6" disabled={isSubmitting}>
        {isSubmitting ? "Adding..." : "Add closed dates"}
      </Button>
    </form>
  );
}
