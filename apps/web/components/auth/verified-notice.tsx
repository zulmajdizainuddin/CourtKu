"use client";

import { useSearchParams } from "next/navigation";
import { FormAlert } from "@/components/forms/form-field";

/**
 * Notes on the home page after an account change: Laravel sends players to "/?verified=1" after they
 * click the link in their email, and deleting an account lands on "/?account=deleted" (ADR-033).
 */
export function VerifiedNotice() {
  const params = useSearchParams();
  if (params.get("verified") === "1") {
    return <FormAlert tone="success" message="Your email is verified. You're all set to book." />;
  }
  if (params.get("account") === "deleted") {
    return <FormAlert tone="success" message="Your account is deleted. Thank you for using CourtKu." />;
  }
  return null;
}
