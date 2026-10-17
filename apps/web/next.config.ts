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
