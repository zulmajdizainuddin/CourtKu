"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import { useSearchParams } from "next/navigation";
import { Suspense, useState } from "react";
import { useForm } from "react-hook-form";
import { AuthCard } from "@/components/auth/auth-card";
import { FormAlert, FormField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { ButtonLink } from "@/components/ui/button-link";
import { useResetPassword } from "@/features/auth/hooks";
import { ApiError } from "@/lib/api";
import { applyApiErrors } from "@/lib/form-errors";
import { type ResetPasswordInput, resetPasswordSchema } from "@/lib/schemas/auth";

/** Opened from the link in the "reset your password" email: /reset-password?token=...&email=... */
export default function ResetPasswordPage() {
  return (
    <Suspense>
      <ResetPasswordForm />
    </Suspense>
  );
}

function ResetPasswordForm() {
  const params = useSearchParams();
  const token = params.get("token") ?? "";
  const email = params.get("email") ?? "";

  const reset = useResetPassword();
  const [formError, setFormError] = useState<string>();
  const form = useForm<ResetPasswordInput>({ resolver: zodResolver(resetPasswordSchema) });
  const { errors, isSubmitting } = form.formState;

  const onSubmit = form.handleSubmit(async (values) => {
    setFormError(undefined);
    try {
      await reset.mutateAsync({ ...values, token, email });
    } catch (e) {
      if (!(e instanceof ApiError)) return setFormError(applyApiErrors(e, form.setError));
      // Password problems go under the password box. A bad or expired link comes back
      // as an "email" or "token" error, and those aren't inputs here, so show it at the top.
      if (e.fields.password) form.setError("password", { message: e.fields.password[0] });
      const linkProblem = e.fields.email?.[0] ?? e.fields.token?.[0];
      setFormError(linkProblem ?? (e.fields.password ? undefined : e.message));
    }
  });

  if (!token || !email) {
    return (
      <AuthCard title="Link not valid" description="This reset link is incomplete. Please request a new one.">
        <ButtonLink href="/forgot-password" size="lg" className="h-11 w-full">
          Request a new link
        </ButtonLink>
      </AuthCard>
    );
  }

  if (reset.isSuccess) {
    return (
      <AuthCard title="Password changed" description="You can now log in with your new password.">
        <ButtonLink href="/login" size="lg" className="h-11 w-full">
          Log in
        </ButtonLink>
      </AuthCard>
    );
  }

  return (
    <AuthCard title="Choose a new password" description={`For ${email}`}>
      <form onSubmit={onSubmit} className="grid gap-4" noValidate>
        <FormAlert message={formError} />
        <FormField
          label="New password"
          type="password"
          autoComplete="new-password"
          error={errors.password?.message}
          {...form.register("password")}
        />
        <FormField
          label="Confirm new password"
          type="password"
          autoComplete="new-password"
          error={errors.password_confirmation?.message}
          {...form.register("password_confirmation")}
        />
        <Button type="submit" size="lg" className="h-11" disabled={isSubmitting}>
          {isSubmitting ? "Saving..." : "Save new password"}
        </Button>
      </form>
    </AuthCard>
  );
}
