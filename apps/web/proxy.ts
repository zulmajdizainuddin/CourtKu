import { NextResponse, type NextRequest } from "next/server";
import { forwardedRequestHeaders } from "@/lib/web-proxy";

// Runs on the server before every request that next.config.ts forwards to Laravel (ADR-004).
// It adds the web app's secret and the visitor's IP, so the API knows it may believe them (ADR-025).
// Page requests don't pass through here.
export function proxy(request: NextRequest) {
  return NextResponse.next({
    request: { headers: forwardedRequestHeaders(request.headers, process.env.WEB_PROXY_SECRET) },
  });
}

// The same paths as the rewrites in next.config.ts.
export const config = {
  matcher: ["/api/:path*", "/auth/:path*", "/sanctum/csrf-cookie"],
};
