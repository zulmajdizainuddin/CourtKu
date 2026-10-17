"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { Button } from "@/components/ui/button";
import { useLogout, useMe } from "@/features/auth/hooks";

/** Top bar: logo on the left; log in / register, or the user's name and log out, on the right. */
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

        <nav className="flex items-center gap-2 text-sm">
          {isPending ? null : user ? (
            <>
              <Link href="/me" className="px-2 py-1 hover:underline">
                {user.name}
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
              <Button render={<Link href="/register" />} nativeButton={false}>
                Sign up
              </Button>
            </>
          )}
        </nav>
      </div>
    </header>
  );
}
