"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { ChevronDown, ChevronUp } from "lucide-react";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { FormAlert, FormField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { type Court, useAddCourt, useCourts, useDeleteCourt, useReorderCourts, useUpdateCourt } from "@/features/venues/courts";
import { ApiError } from "@/lib/api";
import { applyApiErrors } from "@/lib/form-errors";
import { senToInput } from "@/lib/money";
import { type CourtInput, type CourtPayload, courtSchema, toCourtPayload } from "@/lib/schemas/court";
import { SPORTS, sportLabel } from "@/lib/sports";

/** The venue's courts (FR-08). Owners add, edit, reorder and remove them; staff can only read the list. */
export function CourtsSection({ venueId, canEdit }: { venueId: string; canEdit: boolean }) {
  const courts = useCourts(venueId);
  const reorder = useReorderCourts(venueId);
  const remove = useDeleteCourt(venueId);
  const [editingId, setEditingId] = useState<number | "new" | null>(null);
  const list = courts.data ?? [];

  const move = (index: number, by: -1 | 1) => {
    const ids = list.map((c) => c.id);
    [ids[index], ids[index + by]] = [ids[index + by], ids[index]];
    reorder.mutate(ids);
  };

  const onDelete = (court: Court) => {
    // TODO(M3): the API will refuse this once the court has bookings; deactivating stays possible.
    if (window.confirm(`Delete ${court.name} for good? To hide it for a while instead, edit it and untick "Players can book".`)) {
      remove.mutate(court.id);
    }
  };

  const actionError = [reorder.error, remove.error].find((e) => e instanceof ApiError);

  return (
    <div className="grid gap-4">
      {courts.isPending && <p className="text-sm text-muted-foreground">Loading...</p>}
      {courts.isError && <p className="text-sm text-destructive">Could not load the courts. Please refresh the page.</p>}
      {courts.isSuccess && list.length === 0 && editingId !== "new" && (
        <p className="text-sm text-muted-foreground">No courts yet.{canEdit && " Add your first court below."}</p>
      )}

      {list.length > 0 && (
        <ol className="grid gap-2">
          {list.map((court, index) =>
            editingId === court.id ? (
              <li key={court.id} className="rounded-lg p-3 ring-1 ring-foreground/10">
                <CourtForm venueId={venueId} court={court} onDone={() => setEditingId(null)} />
              </li>
            ) : (
              <li key={court.id} className="flex flex-wrap items-center gap-2 rounded-lg px-3 py-2 ring-1 ring-foreground/10">
                {/* At least 10rem wide: on narrow phones the buttons move below instead of squeezing the name. */}
                <div className="min-w-40 flex-1">
                  <p className="font-medium">
                    {court.name}
                    {!court.is_active && (
                      <span className="ml-2 rounded-full bg-muted px-2 py-0.5 text-xs font-normal text-muted-foreground">
                        Inactive
                      </span>
                    )}
                  </p>
                  <p className="text-sm text-muted-foreground">
                    {court.sports.map(sportLabel).join(", ")} · {court.is_indoor ? "Indoor" : "Outdoor"}
                  </p>
                  {court.base_price ? (
                    <p className="text-sm">{court.base_price} an hour</p>
                  ) : (
                    <p className="text-sm text-amber-800 dark:text-amber-300">No price yet: hidden from players</p>
                  )}
                </div>
                {canEdit && (
                  <div className="flex items-center gap-1">
                    <Button
                      variant="ghost"
                      size="icon"
                      aria-label={`Move ${court.name} up`}
                      disabled={index === 0 || reorder.isPending}
                      onClick={() => move(index, -1)}
                    >
                      <ChevronUp />
                    </Button>
                    <Button
                      variant="ghost"
                      size="icon"
                      aria-label={`Move ${court.name} down`}
                      disabled={index === list.length - 1 || reorder.isPending}
                      onClick={() => move(index, 1)}
                    >
                      <ChevronDown />
                    </Button>
                    <Button variant="ghost" aria-label={`Edit ${court.name}`} onClick={() => setEditingId(court.id)}>
                      Edit
                    </Button>
                    <Button
                      variant="ghost"
                      className="text-destructive"
                      aria-label={`Delete ${court.name}`}
                      disabled={remove.isPending}
                      onClick={() => onDelete(court)}
                    >
                      Delete
                    </Button>
                  </div>
                )}
              </li>
            ),
          )}
        </ol>
      )}
      <FormAlert message={actionError instanceof ApiError ? actionError.message : undefined} />

      {canEdit &&
        (editingId === "new" ? (
          <div className="rounded-lg p-3 ring-1 ring-foreground/10">
            <CourtForm venueId={venueId} suggestedName={`Court ${list.length + 1}`} onDone={() => setEditingId(null)} />
          </div>
        ) : (
          <Button variant="outline" className="h-11 justify-self-start px-6" onClick={() => setEditingId("new")}>
            Add a court
          </Button>
        ))}
    </div>
  );
}

