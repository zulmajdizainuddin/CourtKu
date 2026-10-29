"use client";

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useRefreshPublicPage } from "@/features/venues/refresh-public-page";
import { api } from "@/lib/api";
import type { CreateVenueInput, VenueProfilePayload } from "@/lib/schemas/venue";

export type VenueStatus = "pending" | "active" | "suspended";
export type VenueRole = "owner" | "staff";

/** A venue as its owner or staff see it in the dashboard (ManageVenueResource in the API). */
export type ManageVenue = {
  id: number;
  name: string;
  slug: string;
  status: VenueStatus;
  state: string;
  role: VenueRole;
  description: string | null;
  address_line: string | null;
  city: string | null;
  postcode: string | null;
  /** Always in +60 format, e.g. "+60123456789". */
  phone: string | null;
  facilities: string[];
  approved_at: string | null;
  created_at: string;
};

const myVenuesKey = ["manage", "venues"] as const;

/** The venues the logged-in user works at, for the dashboard's venue picker. */
export function useMyVenues() {
  return useQuery({
    queryKey: myVenuesKey,
    queryFn: async () => (await api<{ data: ManageVenue[] }>("/api/v1/manage/venues")).data,
  });
}

/** One venue the user works at. The API answers 404 for anyone else's venue. */
export function useManageVenue(venueId: string) {
  return useQuery({
    queryKey: [...myVenuesKey, venueId],
    queryFn: async () => (await api<{ data: ManageVenue }>(`/api/v1/manage/venues/${venueId}`)).data,
  });
}

/** Saves the venue profile (FR-06). Owners only; the API refuses staff. */
export function useUpdateVenue(venueId: string) {
  const queryClient = useQueryClient();
  const refreshPublicPage = useRefreshPublicPage(venueId);
  return useMutation({
    mutationFn: async (input: VenueProfilePayload) =>
      (await api<{ data: ManageVenue }>(`/api/v1/manage/venues/${venueId}`, { method: "PUT", body: input })).data,
    onSuccess: (venue) => {
      // The public page at the old address (before a pending venue's address changed) and the new one.
      refreshPublicPage();
      refreshPublicPage(venue.slug);
      // Show the saved version straight away, and refresh the venue picker (the name may have changed).
      queryClient.setQueryData([...myVenuesKey, venueId], venue);
      queryClient.invalidateQueries({ queryKey: myVenuesKey, exact: true });
    },
  });
}

/** Lists a new venue (FR-04). It starts as pending until an admin approves it. */
export function useCreateVenue() {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (input: CreateVenueInput) =>
      (await api<{ data: ManageVenue }>("/api/v1/manage/venues", { method: "POST", body: input })).data,
    onSuccess: () => queryClient.invalidateQueries({ queryKey: myVenuesKey }),
  });
}
