"use client";

import Link from "next/link";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { RequireUser } from "@/features/auth/require-user";

/** The player's account page: who is logged in and their security settings. Bookings are added in M3. */
export default function AccountPage() {
  return (
    <RequireUser>
      {(user) => (
        <div className="mx-auto w-full max-w-xl px-4 py-10">
          <Card>
            <CardHeader>
              <CardTitle className="text-xl">My account</CardTitle>
            </CardHeader>
            <CardContent className="grid gap-2 text-sm">
              <p>
                <span className="text-muted-foreground">Name:</span> {user.name}
              </p>
              <p>
                <span className="text-muted-foreground">Email:</span> {user.email}{" "}
                {user.email_verified ? (
                  <span className="text-emerald-700 dark:text-emerald-400">(verified)</span>
                ) : (
                  <Link href="/verify-email" className="text-destructive underline">
                    (not verified yet)
                  </Link>
                )}
              </p>
              <p>
                <span className="text-muted-foreground">Two-factor login:</span>{" "}
                {user.two_factor_enabled ? "On" : "Off"}{" "}
                <Link href="/me/security" className="underline">
                  (manage)
                </Link>
              </p>
            </CardContent>
          </Card>
        </div>
      )}
    </RequireUser>
  );
}
