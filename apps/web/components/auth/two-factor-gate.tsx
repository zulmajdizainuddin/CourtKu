"use client";

import { usePathname } from "next/navigation";
import { ButtonLink } from "@/components/ui/button-link";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import type { User } from "@/features/auth/hooks";

/**
 * Owners and the admin must turn two-factor login on before using their dashboard.
 * Until they do, this shows a card that sends them to set it up and back here after.
 * The API enforces the same rule (TWO_FACTOR_REQUIRED); this only explains it nicely.
 *
 * always: needed even if the user isn't an owner yet, e.g. on "List your venue".
 */
export function TwoFactorGate({
  user,
  always = false,
  children,
}: {
  user: User;
  always?: boolean;
  children: React.ReactNode;
}) {
  const pathname = usePathname();
  const needed = always || user.two_factor_required;

  if (!needed || user.two_factor_enabled) return children;

  return (
    <div className="mx-auto w-full max-w-md px-4 py-10">
      <Card>
        <CardHeader>
          <CardTitle className="text-xl">Turn on two-factor login first</CardTitle>
          <CardDescription>
            {always
              ? "Venue owners must protect their account with a code from an authenticator app, so a stolen password alone can't take over a venue."
              : "You run a venue or CourtKu itself, so your account must be protected with a code from an authenticator app."}
          </CardDescription>
        </CardHeader>
        <CardContent>
          <ButtonLink href={`/me/security?next=${encodeURIComponent(pathname)}`}>
            Set it up (about 2 minutes)
          </ButtonLink>
        </CardContent>
      </Card>
    </div>
  );
}
