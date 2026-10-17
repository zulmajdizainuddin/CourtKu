"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import Link from "next/link";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { AuthCard } from "@/components/auth/auth-card";
import { FormAlert, FormField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { useForgotPassword } from "@/features/auth/hooks";
import { applyApiErrors } from "@/lib/form-errors";
import { type ForgotPasswordInput, forgotPasswordSchema } from "@/lib/schemas/auth";

export default function ForgotPasswordPage() {
  const forgot = useForgotPassword();
  const [formError, setFormError] = useState<string>();
  const form = useForm<ForgotPasswordInput>({ resolver: zodResolver(forgotPasswordSchema) });
  const { errors, isSubmitting } = form.formState;

  const onSubmit = form.handleSubmit(async (values) => {
    setFormError(undefined);
    try {
      await forgot.mutateAsync(values);
    } catch (e) {
      setFormError(applyApiErrors(e, form.setError));
    }
  });

  return (
    <AuthCard
      title="Reset your password"
      description="Enter your email and we'll send you a link to choose a new password."
      footer={
        <Link href="/login" className="underline">
          Back to log in
        </Link>
      }
    >
      {forgot.isSuccess ? (
        <FormAlert tone="success" message="If that email has an account, a reset link is on its way." />
      ) : (
        <form onSubmit={onSubmit} className="grid gap-4" noValidate>
          <FormAlert message={formError} />
          <FormField label="Email" type="email" autoComplete="email" error={errors.email?.message} {...form.register("email")} />
          <Button type="submit" size="lg" className="h-11" disabled={isSubmitting}>
            {isSubmitting ? "Sending..." : "Send reset link"}
          </Button>
        </form>
      )}
    </AuthCard>
  );
}
