"use client";

import { useParams, useRouter } from "next/navigation";
import { Suspense } from "react";
import { AuthCard } from "@/components/auth/auth-card";
import { VerifyEmailFirst } from "@/components/auth/verify-email-first";
import { FormAlert } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { ButtonLink } from "@/components/ui/button-link";
import { type User, useLogout } from "@/features/auth/hooks";
import { RequireUser } from "@/features/auth/require-user";
import { useAcceptInvite, useInvite } from "@/features/venues/staff";
import { ApiError } from "@/lib/api";

/**
 * Where the link in a staff invite email leads (FR-05). Logged-out visitors are sent to
 * log in first and come back here; the invite only works for the invited email.
 */
export default function InvitePage() {
  return (
    <Suspense>
      <InviteRoute />
    </Suspense>
  );
}

function InviteRoute() {
  const { token } = useParams<{ token: string }>();
  return (
    <RequireUser>{(user) => (user.email_verified ? <Invite token={token} user={user} /> : <VerifyEmailFirst />)}</RequireUser>
  );
}

function Invite({ token, user }: { token: string; user: User }) {
  const router = useRouter();
  const invite = useInvite(token);
  const accept = useAcceptInvite(token);
  const logout = useLogout();

  if (invite.isPending) return <AuthCard title="Staff invite">Loading...</AuthCard>;

  if (invite.isError) {
    return (
      <AuthCard
        title="Invite not found"
        description="This invite doesn't exist or was cancelled. Ask the venue owner to send a new one."
      >
        <ButtonLink href="/dashboard" className="h-11 w-full">
          Go to your dashboard
        </ButtonLink>
      </AuthCard>
    );
  }

  const { venue_name, email_hint, for_this_account, expired, accepted } = invite.data;

  if (accepted) {
    return (
      <AuthCard title="Invite already used" description={`This invite to ${venue_name} has already been accepted.`}>
        <ButtonLink href="/dashboard" className="h-11 w-full">
          Go to your dashboard
        </ButtonLink>
      </AuthCard>
    );
  }

  if (expired) {
    return (
      <AuthCard
        title="Invite expired"
        description={`This invite to ${venue_name} has expired. Ask the venue owner to send you a new one.`}
      >
        <ButtonLink href="/dashboard" className="h-11 w-full">
          Go to your dashboard
        </ButtonLink>
      </AuthCard>
    );
  }

  if (!for_this_account) {
    const switchAccount = () =>
      logout.mutate(undefined, { onSuccess: () => router.push(`/login?next=${encodeURIComponent(`/invites/${token}`)}`) });
    return (
      <AuthCard
        title="Wrong account"
        description={`This invite to ${venue_name} is for ${email_hint}. You're logged in as ${user.email}.`}
      >
        <div className="grid gap-3 text-sm">
          <p>Log out, then log in or create an account with the invited email address.</p>
          <Button size="lg" className="h-11" disabled={logout.isPending} onClick={switchAccount}>
            Log out and switch account
          </Button>
        </div>
      </AuthCard>
    );
  }

  const onAccept = async () => {
    const venue = await accept.mutateAsync();
    router.push(`/dashboard/${venue.id}`);
  };

  return (
    <AuthCard title={`Join ${venue_name}`} description="You've been invited to help run this venue on CourtKu as staff.">
      <div className="grid gap-3">
        <FormAlert message={accept.error instanceof ApiError ? accept.error.message : undefined} />
        <Button size="lg" className="h-11" disabled={accept.isPending} onClick={onAccept}>
          {accept.isPending ? "Joining..." : "Accept the invite"}
        </Button>
      </div>
    </AuthCard>
  );
}
