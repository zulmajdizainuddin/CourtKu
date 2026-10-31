import * as Sentry from "@sentry/nextjs";
import { sentryOptions } from "@/lib/sentry";

// Error alerts for the browser side (docs/deploy.md): a page that breaks in a visitor's browser
// is sent to Sentry, which emails us.
Sentry.init(sentryOptions(process.env.NEXT_PUBLIC_VERCEL_ENV));

// Lets Sentry tell which page a visitor was moving to when something broke.
export const onRouterTransitionStart = Sentry.captureRouterTransitionStart;
