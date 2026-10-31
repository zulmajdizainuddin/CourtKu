"use client";

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import type { ManageVenue } from "@/features/venues/hooks";
import { useRefreshPublicPage } from "@/features/venues/refresh-public-page";
import { api } from "@/lib/api";

// The owner's Payments tab (FR-17, ADR-024): the venue's own Stripe account, connected through
// Stripe Connect, and how players pay.

/** The API's PaymentAccountResource. No secrets: CourtKu never holds the venue's keys. */
export type PaymentAccount = {
  gateway: "stripe";
  account_id: string; // "acct_1Abc..."
  account_name: string | null;
  /** true = players can pay online; false = the owner still has to finish Stripe's sign-up pages. */
  ready: boolean;
  is_live: boolean;
  connected_at: string;
};

export type PaymentSettings = Pick<ManageVenue, "payment_mode" | "hold_minutes">;

const accountKey = (venueId: string) => ["manage", "venues", venueId, "payment-account"] as const;
const venueKey = (venueId: string) => ["manage", "venues", venueId] as const;

/** How many times to ask again (every 4 seconds) after the owner comes back from Stripe: about a minute. */
const STRIPE_CHECKS = 15;

/**
 * The connected account. Asking again also asks Stripe how it stands, e.g. after the owner comes back from Stripe.
 * Stripe takes a few seconds to switch payments on after the owner finishes, so just back from Stripe it asks
 * again every 4 seconds until the account is ready, for about a minute at most (waitingForStripe meanwhile).
 */
export function usePaymentAccount(venueId: string, { justBackFromStripe = false } = {}) {
  const queryClient = useQueryClient();
  const stillWaiting = (account: PaymentAccount | null | undefined, checks: number) =>
    justBackFromStripe && account != null && !account.ready && checks < STRIPE_CHECKS;

  const query = useQuery({
    queryKey: accountKey(venueId),
    refetchInterval: (q) => (stillWaiting(q.state.data, q.state.dataUpdateCount) ? 4000 : false),
    queryFn: async () => {
      const account = (await api<{ data: PaymentAccount | null }>(`/api/v1/manage/venues/${venueId}/payment-account`))
        .data;
      // The venue's "payment connected" flag follows the account (it unlocks paying online).
      queryClient.invalidateQueries({
        queryKey: venueKey(venueId),
        exact: true,
      });
      return account;
    },
  });
  const checks = queryClient.getQueryState(accountKey(venueId))?.dataUpdateCount ?? 0;

  return { ...query, waitingForStripe: stillWaiting(query.data, checks) };
}

/** "Connect with Stripe" / "Continue on Stripe": gets a one-time link to Stripe's own pages and goes there. */
export function useGoToStripe(venueId: string) {
  return useMutation({
    mutationFn: async () =>
      (
        await api<{ data: { url: string } }>(`/api/v1/manage/venues/${venueId}/payment-account/onboarding`, {
          method: "POST",
        })
      ).data.url,
    onSuccess: (url) => window.location.assign(url),
  });
}

/** CourtKu stops using the account; players go back to paying at the counter. */
export function useDisconnectPaymentAccount(venueId: string) {
  const queryClient = useQueryClient();
  const refreshPublicPage = useRefreshPublicPage(venueId);
  return useMutation({
    mutationFn: () =>
      api(`/api/v1/manage/venues/${venueId}/payment-account`, {
        method: "DELETE",
      }),
    onSuccess: () => {
      refreshPublicPage(); // players can't pay online there any more
      queryClient.setQueryData(accountKey(venueId), null);
      queryClient.invalidateQueries({
        queryKey: venueKey(venueId),
        exact: true,
      });
    },
  });
}

export function useUpdatePaymentSettings(venueId: string) {
  const queryClient = useQueryClient();
  const refreshPublicPage = useRefreshPublicPage(venueId);
  return useMutation({
    mutationFn: async (input: PaymentSettings) =>
      (
        await api<{ data: ManageVenue }>(`/api/v1/manage/venues/${venueId}/payment-settings`, {
          method: "PUT",
          body: input,
        })
      ).data,
    onSuccess: (venue) => {
      refreshPublicPage(venue.slug); // the booking page offers the new way of paying straight away
      queryClient.setQueryData(venueKey(venueId), venue);
    },
  });
}
