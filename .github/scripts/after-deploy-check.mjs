// Checks the live site after a deploy (ADR-026). There is no staging server, so this is the
// first real look at new code once Render and Vercel run it.
//
// GitHub Actions runs it after every merge to main (.github/workflows/after-deploy.yml).
// By hand, from the repo root (Node 24, nothing to install):
//   node .github/scripts/after-deploy-check.mjs

const WEB_URL = process.env.WEB_URL || "https://courtku.vercel.app";
const API_URL = process.env.API_URL || "https://courtku-api.onrender.com";
const COMMIT = process.env.COMMIT || ""; // the commit that should be running
const WAIT_API = process.env.WAIT_API === "yes"; // the API changed: wait until Render runs COMMIT
const WAIT_WEB = process.env.WAIT_WEB === "yes"; // the web app changed: wait until Vercel runs COMMIT
const WAIT_MINUTES = Number(process.env.WAIT_MINUTES || 25);

const sleep = (seconds) => new Promise((resolve) => setTimeout(resolve, seconds * 1000));
const short = (commit) => (commit || "nothing").slice(0, 7);

/** One request, giving up after 30 seconds. Never throws: a failed request has status 0. */
async function get(url, method = "GET") {
  try {
    const response = await fetch(url, { method, redirect: "manual", signal: AbortSignal.timeout(30_000) });
    return { status: response.status, headers: response.headers, text: method === "HEAD" ? "" : await response.text() };
  } catch {
    return { status: 0, headers: new Headers(), text: "" };
  }
}

async function json(url) {
  try {
    return JSON.parse((await get(url)).text);
  } catch {
    return {};
  }
}

// --- 1. Wait until the new code is running -------------------------------------------------

async function waitFor(name, url) {
  const deadline = Date.now() + WAIT_MINUTES * 60_000;
  for (;;) {
    const running = (await json(url)).version;
    if (running === COMMIT) {
      console.log(`${name} is running ${short(COMMIT)}`);
      return true;
    }
    if (Date.now() >= deadline) {
      console.log(`::error::${name} still runs ${short(running)} after ${WAIT_MINUTES} minutes, not ${short(COMMIT)}. Look at the deploy in the ${name} dashboard.`);
      return false;
    }
    console.log(`${name} runs ${short(running)}, waiting for ${short(COMMIT)}...`);
    await sleep(30);
  }
}

const deployed =
  (!WAIT_API || (await waitFor("Render (API)", `${API_URL}/api/health`))) &&
  (!WAIT_WEB || (await waitFor("Vercel (web)", `${WEB_URL}/version`)));

// --- 2. Check the live site ----------------------------------------------------------------

const checks = [
  ["API is healthy (database and cache answer)", async () => (await json(`${API_URL}/api/health`)).status === "ok"],
  ["API called directly counts as a direct caller", async () => (await json(`${API_URL}/api/health`)).via === "direct"],
  [
    "Web app reaches the API with the right secret (via = browser)",
    async () => {
      const health = await json(`${WEB_URL}/api/health`);
      return health.status === "ok" && health.via === "browser";
    },
  ],
  ["Home page loads", async () => (await get(`${WEB_URL}/`)).status === 200],
  ["Login page loads", async () => (await get(`${WEB_URL}/login`)).status === 200],
  ["Login cookie can be fetched (/sanctum/csrf-cookie)", async () => (await get(`${WEB_URL}/sanctum/csrf-cookie`)).status === 204],
  [
    "Unknown venue shows the not-found page",
    async () => (await get(`${WEB_URL}/v/no-such-venue-${Date.now()}`)).text.includes("Venue not found · CourtKu"),
  ],
  ["Web pages refuse to be framed", async () => (await get(`${WEB_URL}/`, "HEAD")).headers.get("x-frame-options") === "DENY"],
  [
    "API answers carry the strict Content-Security-Policy",
    async () =>
      (await get(`${API_URL}/api/health`, "HEAD")).headers.get("content-security-policy") ===
      "default-src 'none'; frame-ancestors 'none'",
  ],
];

let failures = deployed ? 0 : 1;
for (const [what, passes] of deployed ? checks : []) {
  // Up to 3 tries, so one slow answer doesn't raise a false alarm.
  let ok = false;
  for (let attempt = 1; attempt <= 3 && !ok; attempt++) {
    ok = await passes();
    if (!ok && attempt < 3) await sleep(10);
  }
  console.log(ok ? `ok      ${what}` : `::error::FAILED  ${what}`);
  if (!ok) failures++;
}

// exitCode instead of process.exit(): ending while a connection is still open crashes Node on Windows.
if (!deployed) {
  process.exitCode = 1;
} else if (failures > 0) {
  console.log(`${failures} check(s) failed. See docs/deploy.md, "When something is down".`);
  process.exitCode = 1;
} else {
  console.log("All checks passed.");
}
