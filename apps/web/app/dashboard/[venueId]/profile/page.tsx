"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { useState } from "react";
import { useForm, useWatch } from "react-hook-form";
import { FormAlert, FormField, SelectField, TextareaField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { VenuePage } from "@/components/venues/venue-page";
import { type ManageVenue, useUpdateVenue } from "@/features/venues/hooks";
import { FACILITIES, facilityLabel } from "@/lib/facilities";
import { applyApiErrors } from "@/lib/form-errors";
import { toProfilePayload, type VenueProfileInput, venueProfileSchema } from "@/lib/schemas/venue";
import { MALAYSIAN_STATES, stateLabel } from "@/lib/states";

/** The venue profile (FR-06): what players see on the public page. Owners edit it; staff can only read it. */
export default function VenueProfilePage() {
  return (
    <VenuePage>{(venue) => (venue.role === "owner" ? <ProfileForm venue={venue} /> : <ProfileView venue={venue} />)}</VenuePage>
  );
}

/** The saved venue as form values: empty fields become "" so the inputs stay controlled. */
function toFormValues(venue: ManageVenue): VenueProfileInput {
  return {
    name: venue.name,
    slug: venue.slug,
    state: venue.state,
    description: venue.description ?? "",
    address_line: venue.address_line ?? "",
    city: venue.city ?? "",
    postcode: venue.postcode ?? "",
    phone: venue.phone ?? "",
    facilities: venue.facilities,
  };
}

function ProfileForm({ venue }: { venue: ManageVenue }) {
  const updateVenue = useUpdateVenue(String(venue.id));
  const [formError, setFormError] = useState<string>();
  const [saved, setSaved] = useState(false);
  const form = useForm<VenueProfileInput>({
    resolver: zodResolver(venueProfileSchema),
    defaultValues: toFormValues(venue),
  });
  const { errors, isSubmitting, isDirty } = form.formState;
  const slug = useWatch({ control: form.control, name: "slug" });
  // Once players can see the venue, its web address must not change (links would break).
  const slugLocked = venue.status !== "pending";

  const onSubmit = form.handleSubmit(async (values) => {
    setFormError(undefined);
    setSaved(false);
    try {
      const updated = await updateVenue.mutateAsync(toProfilePayload(values));
      form.reset(toFormValues(updated)); // shows e.g. the phone in +60 format, and clears "unsaved changes"
      setSaved(true);
    } catch (e) {
      setFormError(applyApiErrors(e, form.setError));
    }
  });

  return (
    <form onSubmit={onSubmit} className="grid gap-6" noValidate>
      <Card>
        <CardHeader>
          <CardTitle>About</CardTitle>
          <CardDescription>Players see this on your venue&apos;s page.</CardDescription>
        </CardHeader>
        <CardContent className="grid gap-4">
          <FormField label="Venue name" error={errors.name?.message} {...form.register("name")} />
          <FormField
            label="Web address"
            autoCapitalize="none"
            spellCheck={false}
            readOnly={slugLocked}
            className={slugLocked ? "h-11 bg-muted text-base text-muted-foreground" : undefined}
            hint={
              slugLocked
                ? `/v/${slug}. Locked now that your venue is live, so links players saved keep working.`
                : `Players will find you at /v/${slug || "your-venue"}. You can change it until CourtKu approves your venue.`
            }
            error={errors.slug?.message}
            {...form.register("slug")}
          />
          <TextareaField
            label="Description (optional)"
            rows={4}
            hint="A few lines for players: your courts, the floor, anything special."
            error={errors.description?.message}
            {...form.register("description")}
          />
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Address and contact</CardTitle>
        </CardHeader>
        <CardContent className="grid gap-4">
          <FormField
            label="Address (optional)"
            placeholder="Lot 12, Jalan Industri 3"
            autoComplete="street-address"
            error={errors.address_line?.message}
            {...form.register("address_line")}
          />
          <div className="grid gap-4 sm:grid-cols-2">
            <FormField
              label="City (optional)"
              placeholder="Sungai Buloh"
              autoComplete="address-level2"
              error={errors.city?.message}
              {...form.register("city")}
            />
            <FormField
              label="Postcode (optional)"
              placeholder="47000"
              inputMode="numeric"
              maxLength={5}
              autoComplete="postal-code"
              error={errors.postcode?.message}
              {...form.register("postcode")}
            />
          </div>
          <SelectField label="State" options={MALAYSIAN_STATES} error={errors.state?.message} {...form.register("state")} />
          <FormField
            label="Phone (optional)"
            type="tel"
            placeholder="012-345 6789"
            autoComplete="tel"
            hint="Shown to players, e.g. 012-345 6789 or 03-1234 5678."
            error={errors.phone?.message}
            {...form.register("phone")}
          />
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Facilities</CardTitle>
          <CardDescription>Tick everything your venue offers.</CardDescription>
        </CardHeader>
        <CardContent>
          <fieldset>
            <legend className="sr-only">Facilities</legend>
            <div className="grid grid-cols-1 gap-2 sm:grid-cols-2">
              {FACILITIES.map((facility) => (
                <label
                  key={facility.value}
                  className="flex min-h-11 cursor-pointer items-center gap-3 rounded-lg px-3 text-sm ring-1 ring-foreground/10 has-checked:bg-muted"
                >
                  <input type="checkbox" value={facility.value} className="size-4 accent-foreground" {...form.register("facilities")} />
                  {facility.label}
                </label>
              ))}
            </div>
          </fieldset>
        </CardContent>
      </Card>

      <div className="grid gap-3">
        <FormAlert message={formError} />
        {saved && !isDirty && <FormAlert tone="success" message="Saved." />}
        <Button type="submit" size="lg" className="h-11 justify-self-start px-6" disabled={isSubmitting}>
          {isSubmitting ? "Saving..." : "Save profile"}
        </Button>
      </div>
    </form>
  );
}

/** Staff see the profile but can't change it; the API refuses their changes anyway. */
function ProfileView({ venue }: { venue: ManageVenue }) {
  const rows: [string, string | null][] = [
    ["Web address", `/v/${venue.slug}`],
    ["Description", venue.description],
    ["Address", [venue.address_line, [venue.postcode, venue.city].filter(Boolean).join(" ")].filter(Boolean).join(", ") || null],
    ["State", stateLabel(venue.state)],
    ["Phone", venue.phone],
    ["Facilities", venue.facilities.map(facilityLabel).join(", ") || null],
  ];

  return (
    <Card>
      <CardHeader>
        <CardTitle>Profile</CardTitle>
        <CardDescription>Only the venue owner can change the profile.</CardDescription>
      </CardHeader>
      <CardContent>
        <dl className="grid gap-3 text-sm sm:grid-cols-[10rem_1fr]">
          {rows.map(([label, value]) => (
            <div key={label} className="contents">
              <dt className="text-muted-foreground">{label}</dt>
              <dd className="break-words whitespace-pre-line">{value ?? "Not set yet"}</dd>
            </div>
          ))}
        </dl>
      </CardContent>
    </Card>
  );
}
