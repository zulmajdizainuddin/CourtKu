import { z } from "zod";
import { normalizePhone } from "@/lib/phone";
import { SLUG_PATTERN } from "@/lib/slug";
import { isMalaysianState } from "@/lib/states";

// The same rules as the API's StoreVenueRequest and UpdateVenueRequest; the API checks them again.

const name = z.string().trim().min(3, "Use at least 3 characters.").max(100, "Use at most 100 characters.");
const slug = z
  .string()
  .min(3, "Use at least 3 characters.")
  .max(60, "Use at most 60 characters.")
  .regex(SLUG_PATTERN, "Use lowercase letters, numbers and hyphens only, e.g. arena-sg-buloh.");
const state = z.string().refine(isMalaysianState, "Choose a state.");

export const createVenueSchema = z.object({ name, slug, state });

/** The profile form. Optional fields are "" when empty; toProfilePayload sends them as null. */
export const venueProfileSchema = z.object({
  name,
  slug,
  state,
  description: z.string().max(2000, "Use at most 2,000 characters."),
  address_line: z.string().max(255, "Use at most 255 characters."),
  city: z.string().max(100, "Use at most 100 characters."),
  postcode: z.string().regex(/^(\d{5})?$/, "Use the 5-digit postcode, e.g. 47000."),
  phone: z
    .string()
    .refine(
      (v) => v.trim() === "" || normalizePhone(v) !== null,
      "Enter a Malaysian phone number, e.g. 012-345 6789 or 03-1234 5678.",
    ),
  facilities: z.array(z.string()),
});

export type CreateVenueInput = z.infer<typeof createVenueSchema>;
export type VenueProfileInput = z.infer<typeof venueProfileSchema>;

/** What the API receives: empty optional fields as null, so they are cleared. */
export function toProfilePayload(values: VenueProfileInput) {
  const orNull = (v: string) => (v.trim() === "" ? null : v.trim());
  return {
    ...values,
    name: values.name.trim(),
    description: orNull(values.description),
    address_line: orNull(values.address_line),
    city: orNull(values.city),
    postcode: orNull(values.postcode),
    phone: orNull(values.phone),
  };
}

export type VenueProfilePayload = ReturnType<typeof toProfilePayload>;
