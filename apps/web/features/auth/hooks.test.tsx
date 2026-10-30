import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { renderHook, waitFor } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { ApiError, api } from "@/lib/api";
import { type User, useLogin, useLogout, useMe } from "./hooks";

vi.mock("@/lib/api", async (importOriginal) => ({
  ...(await importOriginal<typeof import("@/lib/api")>()),
  api: vi.fn(),
}));

const player: User = {
  id: 2,
  name: "Demo Player",
  email: "player@example.com",
  phone: null,
  email_verified: true,
  is_platform_admin: false,
  two_factor_enabled: false,
  two_factor_required: false,
};

/**
 * A pretend API: "nobody is logged in" until /auth/login is called.
 * "Who is logged in?" takes 20 ms, like a real network, so a page that doesn't
 * wait for the answer moves on too early, which was the bug.
 */
function fakeApi({ twoFactor }: { twoFactor: boolean }) {
  let loggedIn = false;
  vi.mocked(api).mockImplementation(async (path: string) => {
    if (path === "/auth/login") {
      loggedIn = !twoFactor;
      return { two_factor: twoFactor };
    }
    await new Promise((resolve) => setTimeout(resolve, 20));
    if (loggedIn) return { data: player };
    throw new ApiError(401, "UNAUTHENTICATED", "Please log in first.");
  });
}

/** Like the real login page: the header watches who is logged in while the form logs in. */
async function setup() {
  const queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  const { result } = renderHook(() => ({ login: useLogin(), me: useMe() }), {
    wrapper: ({ children }) => <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>,
  });
  await waitFor(() => expect(result.current.me.isSuccess).toBe(true));
  expect(result.current.me.data).toBeNull();
  return { queryClient, login: result.current.login };
}

describe("useLogin", () => {
  beforeEach(() => {
    vi.mocked(api).mockReset();
  });

  it("knows who is logged in before the login page moves on", async () => {
    fakeApi({ twoFactor: false });
    const { queryClient, login } = await setup();

    await login.mutateAsync({ email: player.email, password: "secret" });

    // Without this, protected pages still saw "nobody" and sent the player back to /login.
    expect(queryClient.getQueryData(["me"])).toEqual(player);
  });

  it("doesn't count as logged in while the two-factor code is still missing", async () => {
    fakeApi({ twoFactor: true });
    const { queryClient, login } = await setup();

    await login.mutateAsync({ email: player.email, password: "secret" });

    expect(queryClient.getQueryData(["me"])).toBeNull();
  });
});

describe("a new person on the same browser", () => {
  beforeEach(() => {
    vi.mocked(api).mockReset();
  });

  it("forgets the previous person's bookings and venues after logout", async () => {
    vi.mocked(api).mockResolvedValue(undefined);
    const queryClient = new QueryClient();
    queryClient.setQueryData(["bookings", "CK-EF9BX"], { code: "CK-EF9BX" });
    queryClient.setQueryData(["manage", "venues", "9"], { name: "Arena Demo" });
    const { result } = renderHook(() => useLogout(), {
      wrapper: ({ children }) => <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>,
    });

    await result.current.mutateAsync();

    expect(queryClient.getQueryData(["bookings", "CK-EF9BX"])).toBeUndefined();
    expect(queryClient.getQueryData(["manage", "venues", "9"])).toBeUndefined();
    expect(queryClient.getQueryData(["me"])).toBeNull();
  });

  it("starts a login with nothing left from whoever was here before", async () => {
    fakeApi({ twoFactor: false });
    const { queryClient, login } = await setup();
    // Left over from someone else, e.g. after their session ran out without a logout.
    queryClient.setQueryData(["bookings", "CK-EF9BX"], { code: "CK-EF9BX" });

    await login.mutateAsync({ email: player.email, password: "secret" });

    expect(queryClient.getQueryData(["bookings", "CK-EF9BX"])).toBeUndefined();
    expect(queryClient.getQueryData(["me"])).toEqual(player);
  });
});
