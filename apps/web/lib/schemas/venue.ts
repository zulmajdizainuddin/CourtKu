import { z } from "zod";
import { SLUG_PATTERN } from "@/lib/slug";
import { isMalaysianState } from "@/lib/states";

// The same rules as the API's StoreVenueRequest; the API checks them again.

export const createVenueSchema = z.object({
  name: z.string().trim().min(3, "Use at least 3 characters.").max(100, "Use at most 100 characters."),
  slug: z
    .string()
    .min(3, "Use at least 3 characters.")
    .max(60, "Use at most 60 characters.")
    .regex(SLUG_PATTERN, "Use lowercase letters, numbers and hyphens only, e.g. arena-sg-buloh."),
  state: z.string().refine(isMalaysianState, "Choose a state."),
});

export type CreateVenueInput = z.infer<typeof createVenueSchema>;
