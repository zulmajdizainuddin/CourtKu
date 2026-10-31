"use client";

import * as Sentry from "@sentry/nextjs";
import { useEffect } from "react";
import { AuthCard } from "@/components/auth/auth-card";
import { Button } from "@/components/ui/button";

/** The API didn't answer (e.g. it was asleep or the database was off): offer to try again. */
export default function VenueError({ error, reset }: { error: Error; reset: () => void }) {
  // Usually the API waking up, but worth knowing about if it keeps happening (docs/deploy.md).
  useEffect(() => {
    Sentry.captureException(error);
  }, [error]);

  return (
    <AuthCard
      title="Couldn't load this venue"
      description="CourtKu didn't answer in time. This can happen when the server has been quiet for a while."
    >
      <Button size="lg" className="h-11 w-full" onClick={reset}>
        Try again
      </Button>
    </AuthCard>
  );
}
