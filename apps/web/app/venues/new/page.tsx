"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { useForm, useWatch } from "react-hook-form";
import { TwoFactorGate } from "@/components/auth/two-factor-gate";
import { VerifyEmailFirst } from "@/components/auth/verify-email-first";
import { FormAlert, FormField, SelectField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { RequireUser } from "@/features/auth/require-user";
import { useCreateVenue } from "@/features/venues/hooks";
import { applyApiErrors } from "@/lib/form-errors";
import { type CreateVenueInput, createVenueSchema } from "@/lib/schemas/venue";
import { slugify } from "@/lib/slug";
import { MALAYSIAN_STATES } from "@/lib/states";

/** "List your venue": the first step for a venue owner (FR-04). */
export default function NewVenuePage() {
  return (
    <RequireUser>
      {(user) =>
        user.email_verified ? (
          // Listing a venue makes you its owner, and owners must use two-factor login.
          <TwoFactorGate user={user} always>
            <NewVenueForm />
          </TwoFactorGate>
        ) : (
          <VerifyEmailFirst />
        )
      }
    </RequireUser>
  );
}

function NewVenueForm() {
  const router = useRouter();
  const createVenue = useCreateVenue();
  const [formError, setFormError] = useState<string>();
  const form = useForm<CreateVenueInput>({
    resolver: zodResolver(createVenueSchema),
    defaultValues: { name: "", slug: "", state: "" },
  });
  const { errors, isSubmitting } = form.formState;
  const slug = useWatch({ control: form.control, name: "slug" });

  // The web address follows the name until the owner types their own.
  const [slugTouched, setSlugTouched] = useState(false);
  const nameField = form.register("name", {
    onChange: (e: React.ChangeEvent<HTMLInputElement>) => {
      if (!slugTouched) form.setValue("slug", slugify(e.target.value));
    },
  });
  const slugField = form.register("slug", {
    onChange: (e: React.ChangeEvent<HTMLInputElement>) => setSlugTouched(e.target.value !== ""),
  });

  const onSubmit = form.handleSubmit(async (values) => {
    setFormError(undefined);
    try {
      const venue = await createVenue.mutateAsync(values);
      router.push(`/dashboard/${venue.id}`);
    } catch (e) {
      setFormError(applyApiErrors(e, form.setError));
    }
  });

  return (
    <div className="mx-auto w-full max-w-md px-4 py-10">
      <Card>
        <CardHeader>
          <CardTitle className="text-xl">List your venue</CardTitle>
          <CardDescription>
            Start with the basics. CourtKu checks every new venue before players can see it, and you can
            set up the rest while you wait.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={onSubmit} className="grid gap-4" noValidate>
            <FormAlert message={formError} />
            <FormField label="Venue name" placeholder="Arena Sungai Buloh" error={errors.name?.message} {...nameField} />
            <FormField
              label="Web address"
              autoCapitalize="none"
              spellCheck={false}
              hint={`Players will find you at /v/${slug || "your-venue"}`}
              error={errors.slug?.message}
              {...slugField}
            />
            <SelectField
              label="State"
              placeholder="Choose a state"
              options={MALAYSIAN_STATES}
              error={errors.state?.message}
              {...form.register("state")}
            />
            <Button type="submit" size="lg" className="h-11" disabled={isSubmitting}>
              {isSubmitting ? "Saving..." : "List my venue"}
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  );
}
