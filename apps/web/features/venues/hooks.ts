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
  /** Booking settings (FR-13): slot length 30 or 60 minutes, shortest/longest booking, days ahead. */
  slot_minutes: 30 | 60;
  min_minutes: number;
  max_minutes: number;
  booking_window_days: number;
  /** How close to the start players may still book; 0 = right up to the start time. */
  min_notice_minutes: number;
  /** Players cancel online until this many hours before the start (FR-24). */
  cancel_cutoff_hours: number;
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

export type BookingSettings = Pick<
  ManageVenue,
  "slot_minutes" | "min_minutes" | "max_minutes" | "booking_window_days" | "min_notice_minutes" | "cancel_cutoff_hours"
>;

/** Saves the booking settings (FR-13). Owners only. */
export function useUpdateSettings(venueId: string) {
  const queryClient = useQueryClient();
  const refreshPublicPage = useRefreshPublicPage(venueId);
  return useMutation({
    mutationFn: async (input: BookingSettings) =>
      (await api<{ data: ManageVenue }>(`/api/v1/manage/venues/${venueId}/settings`, { method: "PUT", body: input })).data,
    onSuccess: (venue) => {
      refreshPublicPage();
      queryClient.setQueryData([...myVenuesKey, venueId], venue);
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
