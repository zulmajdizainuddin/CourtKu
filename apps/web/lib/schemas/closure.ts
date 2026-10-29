import { z } from "zod";

// The same rules as the API's StoreClosureRequest; the API checks them again.

const day = /^\d{4}-\d{2}-\d{2}$/;

export const closureSchema = z
  .object({
    starts_on: z.string().regex(day, "Choose the first day."),
    // Empty means a single day.
    ends_on: z.string().regex(new RegExp(`^(${day.source.slice(1, -1)})?$`), "Choose the last day."),
    reason: z.string().trim().min(1, "Tell players why, e.g. Hari Raya or renovation.").max(100, "Use at most 100 characters."),
  })
  .refine((v) => v.ends_on === "" || v.ends_on >= v.starts_on, {
    path: ["ends_on"],
    message: "The last day can't be before the first day.",
  });

export type ClosureInput = z.infer<typeof closureSchema>;

/** What the API receives: a single day when the last day was left empty. */
export function toClosurePayload(values: ClosureInput) {
  return { starts_on: values.starts_on, ends_on: values.ends_on || values.starts_on, reason: values.reason.trim() };
}
