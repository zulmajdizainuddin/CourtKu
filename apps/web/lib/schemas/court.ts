import { z } from "zod";

// The same rules as the API's CourtRequest; the API checks them again (and that the name is unique).

export const courtSchema = z.object({
  name: z.string().trim().min(1, "Give the court a name, e.g. Court 1.").max(50, "Use at most 50 characters."),
  sports: z.array(z.string()).min(1, "Choose at least one sport."),
  location: z.enum(["indoor", "outdoor"]),
  is_active: z.boolean(),
});

export type CourtInput = z.infer<typeof courtSchema>;

/** What the API receives. */
export function toCourtPayload(values: CourtInput) {
  return {
    name: values.name.trim(),
    sports: values.sports,
    is_indoor: values.location === "indoor",
    is_active: values.is_active,
  };
}

export type CourtPayload = ReturnType<typeof toCourtPayload>;
