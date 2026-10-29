import type { VariantProps } from "class-variance-authority";
import Link from "next/link";
import type { ComponentProps } from "react";
import { buttonVariants } from "@/components/ui/button";

/**
 * A link that looks like a button, e.g. "Sign up" or "List a venue".
 * It stays a real link, so screen readers announce "link" and people know it opens
 * another page (NFR-09). Use Button only for actions on the same page.
 */
export function ButtonLink({
  className,
  variant,
  size,
  ...props
}: ComponentProps<typeof Link> & VariantProps<typeof buttonVariants>) {
  return <Link className={buttonVariants({ variant, size, className })} {...props} />;
}
