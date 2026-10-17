"use client";

import { zodResolver } from "@hookform/resolvers/zod";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { useForm } from "react-hook-form";
import { AuthCard } from "@/components/auth/auth-card";
import { FormAlert, FormField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { useLogin } from "@/features/auth/hooks";
import { applyApiErrors } from "@/lib/form-errors";
import { safeNextPath } from "@/lib/safe-next-path";
import { type LoginInput, loginSchema } from "@/lib/schemas/auth";

export default function LoginPage() {
  const router = useRouter();
  const login = useLogin();
  const [formError, setFormError] = useState<string>();
  const form = useForm<LoginInput>({ resolver: zodResolver(loginSchema) });
  const { errors, isSubmitting } = form.formState;

  const onSubmit = form.handleSubmit(async (values) => {
    setFormError(undefined);
    try {
      await login.mutateAsync(values);
      router.push(safeNextPath(new URLSearchParams(window.location.search).get("next")));
    } catch (e) {
      setFormError(applyApiErrors(e, form.setError));
    }
  });

  return (
    <AuthCard
      title="Log in"
      description="Welcome back. Log in to book and manage your courts."
      footer={
        <span>
          New to CourtKu?{" "}
          <Link href="/register" className="text-foreground underline">
            Create an account
          </Link>
        </span>
      }
    >
      <form onSubmit={onSubmit} className="grid gap-4" noValidate>
        <FormAlert message={formError} />
        <FormField label="Email" type="email" autoComplete="email" error={errors.email?.message} {...form.register("email")} />
        <FormField
          label="Password"
          type="password"
          autoComplete="current-password"
          error={errors.password?.message}
          {...form.register("password")}
        />
        <Link href="/forgot-password" className="-mt-2 justify-self-end text-sm text-muted-foreground underline">
          Forgot password?
        </Link>
        <Button type="submit" size="lg" className="h-11" disabled={isSubmitting}>
          {isSubmitting ? "Logging in..." : "Log in"}
        </Button>
      </form>
    </AuthCard>
  );
}
