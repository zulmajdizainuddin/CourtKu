"use client";

import { useMutation } from "@tanstack/react-query";
import { useRefreshMe } from "@/features/auth/hooks";
import { api } from "@/lib/api";

// Two-factor login: after the password, the user also types a 6-digit code from an
// authenticator app on their phone (Google Authenticator, Microsoft Authenticator...).
// Laravel Fortify provides these endpoints under /auth.

/** The second step of logging in: the code from the app, or one of the recovery codes. */
export function useTwoFactorChallenge() {
  const refreshMe = useRefreshMe();
  return useMutation({
    mutationFn: (input: { code: string } | { recovery_code: string }) =>
      api("/auth/two-factor-challenge", { method: "POST", body: input }),
    onSuccess: refreshMe,
  });
}

/**
 * "Enter your password again." Turning two-factor on or off needs a password typed in
 * the last 3 hours; otherwise the API answers 423 PASSWORD_CONFIRMATION_REQUIRED.
 */
export function useConfirmPassword() {
  return useMutation({
    mutationFn: (password: string) => api("/auth/user/confirm-password", { method: "POST", body: { password } }),
  });
}

export type TwoFactorSetup = {
  /** The QR code to scan, as an SVG image. */
  svg: string;
  /** The same secret as text, for typing in when the camera can't scan. */
  secretKey: string;
};

/**
 * Step 1 of turning two-factor on: makes a new secret and returns its QR code.
 * Not "on" yet; the user must prove it works with a code (useConfirmTwoFactor).
 * Only offer this while two-factor is off: a new secret would no longer match the phone app.
 */
export function useStartTwoFactor() {
  return useMutation({
    mutationFn: async (): Promise<TwoFactorSetup> => {
      // force: a fresh secret every time, in case an earlier setup was abandoned halfway.
      await api("/auth/user/two-factor-authentication", { method: "POST", body: { force: true } });
      const [qr, key] = await Promise.all([
        api<{ svg: string }>("/auth/user/two-factor-qr-code"),
        api<{ secretKey: string }>("/auth/user/two-factor-secret-key"),
      ]);
      return { svg: qr.svg, secretKey: key.secretKey };
    },
  });
}

/** Step 2: the first code from the app. If it's right, two-factor is on. Returns the recovery codes. */
export function useConfirmTwoFactor() {
  const refreshMe = useRefreshMe();
  return useMutation({
    mutationFn: async (code: string) => {
      await api("/auth/user/confirmed-two-factor-authentication", { method: "POST", body: { code } });
      return api<string[]>("/auth/user/two-factor-recovery-codes");
    },
    onSuccess: refreshMe,
  });
}

/** Replaces the recovery codes, e.g. after some were used. The old ones stop working. */
export function useNewRecoveryCodes() {
  return useMutation({
    mutationFn: async () => {
      await api("/auth/user/two-factor-recovery-codes", { method: "POST" });
      return api<string[]>("/auth/user/two-factor-recovery-codes");
    },
  });
}

export function useDisableTwoFactor() {
  const refreshMe = useRefreshMe();
  return useMutation({
    mutationFn: () => api("/auth/user/two-factor-authentication", { method: "DELETE" }),
    onSuccess: refreshMe,
  });
}
