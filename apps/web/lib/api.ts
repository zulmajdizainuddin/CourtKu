// One place where the browser talks to the Laravel API.
// Every request goes to a relative path ("/api/v1/me", "/auth/login"), which the
// Next.js rewrites forward to Laravel (see next.config.ts).

/** The error shape every API error uses: { error: { code, message, details } } */
export type ApiErrorBody = {
  error: {
    code: string;
    message: string;
    details?: { fields?: Record<string, string[]> } & Record<string, unknown>;
  };
};

/** Thrown for any non-2xx response, so callers can check `error.code`. */
export class ApiError extends Error {
  constructor(
    public status: number,
    public code: string,
    message: string,
    /** Field-level validation messages, e.g. { email: ["The email has already been taken."] } */
    public fields: Record<string, string[]> = {},
    /** Everything else the API sent in error.details, e.g. the bookings in the way (BOOKINGS_CONFLICT). */
    public details: Record<string, unknown> = {},
  ) {
    super(message);
    this.name = "ApiError";
  }
}

/** Turns any failed response body into an ApiError, even if it isn't our standard shape. */
export function toApiError(status: number, body: unknown): ApiError {
  const err = (body as Partial<ApiErrorBody> | null)?.error;
  if (err && typeof err.code === "string") {
    return new ApiError(status, err.code, err.message, err.details?.fields ?? {}, err.details ?? {});
  }
  return new ApiError(status, `HTTP_${status}`, "Something went wrong. Please try again.");
}

/** Reads a cookie by name in the browser. */
function readCookie(name: string): string | null {
  const match = document.cookie.split("; ").find((c) => c.startsWith(`${name}=`));
  return match ? decodeURIComponent(match.split("=").slice(1).join("=")) : null;
}

/**
 * Laravel protects every request that changes data with a CSRF token.
 * GET /sanctum/csrf-cookie gives the browser an XSRF-TOKEN cookie; we copy it into
 * the X-XSRF-TOKEN header so Laravel knows the request came from our own pages.
 */
async function ensureCsrfCookie(force = false): Promise<void> {
  if (!force && readCookie("XSRF-TOKEN")) return;
  await fetch("/sanctum/csrf-cookie", { credentials: "same-origin" });
}

/**
 * body: an object is sent as JSON; FormData (e.g. a photo file) is sent as a file upload.
 * headers: extra headers, e.g. the Idempotency-Key of a booking.
 */
type RequestOptions = { method?: string; body?: unknown; headers?: Record<string, string> };

export async function api<T = unknown>(path: string, options: RequestOptions = {}): Promise<T> {
  const method = (options.method ?? "GET").toUpperCase();
  const changesData = method !== "GET" && method !== "HEAD";

  // The browser writes a file upload's Content-Type itself (it includes a separator), so we don't.
  const isUpload = options.body instanceof FormData;

  const send = async () => {
    if (changesData) await ensureCsrfCookie();
    const xsrf = readCookie("XSRF-TOKEN");

    return fetch(path, {
      method,
      credentials: "same-origin",
      headers: {
        Accept: "application/json",
        ...(options.body !== undefined && !isUpload && { "Content-Type": "application/json" }),
        ...(xsrf && { "X-XSRF-TOKEN": xsrf }),
        ...options.headers,
      },
      body: options.body === undefined ? undefined : isUpload ? (options.body as FormData) : JSON.stringify(options.body),
    });
  };

  let response = await send();

  // 419 = the CSRF token expired (e.g. the tab was open for hours). Get a new one, retry once.
  if (response.status === 419 && changesData) {
    await ensureCsrfCookie(true);
    response = await send();
  }

  if (response.status === 204) return undefined as T;

  const body = await response.json().catch(() => null);
  if (!response.ok) throw toApiError(response.status, body);
  return body as T;
}
