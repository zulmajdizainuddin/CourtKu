import { z } from "zod";
import { parseRinggit } from "@/lib/money";

// The same rules as the API's CourtRequest; the API checks them again (and that the name is unique).

export const courtSchema = z.object({
  name: z.string().trim().min(1, "Give the court a name, e.g. Court 1.").max(50, "Use at most 50 characters."),
  // Price per hour as typed, e.g. "25.50". Empty = no price yet (the court stays hidden from players).
  price: z.string().superRefine((value, ctx) => {
    if (value.trim() === "") return;
    const sen = parseRinggit(value);
    if (sen === null) ctx.addIssue({ code: "custom", message: "Enter a price like 25.00." });
    else if (sen % 10 !== 0) ctx.addIssue({ code: "custom", message: "Use a price in 10-sen steps, e.g. 25.00 or 25.50." });
    else if (sen > 100000) ctx.addIssue({ code: "custom", message: "The price can be at most RM 1,000.00 an hour." });
  }),
  sports: z.array(z.string()).min(1, "Choose at least one sport."),
  location: z.enum(["indoor", "outdoor"]),
  is_active: z.boolean(),
});

export type CourtInput = z.infer<typeof courtSchema>;

/** What the API receives: the price in whole sen, or null when it was left empty. */
export function toCourtPayload(values: CourtInput) {
  return {
    name: values.name.trim(),
    base_price_sen: values.price.trim() === "" ? null : parseRinggit(values.price),
    sports: values.sports,
    is_indoor: values.location === "indoor",
    is_active: values.is_active,
  };
}

export type CourtPayload = ReturnType<typeof toCourtPayload>;
