"use client";

import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { api } from "@/lib/api";
import { resizeForUpload } from "@/lib/resize-image";

// The photos of one venue (FR-06), stored on Cloudinary by the API (ADR-018).

export type VenuePhoto = {
  id: number;
  /** 400 × 300, cropped to fill: for lists and galleries. */
  thumbnail_url: string;
  /** At most 1600 pixels wide: to view the photo large. */
  url: string;
  width: number;
  height: number;
  sort_order: number;
};

/** Must match MAX_PHOTOS in the API's VenuePhotoController. */
export const MAX_PHOTOS = 10;

const photosKey = (venueId: string) => ["manage", "venues", venueId, "photos"] as const;

/** The photos in the owner's chosen order; the first is the cover photo. */
export function usePhotos(venueId: string) {
  return useQuery({
    queryKey: photosKey(venueId),
    queryFn: async () => (await api<{ data: VenuePhoto[] }>(`/api/v1/manage/venues/${venueId}/photos`)).data,
  });
}

/** Shrinks the photo in the browser, then uploads it as a file. */
export function useUploadPhoto(venueId: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: async (file: File) => {
      const form = new FormData();
      form.append("photo", await resizeForUpload(file), "photo.jpg");
      return api(`/api/v1/manage/venues/${venueId}/photos`, { method: "POST", body: form });
    },
    onSuccess: () => queryClient.invalidateQueries({ queryKey: photosKey(venueId) }),
  });
}

export function useDeletePhoto(venueId: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (photoId: number) => api(`/api/v1/manage/venues/${venueId}/photos/${photoId}`, { method: "DELETE" }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: photosKey(venueId) }),
  });
}

/** Saves a new order: every photo id of the venue, first (the cover) to last. */
export function useReorderPhotos(venueId: string) {
  const queryClient = useQueryClient();
  return useMutation({
    mutationFn: (photoIds: number[]) =>
      api(`/api/v1/manage/venues/${venueId}/photos/order`, { method: "PUT", body: { photo_ids: photoIds } }),
    onSuccess: () => queryClient.invalidateQueries({ queryKey: photosKey(venueId) }),
  });
}