/** Adds a court (no court given) or changes one. */
function CourtForm({
  venueId,
  court,
  suggestedName,
  onDone,
}: {
  venueId: string;
  court?: Court;
  suggestedName?: string;
  onDone: () => void;
}) {
  const add = useAddCourt(venueId);
  const update = useUpdateCourt(venueId);
  const [formError, setFormError] = useState<string>();
  const form = useForm<CourtInput>({
    resolver: zodResolver(courtSchema),
    defaultValues: court
      ? {
          name: court.name,
          price: court.base_price_sen === null ? "" : senToInput(court.base_price_sen),
          sports: court.sports,
          location: court.is_indoor ? "indoor" : "outdoor",
          is_active: court.is_active,
        }
      : { name: suggestedName ?? "", price: "", sports: [], location: "indoor", is_active: true },
  });
  const { errors, isSubmitting } = form.formState;

  const onSubmit = form.handleSubmit(async (values) => {
    setFormError(undefined);
    const input: CourtPayload = toCourtPayload(values);
    try {
      if (court) await update.mutateAsync({ courtId: court.id, input });
      else await add.mutateAsync(input);
      onDone();
    } catch (e) {
      setFormError(applyApiErrors(e, form.setError));
    }
  });

  return (
    <form onSubmit={onSubmit} className="grid gap-4" noValidate>
      <FormField label="Court name" error={errors.name?.message} {...form.register("name")} />
      <FormField
        label="Price per hour (RM)"
        inputMode="decimal"
        placeholder="25.00"
        hint="In 10-sen steps, e.g. 25.00 or 25.50. Players don't see the court until it has a price. Peak and off-peak prices come next."
        error={errors.price?.message}
        {...form.register("price")}
      />

      <fieldset className="grid gap-2">
        <legend className="mb-1.5 text-sm font-medium">Sports played here</legend>
        <div className="flex flex-wrap gap-2">
          {SPORTS.map((sport) => (
            <label
              key={sport.value}
              className="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg px-3 text-sm ring-1 ring-foreground/10 has-checked:bg-muted"
            >
              <input type="checkbox" value={sport.value} className="size-4 accent-foreground" {...form.register("sports")} />
              {sport.label}
            </label>
          ))}
        </div>
        {errors.sports && <p className="text-sm text-destructive">{errors.sports.message}</p>}
      </fieldset>

      <fieldset className="grid gap-2">
        <legend className="mb-1.5 text-sm font-medium">Indoor or outdoor</legend>
        <div className="flex flex-wrap gap-2">
          {(["indoor", "outdoor"] as const).map((location) => (
            <label
              key={location}
              className="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg px-3 text-sm ring-1 ring-foreground/10 has-checked:bg-muted"
            >
              <input type="radio" value={location} className="size-4 accent-foreground" {...form.register("location")} />
              {location === "indoor" ? "Indoor" : "Outdoor"}
            </label>
          ))}
        </div>
      </fieldset>

      {court && (
        <label className="flex min-h-11 cursor-pointer items-center gap-3 text-sm">
          <input type="checkbox" className="size-4 accent-foreground" {...form.register("is_active")} />
          Players can book this court (untick to hide it, e.g. during repairs)
        </label>
      )}

      <FormAlert message={formError} />
      <div className="flex flex-wrap gap-2">
        <Button type="submit" className="h-11 px-6" disabled={isSubmitting}>
          {isSubmitting ? "Saving..." : court ? "Save court" : "Add court"}
        </Button>
        <Button type="button" variant="outline" className="h-11" onClick={onDone}>
          Cancel
        </Button>
      </div>
    </form>
  );
}
