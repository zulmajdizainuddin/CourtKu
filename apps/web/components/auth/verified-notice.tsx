"use client";

import { useSearchParams } from "next/navigation";
import { FormAlert } from "@/components/forms/form-field";

/** Laravel sends players to "/?verified=1" after they click the link in their email. */
export function VerifiedNotice() {
  const verified = useSearchParams().get("verified") === "1";
  return verified ? <FormAlert tone="success" message="Your email is verified. You're all set to book." /> : null;
}
