"use client";

import * as Sentry from "@sentry/nextjs";
import { useEffect } from "react";

/**
 * The last safety net: shown when even the page layout fails. It replaces the whole page, so it
 * brings its own <html> and plain styles. The error goes to Sentry (docs/deploy.md).
 */
export default function GlobalError({ error, reset }: { error: Error & { digest?: string }; reset: () => void }) {
  useEffect(() => {
    Sentry.captureException(error);
  }, [error]);

  return (
    <html lang="en">
      <body style={{ fontFamily: "system-ui, sans-serif", padding: "3rem 1rem", textAlign: "center" }}>
        <h1 style={{ fontSize: "1.5rem" }}>Something went wrong</h1>
        <p>CourtKu has been told about it. Please try again.</p>
        <button type="button" onClick={reset} style={{ marginTop: "1rem", padding: "0.75rem 1.5rem", fontSize: "1rem" }}>
          Try again
        </button>
      </body>
    </html>
  );
}
