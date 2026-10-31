// GET /version: which commit this deployment of the web app was built from (ADR-026).
// Vercel sets VERCEL_GIT_COMMIT_SHA on every deployment; the after-deploy check waits until
// it matches the commit that was just merged.
export function GET() {
  return Response.json({ version: process.env.VERCEL_GIT_COMMIT_SHA ?? "local" });
}
