"use client";

import Link from "next/link";
import { useSearchParams } from "next/navigation";
import { Suspense } from "react";
import { TwoFactorSettings } from "@/components/auth/two-factor-settings";
import { ButtonLink } from "@/components/ui/button-link";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { RequireUser } from "@/features/auth/require-user";
import { safeNextPath } from "@/lib/safe-next-path";

/** Account security: two-factor login. Owners and the admin are sent here until it's on. */
export default function SecurityPage() {
  return (
    <Suspense>
      <SecurityContent />
    </Suspense>
  );
}

function SecurityContent() {
  // Where the user was going before we asked them to turn two-factor on, e.g. /admin.
  const nextParam = useSearchParams().get("next");
  const next = nextParam ? safeNextPath(nextParam) : null;

  return (
    <RequireUser>
      {(user) => (
        <div className="mx-auto grid w-full max-w-xl gap-6 px-4 py-10">
          <Link href="/me" className="text-sm text-muted-foreground hover:underline">
            ← My account
          </Link>

          {next && user.two_factor_enabled && (
            <ButtonLink href={next} className="h-11 justify-self-start">
              Continue where you were
            </ButtonLink>
          )}

          <Card>
            <CardHeader>
              <CardTitle className="text-xl">Two-factor login</CardTitle>
              <CardDescription>
                {user.two_factor_required
                  ? "Required for venue owners and the CourtKu team."
                  : "Optional for players and staff, and a good idea."}
              </CardDescription>
            </CardHeader>
            <CardContent>
              <TwoFactorSettings user={user} />
            </CardContent>
          </Card>
        </div>
      )}
    </RequireUser>
  );
}
