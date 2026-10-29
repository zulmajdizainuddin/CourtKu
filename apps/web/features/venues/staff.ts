"use client";

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import type { ManageVenue, VenueRole } from "@/features/venues/hooks";
import { api } from "@/lib/api";

// Staff of one venue, and invites (FR-05). The owner's side, then the invited person's side.

export type StaffMember = { user_id: number; name: string; email: string; role: VenueRole; joined_at: string | null };
export type PendingInvite = { id: number; email: string; expires_at: string };

const staffKey = (venueId: string) => ["manage", "venues", venueId, "staff"] as const;

export function useStaff(venueId: string) {
  return useQuery({
    queryKey: staffKey(venueId),
    queryFn: async () =>
      (await api<{ data: { members: StaffMember[]; invites: PendingInvite[] } }>(`/api/v1/manage/venues/${venueId}/staff`))
        .data,
  });
}

function useStaffMutation<T>(venueId: string, mutationFn: (input: T) => Promise<unknown>) {
  const queryClient = useQueryClient();
  return useMutation({ mutationFn, onSuccess: () => queryClient.invalidateQueries({ queryKey: staffKey(venueId) }) });
}

/** Emails an invite link. Inviting the same email again sends a fresh link. */
export function useInviteStaff(venueId: string) {
  return useStaffMutation(venueId, (email: string) =>
    api(`/api/v1/manage/venues/${venueId}/staff-invites`, { method: "POST", body: { email } }),
  );
}

export function useCancelInvite(venueId: string) {
  return useStaffMutation(venueId, (inviteId: number) =>
    api(`/api/v1/manage/venues/${venueId}/staff-invites/${inviteId}`, { method: "DELETE" }),
  );
}

export function useRemoveStaff(venueId: string) {
  return useStaffMutation(venueId, (userId: number) =>
    api(`/api/v1/manage/venues/${venueId}/staff/${userId}`, { method: "DELETE" }),
  );
}

/** What the invited person sees before accepting. */
export type InvitePreview = {
  venue_name: string;
  role: VenueRole;
  /** e.g. "a***@gmail.com": which account the invite is for. */
  email_hint: string;
  for_this_account: boolean;
  expired: boolean;
  accepted: boolean;
};

export function useInvite(token: string) {
  return useQuery({
    queryKey: ["invites", token],
    queryFn: async () => (await api<{ data: InvitePreview }>(`/api/v1/invites/${token}`)).data,
    retry: false,
  });
}

export function useAcceptInvite(token: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async () =>
      (await api<{ data: ManageVenue }>(`/api/v1/invites/${token}/accept`, { method: "POST" })).data,
    // The new venue should show in the venue picker straight away.
    onSuccess: () => queryClient.invalidateQueries({ queryKey: ["manage", "venues"], exact: true }),
  });
}
