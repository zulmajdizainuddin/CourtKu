"use client";

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useRefreshPublicPage } from "@/features/venues/refresh-public-page";
import { api } from "@/lib/api";
import type { PriceRule } from "@/lib/price-rules";
import type { PriceRulePayload } from "@/lib/schemas/price-rule";

// The peak and off-peak prices of one venue (FR-10), for the dashboard.

const priceRulesKey = (venueId: string) => ["manage", "venues", venueId, "price-rules"] as const;

/** All-courts prices first, then by start time. */
export function usePriceRules(venueId: string) {
  return useQuery({
    queryKey: priceRulesKey(venueId),
    queryFn: async () => (await api<{ data: PriceRule[] }>(`/api/v1/manage/venues/${venueId}/price-rules`)).data,
  });
}

/** Every change reloads the list, and the public venue page, so players never see an old price. */
function usePriceRuleMutation<T>(venueId: string, mutationFn: (input: T) => Promise<unknown>) {
  const queryClient = useQueryClient();
  const refreshPublicPage = useRefreshPublicPage(venueId);
  return useMutation({
    mutationFn,
    onSuccess: () => {
      refreshPublicPage();
      return queryClient.invalidateQueries({ queryKey: priceRulesKey(venueId) });
    },
  });
}

export function useAddPriceRule(venueId: string) {
  return usePriceRuleMutation(venueId, (input: PriceRulePayload) =>
    api(`/api/v1/manage/venues/${venueId}/price-rules`, { method: "POST", body: input }),
  );
}

export function useUpdatePriceRule(venueId: string) {
  return usePriceRuleMutation(venueId, ({ ruleId, input }: { ruleId: number; input: PriceRulePayload }) =>
    api(`/api/v1/manage/venues/${venueId}/price-rules/${ruleId}`, { method: "PUT", body: input }),
  );
}

export function useDeletePriceRule(venueId: string) {
  return usePriceRuleMutation(venueId, (ruleId: number) =>
    api(`/api/v1/manage/venues/${venueId}/price-rules/${ruleId}`, { method: "DELETE" }),
  );
}
