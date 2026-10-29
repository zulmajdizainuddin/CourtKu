"use client";

import { ChevronLeft, ChevronRight } from "lucide-react";
import Image from "next/image";
import { useRef, useState } from "react";
import { FormAlert } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import { MAX_PHOTOS, useDeletePhoto, usePhotos, useReorderPhotos, useUploadPhoto, type VenuePhoto } from "@/features/venues/photos";
import { ApiError } from "@/lib/api";

const ACCEPTED_TYPES = ["image/jpeg", "image/png", "image/webp"];

function errorMessage(e: unknown): string {
  if (e instanceof ApiError) return e.fields.photo?.[0] ?? e.message;
  if (e instanceof Error) return e.message;
  return "Could not upload the photo. Please try again.";
}

/** The venue's photos (FR-06). Owners add, reorder and remove them; the first is the cover photo. */
export function PhotosSection({ venueId, canEdit }: { venueId: string; canEdit: boolean }) {
  const photos = usePhotos(venueId);
  const upload = useUploadPhoto(venueId);
  const remove = useDeletePhoto(venueId);
  const reorder = useReorderPhotos(venueId);
  const fileInput = useRef<HTMLInputElement>(null);
  const [progress, setProgress] = useState<string>();
  const [problems, setProblems] = useState<string[]>([]);
  const list = photos.data ?? [];
  const busy = upload.isPending || remove.isPending || reorder.isPending;

  const onFilesChosen = async (files: FileList | null) => {
    if (!files || files.length === 0) return;
    const chosen = Array.from(files);
    const found: string[] = [];
    const room = MAX_PHOTOS - list.length;

    if (chosen.length > room) {
      found.push(`You can add ${room} more photo${room === 1 ? "" : "s"} (at most ${MAX_PHOTOS}). Only the first ${room} will be added.`);
    }

    // One at a time, so a slow phone connection isn't asked to send everything at once.
    const toUpload = chosen.slice(0, Math.max(0, room));
    for (const [index, file] of toUpload.entries()) {
      setProgress(`Uploading ${index + 1} of ${toUpload.length}...`);
      if (!ACCEPTED_TYPES.includes(file.type)) {
        found.push(`${file.name}: choose a photo (JPEG, PNG or WebP).`);
        continue;
      }
      try {
        await upload.mutateAsync(file);
      } catch (e) {
        found.push(`${file.name}: ${errorMessage(e)}`);
      }
    }

    setProgress(undefined);
    setProblems(found);
    if (fileInput.current) fileInput.current.value = ""; // the same file can be chosen again
  };

  const move = (index: number, by: -1 | 1) => {
    const ids = list.map((p) => p.id);
    [ids[index], ids[index + by]] = [ids[index + by], ids[index]];
    reorder.mutate(ids);
  };

  const onRemove = (photo: VenuePhoto, index: number) => {
    if (window.confirm(`Remove photo ${index + 1}? It will be deleted for good.`)) remove.mutate(photo.id);
  };

  return (
    <div className="grid gap-4">
      {photos.isPending && <p className="text-sm text-muted-foreground">Loading...</p>}
      {photos.isError && <p className="text-sm text-destructive">Could not load the photos. Please refresh the page.</p>}
      {photos.isSuccess && list.length === 0 && (
        <p className="text-sm text-muted-foreground">
          No photos yet.{canEdit && " Players like to see the courts before they book, so add a few."}
        </p>
      )}

      {list.length > 0 && (
        <ol className="grid grid-cols-2 gap-3 sm:grid-cols-3">
          {list.map((photo, index) => (
            <li key={photo.id} className="grid gap-1.5">
              <div className="relative overflow-hidden rounded-lg bg-muted">
                {/* Cloudinary already resizes and compresses, so Next.js doesn't need to again. */}
                <Image
                  src={photo.thumbnail_url}
                  alt={`Venue photo ${index + 1}${index === 0 ? " (cover photo)" : ""}`}
                  width={400}
                  height={300}
                  unoptimized
                  className="aspect-4/3 w-full object-cover"
                />
                {index === 0 && (
                  <span className="absolute top-2 left-2 rounded-full bg-background/90 px-2 py-0.5 text-xs font-medium">
                    Cover photo
                  </span>
                )}
              </div>
              {canEdit && (
                <div className="flex items-center justify-between">
                  <div className="flex">
                    <Button
                      variant="ghost"
                      size="icon"
                      aria-label={`Move photo ${index + 1} earlier`}
                      disabled={index === 0 || busy}
                      onClick={() => move(index, -1)}
                    >
                      <ChevronLeft />
                    </Button>
                    <Button
                      variant="ghost"
                      size="icon"
                      aria-label={`Move photo ${index + 1} later`}
                      disabled={index === list.length - 1 || busy}
                      onClick={() => move(index, 1)}
                    >
                      <ChevronRight />
                    </Button>
                  </div>
                  <Button
                    variant="ghost"
                    className="text-destructive"
                    aria-label={`Remove photo ${index + 1}`}
                    disabled={busy}
                    onClick={() => onRemove(photo, index)}
                  >
                    Remove
                  </Button>
                </div>
              )}
            </li>
          ))}
        </ol>
      )}

      {problems.length > 0 && (
        <ul role="alert" className="grid gap-1 rounded-lg bg-destructive/10 px-3 py-2 text-sm text-destructive">
          {problems.map((problem) => (
            <li key={problem}>{problem}</li>
          ))}
        </ul>
      )}
      <FormAlert message={[remove.error, reorder.error].find((e) => e instanceof ApiError)?.message} />
      {progress && (
        <p role="status" className="text-sm text-muted-foreground">
          {progress}
        </p>
      )}

      {canEdit && list.length < MAX_PHOTOS && (
        <div className="grid gap-1.5">
          <input
            ref={fileInput}
            type="file"
            accept={ACCEPTED_TYPES.join(",")}
            multiple
            className="sr-only"
            id="photo-upload"
            onChange={(e) => onFilesChosen(e.target.files)}
          />
          <Button
            variant="outline"
            className="h-11 justify-self-start px-6"
            disabled={busy}
            onClick={() => fileInput.current?.click()}
          >
            {upload.isPending ? "Uploading..." : "Add photos"}
          </Button>
          <p className="text-sm text-muted-foreground">
            JPEG, PNG or WebP, at least 400 × 300 pixels. Up to {MAX_PHOTOS} photos; the first is the cover photo.
          </p>
        </div>
      )}
    </div>
  );
}
