"use client";

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { api, ApiError } from "@/lib/api";
import type { LoginInput, RegisterInput } from "@/lib/schemas/auth";

export type User = {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  email_verified: boolean;
  is_platform_admin: boolean;
  /** On only after the user confirmed it with a code from their authenticator app. */
  two_factor_enabled: boolean;
  /** Owners and the platform admin must turn two-factor on; the API refuses their dashboard until they do. */
  two_factor_required: boolean;
};

const meKey = ["me"] as const;

/**
 * Who is logged in? Returns the user, or null when logged out.
 * TanStack Query remembers the answer, so every component can call this
 * without sending a new request each time.
 */
export function useMe() {
  return useQuery({
    queryKey: meKey,
    queryFn: async (): Promise<User | null> => {
      try {
        const res = await api<{ data: User }>("/api/v1/me");
        return res.data;
      } catch (e) {
        if (e instanceof ApiError && e.status === 401) return null; // logged out is not an error
        throw e;
      }
    },
    staleTime: 60_000,
  });
}

/** After login, logout or register, ask the API again who is logged in. */
export function useRefreshMe() {
  const queryClient = useQueryClient();
  return () => queryClient.invalidateQueries({ queryKey: meKey });
}

/**
 * A new person is logged in: forget everything the previous one loaded (their bookings, venues,
 * schedules...), then ask who is logged in now. Without this, on a shared phone or the counter
 * computer, the next person briefly saw the previous person's data from the browser's memory.
 */
export function useStartSession() {
  const queryClient = useQueryClient();
  return async () => {
    queryClient.removeQueries({ predicate: (query) => query.queryKey[0] !== meKey[0] });
    await queryClient.invalidateQueries({ queryKey: meKey });
  };
}

export function useLogin() {
  const startSession = useStartSession();
  return useMutation({
    // two_factor: true means the password was right, but the user must still enter
    // the code from their authenticator app before they are logged in.
    mutationFn: (input: LoginInput) =>
      api<{ two_factor: boolean }>("/auth/login", { method: "POST", body: input }),
    // Waits until we know who is logged in before the login page moves on. Otherwise the
    // next page still sees "nobody is logged in" and sends the user back to /login.
    onSuccess: async (result) => {
      if (!result.two_factor) await startSession();
    },
  });
}

export function useRegister() {
  const startSession = useStartSession();
  return useMutation({
    mutationFn: (input: RegisterInput) => api("/auth/register", { method: "POST", body: input }),
    onSuccess: startSession,
  });
}

export function useLogout() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: () => api("/auth/logout", { method: "POST" }),
    // Forget everything this person loaded, so the next person on this browser can't see it.
    onSuccess: () => {
      queryClient.clear();
      queryClient.setQueryData(meKey, null);
    },
  });
}

export function useResendVerification() {
  return useMutation({
    mutationFn: () => api("/auth/email/verification-notification", { method: "POST" }),
  });
}

export function useForgotPassword() {
  return useMutation({
    mutationFn: (input: { email: string }) =>
      api("/auth/forgot-password", { method: "POST", body: input }),
  });
}

export function useResetPassword() {
  return useMutation({
    mutationFn: (input: {
      token: string;
      email: string;
      password: string;
      password_confirmation: string;
    }) => api("/auth/reset-password", { method: "POST", body: input }),
  });
}
