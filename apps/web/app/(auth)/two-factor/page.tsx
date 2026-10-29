"use client";

import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { Suspense, useState } from "react";
import { AuthCard } from "@/components/auth/auth-card";
import { FormField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { useTwoFactorChallenge } from "@/features/auth/two-factor";
import { ApiError } from "@/lib/api";
import { safeNextPath } from "@/lib/safe-next-path";

/** The second step of logging in, for accounts with two-factor login turned on. */
export default function TwoFactorPage() {
  return (
    <Suspense>
      <TwoFactorForm />
    </Suspense>
  );
}

function TwoFactorForm() {
  const router = useRouter();
  const next = safeNextPath(useSearchParams().get("next"));
  const challenge = useTwoFactorChallenge();
  const [useRecovery, setUseRecovery] = useState(false);
  const [value, setValue] = useState("");
  const [error, setError] = useState<string>();

  const onSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(undefined);
    const entered = value.trim();
    if (!entered) {
      setError(useRecovery ? "Enter one of your recovery codes." : "Enter the 6-digit code from your app.");
      return;
    }
    try {
      await challenge.mutateAsync(useRecovery ? { recovery_code: entered } : { code: entered.replace(/\s/g, "") });
      router.push(next);
    } catch (err) {
      if (err instanceof ApiError && err.code === "VALIDATION_FAILED") {
        setError(
          useRecovery
            ? "That recovery code didn't work. Each code works only once."
            : "That code didn't work. Check your app and try the newest code.",
        );
      } else {
        setError(err instanceof ApiError ? err.message : "Could not reach CourtKu. Check your connection and try again.");
      }
    }
  };

  const switchMode = () => {
    setUseRecovery(!useRecovery);
    setValue("");
    setError(undefined);
  };

  return (
    <AuthCard
      title="Enter your code"
      description={
        useRecovery
          ? "Type one of the recovery codes you saved when you turned on two-factor login."
          : "Open your authenticator app and type the 6-digit code it shows for CourtKu."
      }
      footer={
        <span>
          Wrong account?{" "}
          <Link href="/login" className="text-foreground underline">
            Log in again
          </Link>
        </span>
      }
    >
      <form onSubmit={onSubmit} className="grid gap-4" noValidate>
        {useRecovery ? (
          <FormField
            key="recovery"
            label="Recovery code"
            name="recovery_code"
            autoComplete="off"
            autoCapitalize="none"
            spellCheck={false}
            value={value}
            onChange={(e) => setValue(e.target.value)}
            error={error}
          />
        ) : (
          <FormField
            key="code"
            label="6-digit code"
            name="code"
            inputMode="numeric"
            autoComplete="one-time-code"
            maxLength={7}
            autoFocus
            value={value}
            onChange={(e) => setValue(e.target.value)}
            error={error}
          />
        )}
        <Button type="submit" size="lg" className="h-11" disabled={challenge.isPending}>
          {challenge.isPending ? "Checking..." : "Log in"}
        </Button>
        <Button type="button" variant="link" onClick={switchMode}>
          {useRecovery ? "Use the code from my app instead" : "Lost your phone? Use a recovery code"}
        </Button>
      </form>
    </AuthCard>
  );
}
