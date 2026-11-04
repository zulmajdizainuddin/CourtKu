"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { FormAlert, FormField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { useDeleteAccount } from "@/features/auth/hooks";
import { ApiError } from "@/lib/api";

/**
 * "Delete my account" (NFR-07, ADR-033): opened with a button, then the password and a final click.
 * The API refuses while the user has upcoming bookings or owns a venue, and says why.
 */
export function DeleteAccount() {
  const router = useRouter();
  const deletion = useDeleteAccount();
  const [open, setOpen] = useState(false);
  const [password, setPassword] = useState("");
  const [fieldError, setFieldError] = useState<string>();
  const [formError, setFormError] = useState<string>();

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setFieldError(undefined);
    setFormError(undefined);
    try {
      await deletion.mutateAsync(password);
      router.push("/?account=deleted");
    } catch (err) {
      if (err instanceof ApiError && err.fields.password) {
        setFieldError(err.fields.password[0]);
      } else {
        setFormError(err instanceof ApiError ? err.message : "Could not reach CourtKu. Check your connection and try again.");
      }
    }
  };

  if (!open) {
    return (
      <div className="grid gap-3 text-sm">
        <p>
          Your name and email are removed and you can&apos;t log in again. Past bookings stay in the venues&apos; records
          as &quot;Deleted user&quot;. See the <Link href="/privacy#keep" className="underline">Privacy Notice</Link>.
        </p>
        <Button variant="outline" className="h-11 justify-self-start text-destructive" onClick={() => setOpen(true)}>
          Delete my account
        </Button>
      </div>
    );
  }

  return (
    <form className="grid gap-4" onSubmit={submit} noValidate>
      <p className="text-sm">
        This can&apos;t be undone. Enter your password to delete your account.
      </p>
      <FormAlert message={formError} />
      <FormField
        label="Password"
        name="password"
        type="password"
        autoComplete="current-password"
        autoFocus
        value={password}
        onChange={(e) => setPassword(e.target.value)}
        error={fieldError}
      />
      <div className="flex flex-wrap gap-2">
        <Button type="submit" variant="destructive" className="h-11" disabled={deletion.isPending || password === ""}>
          {deletion.isPending ? "Deleting..." : "Delete my account for good"}
        </Button>
        <Button
          type="button"
          variant="outline"
          className="h-11"
          onClick={() => {
            setOpen(false);
            setPassword("");
            setFieldError(undefined);
            setFormError(undefined);
          }}
        >
          Cancel
        </Button>
      </div>
    </form>
  );
}
