/**
 * Where to go after logging in, from "?next=/me".
 * Only paths on our own site are allowed. Otherwise a phishing link like
 * /login?next=https://evil.example could send someone to a fake site after a real login.
 */
export function safeNextPath(next: string | null): string {
  if (!next || !next.startsWith("/") || next.startsWith("//") || next.startsWith("/\\")) return "/";
  return next;
}
