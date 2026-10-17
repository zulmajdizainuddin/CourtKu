"use client";

import { usePathname, useRouter } from "next/navigation";
import { useEffect } from "react";
import { type User, useMe } from "@/features/auth/hooks";

/**
 * Wrap a page that needs a logged-in user. While we ask the API "who is logged in?"
 * nothing is shown; if nobody is, we go to /login?next=<this page>.
 *
 * Why not check the cookie in proxy.ts? Laravel gives every visitor a session
 * cookie, logged in or not, so the cookie alone can't tell us.
 * This only decides what the page shows; the API still checks every request.
 */
export function RequireUser({ children }: { children: (user: User) => React.ReactNode }) {
  const { data: user, isPending } = useMe();
  const router = useRouter();
  const pathname = usePathname();

  useEffect(() => {
    if (!isPending && !user) {
      router.replace(`/login?next=${encodeURIComponent(pathname)}`);
    }
  }, [isPending, user, router, pathname]);

  if (!user) return null;
  return children(user);
}
