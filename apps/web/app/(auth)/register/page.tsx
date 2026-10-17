"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { AuthCard } from "@/components/auth/auth-card";
import { FormAlert, FormField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { useRegister } from "@/features/auth/hooks";
import { applyApiErrors } from "@/lib/form-errors";
import { type RegisterInput, registerSchema } from "@/lib/schemas/auth";

export default function RegisterPage() {
  const router = useRouter();
  const register = useRegister();
  const [formError, setFormError] = useState<string>();
  const form = useForm<RegisterInput>({ resolver: zodResolver(registerSchema) });
  const { errors, isSubmitting } = form.formState;

  const onSubmit = form.handleSubmit(async (values) => {
    setFormError(undefined);
    try {
      await register.mutateAsync(values);
      // Laravel has logged them in and emailed a verification link.
      router.push("/verify-email");
    } catch (e) {
      setFormError(applyApiErrors(e, form.setError));
    }
  });

  return (
    <AuthCard
      title="Create your account"
      description="You need an account to book and pay for courts."
      footer={
        <span>
          Already have an account?{" "}
          <Link href="/login" className="text-foreground underline">
            Log in
          </Link>
        </span>
      }
    >
      <form onSubmit={onSubmit} className="grid gap-4" noValidate>
        <FormAlert message={formError} />
        <FormField label="Full name" autoComplete="name" error={errors.name?.message} {...form.register("name")} />
        <FormField label="Email" type="email" autoComplete="email" error={errors.email?.message} {...form.register("email")} />
        <FormField
          label="Password"
          type="password"
          autoComplete="new-password"
          error={errors.password?.message}
          {...form.register("password")}
        />
        <FormField
          label="Confirm password"
          type="password"
          autoComplete="new-password"
          error={errors.password_confirmation?.message}
          {...form.register("password_confirmation")}
        />
        <Button type="submit" size="lg" className="h-11" disabled={isSubmitting}>
          {isSubmitting ? "Creating account..." : "Create account"}
        </Button>
      </form>
    </AuthCard>
  );
}
