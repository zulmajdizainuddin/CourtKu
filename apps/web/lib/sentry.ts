import type * as Sentry from "@sentry/nextjs";

type SentryOptions = NonNullable<Parameters<typeof Sentry.init>[0]>;

/**
 * Error alerts (docs/deploy.md), for the browser and the web app's server alike. Errors only, and
 * nothing about the visitor: Sentry's defaults would also send their user details, cookies, headers,
 * what they typed and the values inside the code, which CourtKu never needs (docs/security.md).
 * No performance tracing or session replay either, which keeps CourtKu inside Sentry's free plan.
 * Without NEXT_PUBLIC_SENTRY_DSN (on a laptop) nothing is sent.
 */
export function sentryOptions(environment: string | undefined): SentryOptions {
  return {
    dsn: process.env.NEXT_PUBLIC_SENTRY_DSN,
    environment: environment ?? "development",
    dataCollection: {
      userInfo: false,
      cookies: false,
      httpHeaders: false,
      httpBodies: [],
      urlQueryParams: false,
      databaseQueryData: false,
      queues: false,
      stackFrameVariables: false,
    },
  };
}
