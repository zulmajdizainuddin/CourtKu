"use client";

import { useSearchParams } from "next/navigation";
import { Suspense } from "react";
import { AuthCard } from "@/components/auth/auth-card";
import { FormAlert } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { useMe, useResendVerification } from "@/features/auth/hooks";

/**
 * Shown right after registering: "check your inbox", with a button to send the email again.
 * Also where a broken or expired verification link lands.
 */
export default function VerifyEmailPage() {
  const { data: user } = useMe();
  const resend = useResendVerification();

  return (
    <AuthCard
      title="Check your email"
      description={
        user
          ? `We sent a verification link to ${user.email}. Open it on this device to confirm your email.`
          : "We sent you a verification link. Open it on this device to confirm your email."
      }
    >
      <div className="grid gap-4">
        <Suspense>
          <InvalidLinkNotice />
        </Suspense>
        {resend.isSuccess && <FormAlert tone="success" message="A new link is on its way." />}
        {resend.isError && <FormAlert message="Could not send the email. Please try again in a minute." />}
        <Button variant="outline" size="lg" className="h-11" disabled={resend.isPending} onClick={() => resend.mutate()}>
          {resend.isPending ? "Sending..." : "Send the link again"}
        </Button>
      </div>
    </AuthCard>
  );
}

/** The API sends people here with ?link=invalid when an email link expired or was changed. */
function InvalidLinkNotice() {
  const invalid = useSearchParams().get("link") === "invalid";
  return invalid ? (
    <FormAlert message="That link has expired or isn't valid. Send yourself a new one below, then use the newest email." />
  ) : null;
}
