"use client";

import { useState } from "react";
import { FormAlert } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { api } from "@/lib/api";

/**
 * For the admin: make one error on purpose in the API and one in the browser, to check that error
 * alerts reach Sentry on the live site (docs/deploy.md, "Error alerts"). Both should show up in
 * Sentry within a minute, and Sentry emails about each new kind of error.
 */
export function ErrorAlertsCheck() {
  const [message, setMessage] = useState<string>();

  const testApi = async () => {
    setMessage(undefined);
    try {
      await api("/api/v1/admin/test-error", { method: "POST" });
    } catch {
      // The API answers 500 on purpose.
    }
    setMessage("The API failed on purpose. Look for \"Test error from the CourtKu admin page\" in Sentry's Laravel project.");
  };

  const testBrowser = () => {
    // Thrown outside React, after this click has finished, so the page keeps working
    // and Sentry's browser part catches it as an unexpected error.
    setTimeout(() => {
      throw new Error("Test error from the CourtKu admin page (browser). If you can read this in Sentry, error alerts work.");
    });
    setMessage("The browser failed on purpose. Look for the test error in Sentry's Next.js project.");
  };

  return (
    <section aria-labelledby="error-alerts" className="mx-auto grid w-full max-w-3xl gap-3 px-4 pb-10">
      <h2 id="error-alerts" className="text-lg font-semibold tracking-tight">
        Error alerts
      </h2>
      <p className="text-sm text-muted-foreground">
        Check that Sentry hears about errors. Each button makes one error on purpose; nothing else is affected.
      </p>
      <div className="flex flex-wrap gap-2">
        <Button variant="outline" className="h-11" onClick={testApi}>
          Test the API
        </Button>
        <Button variant="outline" className="h-11" onClick={testBrowser}>
          Test the web app
        </Button>
      </div>
      <FormAlert tone="success" message={message} />
    </section>
  );
}
