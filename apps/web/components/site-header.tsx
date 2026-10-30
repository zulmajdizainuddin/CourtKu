"use client";

import { CalendarDays, UserRound } from "lucide-react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { Button } from "@/components/ui/button";
import { ButtonLink } from "@/components/ui/button-link";
import { useLogout, useMe } from "@/features/auth/hooks";

/** Top bar: logo on the left; log in / register, or my bookings, the dashboard, account and log out, on the right. */
export function SiteHeader() {
  const { data: user, isPending } = useMe();
  const logout = useLogout();
  const router = useRouter();

  return (
    <header className="border-b">
      <div className="mx-auto flex h-14 max-w-5xl items-center justify-between px-4">
        <Link href="/" className="text-lg font-semibold tracking-tight">
          CourtKu
        </Link>

        <nav className="flex items-center gap-1 text-sm sm:gap-2">
          {isPending ? null : user ? (
            <>
              {/* On phones only an icon, like the account link, so the header fits a 360 px screen. */}
              <Link href="/me/bookings" aria-label="My bookings" className="flex items-center px-1.5 py-1 hover:underline sm:px-2">
                <CalendarDays className="size-5 sm:hidden" aria-hidden />
                <span className="hidden sm:inline">Bookings</span>
              </Link>
              <Link href="/dashboard" className="px-1.5 py-1 hover:underline sm:px-2">
                Dashboard
              </Link>
              {user.is_platform_admin && (
                <Link href="/admin" className="px-1.5 py-1 hover:underline sm:px-2">
                  Admin
                </Link>
              )}
              {/* On phones only an icon, so everything fits on a 360 px screen (NFR-08). */}
              <Link href="/me" aria-label="My account" className="flex items-center gap-1.5 px-1.5 py-1 hover:underline sm:px-2">
                <UserRound className="size-5 sm:hidden" aria-hidden />
                <span className="hidden max-w-32 truncate sm:inline">{user.name}</span>
              </Link>
              <Button
                variant="outline"
                disabled={logout.isPending}
                onClick={() => logout.mutate(undefined, { onSuccess: () => router.push("/") })}
              >
                Log out
              </Button>
            </>
          ) : (
            <>
              <Link href="/login" className="px-2 py-1 hover:underline">
                Log in
              </Link>
              <ButtonLink href="/register">
                Sign up
              </ButtonLink>
            </>
          )}
        </nav>
      </div>
    </header>
  );
}
