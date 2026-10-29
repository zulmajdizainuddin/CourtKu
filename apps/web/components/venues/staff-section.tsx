"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { z } from "zod";
import { FormAlert, FormField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { type StaffMember, useCancelInvite, useInviteStaff, useRemoveStaff, useStaff } from "@/features/venues/staff";
import { ApiError } from "@/lib/api";
import { applyApiErrors } from "@/lib/form-errors";
import { formatDate } from "@/lib/format";

const inviteSchema = z.object({ email: z.email("Enter a valid email address.") });
type InviteInput = z.infer<typeof inviteSchema>;

/** The owner's staff page (FR-05): who works here, invites waiting, inviting and removing. */
export function StaffSection({ venueId }: { venueId: string }) {
  const staff = useStaff(venueId);
  const cancel = useCancelInvite(venueId);
  const remove = useRemoveStaff(venueId);

  const onRemove = (member: StaffMember) => {
    if (window.confirm(`Remove ${member.name}? They lose access to this venue straight away.`)) remove.mutate(member.user_id);
  };

  return (
    <div className="grid gap-6">
      {staff.isPending && <p className="text-sm text-muted-foreground">Loading...</p>}
      {staff.isError && <p className="text-sm text-destructive">Could not load the staff. Please refresh the page.</p>}

      {staff.data && (
        <>
          <section className="grid gap-2" aria-labelledby="people-heading">
            <h3 id="people-heading" className="text-sm font-medium">
              People
            </h3>
            <ul className="grid gap-2">
              {staff.data.members.map((member) => (
                <li
                  key={member.user_id}
                  className="flex flex-wrap items-center gap-2 rounded-lg px-3 py-2 text-sm ring-1 ring-foreground/10"
                >
                  <div className="min-w-40 flex-1">
                    <p className="font-medium">
                      {member.name}
                      <span className="ml-2 rounded-full bg-muted px-2 py-0.5 text-xs font-normal text-muted-foreground">
                        {member.role === "owner" ? "Owner" : "Staff"}
                      </span>
                    </p>
                    <p className="break-all text-muted-foreground">{member.email}</p>
                  </div>
                  {member.role === "staff" && (
                    <Button
                      variant="ghost"
                      className="text-destructive"
                      aria-label={`Remove ${member.name}`}
                      disabled={remove.isPending}
                      onClick={() => onRemove(member)}
                    >
                      Remove
                    </Button>
                  )}
                </li>
              ))}
            </ul>
          </section>

          {staff.data.invites.length > 0 && (
            <section className="grid gap-2" aria-labelledby="invites-heading">
              <h3 id="invites-heading" className="text-sm font-medium">
                Invites waiting
              </h3>
              <ul className="grid gap-2">
                {staff.data.invites.map((invite) => (
                  <li
                    key={invite.id}
                    className="flex flex-wrap items-center gap-2 rounded-lg px-3 py-2 text-sm ring-1 ring-foreground/10"
                  >
                    <div className="min-w-40 flex-1">
                      <p className="break-all">{invite.email}</p>
                      <p className="text-muted-foreground">Link works until {formatDate(invite.expires_at)}</p>
                    </div>
                    <Button
                      variant="ghost"
                      aria-label={`Cancel the invite to ${invite.email}`}
                      disabled={cancel.isPending}
                      onClick={() => cancel.mutate(invite.id)}
                    >
                      Cancel invite
                    </Button>
                  </li>
                ))}
              </ul>
            </section>
          )}
        </>
      )}

      <FormAlert message={[remove.error, cancel.error].find((e) => e instanceof ApiError)?.message} />
      <InviteForm venueId={venueId} />
    </div>
  );
}

function InviteForm({ venueId }: { venueId: string }) {
  const invite = useInviteStaff(venueId);
  const [formError, setFormError] = useState<string>();
  const [sentTo, setSentTo] = useState<string>();
  const form = useForm<InviteInput>({ resolver: zodResolver(inviteSchema), defaultValues: { email: "" } });
  const { errors, isSubmitting } = form.formState;

  const onSubmit = form.handleSubmit(async ({ email }) => {
    setFormError(undefined);
    setSentTo(undefined);
    try {
      await invite.mutateAsync(email);
      form.reset();
      setSentTo(email);
    } catch (e) {
      setFormError(applyApiErrors(e, form.setError));
    }
  });

  return (
    <form onSubmit={onSubmit} className="grid gap-3 border-t pt-4" noValidate>
      <FormField
        label="Invite staff by email"
        type="email"
        autoComplete="off"
        placeholder="aina@example.com"
        hint="They get an email with a link. It works for 7 days, and only for this email address."
        error={errors.email?.message}
        {...form.register("email")}
      />
      <FormAlert message={formError} />
      {sentTo && <FormAlert tone="success" message={`Invite sent to ${sentTo}.`} />}
      <Button type="submit" variant="outline" className="h-11 justify-self-start px-6" disabled={isSubmitting}>
        {isSubmitting ? "Sending..." : "Send invite"}
      </Button>
    </form>
  );
}
