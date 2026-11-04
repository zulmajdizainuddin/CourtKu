import { defineConfig, devices } from "@playwright/test";

// Browser tests (ADR-034): the 4 key flows, on a laptop screen and on a 360 px phone, against
// a real API (port 8100, its own courtku_e2e database, pretend Stripe) and a production build of
// the web app (port 3100). Run with `npm run e2e`; needs MySQL and Mailpit (docker compose up -d).
const webUrl = "http://localhost:3100";
const apiUrl = "http://127.0.0.1:8100";
// A test value; the API reads the same one from apps/api/.env.e2e.example.
const webProxySecret = "e2e-web-proxy-secret";

export default defineConfig({
  testDir: "./e2e",
  // One test at a time: they share one database, and each screen size has its own accounts.
  workers: 1,
  fullyParallel: false,
  retries: process.env.CI ? 1 : 0,
  timeout: 60_000,
  expect: { timeout: 15_000 },
  reporter: process.env.CI ? [["github"], ["html", { open: "never" }]] : [["list"]],
  use: {
    baseURL: webUrl,
    locale: "en-MY",
    timezoneId: "Asia/Kuala_Lumpur",
    trace: "retain-on-failure",
    screenshot: "only-on-failure",
  },
  projects: [
    { name: "desktop", use: { ...devices["Desktop Chrome"] } },
    // The plan's smallest screen (NFR-08).
    { name: "phone", use: { ...devices["Pixel 7"], viewport: { width: 360, height: 780 } } },
  ],
  webServer: [
    {
      command: "node e2e/start-api.mjs",
      url: `${apiUrl}/up`,
      timeout: 180_000,
      reuseExistingServer: false,
      stdout: "pipe",
    },
    {
      // The API address is fixed into the build (next.config.ts rewrites), so the tests need their own.
      command: "npm run build && npx next start --port 3100",
      url: webUrl,
      timeout: 600_000,
      reuseExistingServer: false,
      env: { API_URL: apiUrl, WEB_PROXY_SECRET: webProxySecret, NEXT_PUBLIC_SENTRY_DSN: "" },
    },
  ],
});
