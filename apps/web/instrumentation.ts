import * as Sentry from "@sentry/nextjs";
import { sentryOptions } from "@/lib/sentry";

// Error alerts for the web app's server side (docs/deploy.md): a page that fails while the server
// builds it is sent to Sentry. Next.js runs register() once when the server starts.
export async function register() {
  if (process.env.NEXT_RUNTIME === "nodejs") {
    Sentry.init(sentryOptions(process.env.VERCEL_ENV));
  }
}

// Next.js calls this for every error while building a page on the server.
export const onRequestError = Sentry.captureRequestError;
