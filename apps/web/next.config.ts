import type { NextConfig } from "next";

// Where the Laravel API runs. Only the Next.js server uses this address;
// the browser never sees it (see ADR-004: one origin for the browser).
const apiUrl = process.env.API_URL ?? "http://127.0.0.1:8000";

const nextConfig: NextConfig = {
  cacheComponents: true,
  partialPrefetching: true,
  turbopack: {
    rules: {
      "*.css": {
        loaders: ["@tailwindcss/turbopack"],
        as: "*.css",
      },
    },
  },

  // Safety headers on every page (docs/security.md). The forwarded API paths are left out:
  // Laravel adds its own there, and two copies of a header can cancel each other out.
  async headers() {
    return [
      {
        source: "/((?!api/|auth/|sanctum/).*)",
        headers: [
          // Browsers must not guess that a file is a different type than we say.
          { key: "X-Content-Type-Options", value: "nosniff" },
          // Other sites may not show CourtKu inside a frame (stops "clickjacking").
          { key: "X-Frame-Options", value: "DENY" },
          // Other sites we link to only learn "courtku.vercel.app", never the full page address.
          { key: "Referrer-Policy", value: "strict-origin-when-cross-origin" },
          // CourtKu never needs the camera, microphone or location.
          { key: "Permissions-Policy", value: "camera=(), microphone=(), geolocation=()" },
          // No framing, no plugins, and forms and <base> links stay on CourtKu. Scripts aren't
          // limited yet: Next.js needs per-request nonces for that (see docs/security.md).
          {
            key: "Content-Security-Policy",
            value: "frame-ancestors 'none'; object-src 'none'; base-uri 'self'; form-action 'self'",
          },
        ],
      },
    ];
  },

  // The browser calls these paths on the web app itself, and Next.js forwards them
  // to Laravel. Because the browser only ever talks to one site, Laravel's login
  // cookie is a normal first-party cookie and no CORS setup is needed.
  // Every path the browser sends to Laravel must be listed here.
  async rewrites() {
    return [
      { source: "/api/:path*", destination: `${apiUrl}/api/:path*` },
      { source: "/auth/:path*", destination: `${apiUrl}/auth/:path*` },
      { source: "/sanctum/csrf-cookie", destination: `${apiUrl}/sanctum/csrf-cookie` },
    ];
  },
};

export default nextConfig;
