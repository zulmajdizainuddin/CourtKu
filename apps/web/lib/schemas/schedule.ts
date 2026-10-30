import { z } from "zod";
import { normalizePhone } from "@/lib/phone";

// The staff forms on the schedule. The same rules as the API, which checks them again,
// together with the time rules (opening hours, length, clashes).

/** A walk-in or phone booking (FR-20): name and phone both required (the owner's choice). */
export const counterBookingSchema = z.object({
  source: z.enum(["walk_in", "phone"]),
  court_id: z.string().min(1, "Choose a court."),
  sport: z.string().min(1, "Choose a sport."),
  starts_at: z.string().min(1, "Choose a start time."),
  minutes: z.string(),
  customer_name: z.string().trim().min(1, "Enter the customer's name.").max(100, "Use at most 100 characters."),
  customer_phone: z
    .string()
    .refine((value) => normalizePhone(value) !== null, "Enter a Malaysian phone number, e.g. 012-345 6789."),
});

export type CounterBookingInput = z.infer<typeof counterBookingSchema>;

/** What the API receives: numbers for the court and length, the phone in one format. */
export function toCounterBookingPayload(values: CounterBookingInput) {
  return {
    source: values.source,
    court_id: Number(values.court_id),
    sport: values.sport,
    starts_at: values.starts_at,
    minutes: Number(values.minutes),
    customer_name: values.customer_name.trim(),
    customer_phone: normalizePhone(values.customer_phone) ?? values.customer_phone,
  };
}

/** Closing one or more courts for a while (FR-21). */
export const blockSchema = z.object({
  /** Checkbox values: court ids as text. */
  court_ids: z.array(z.string()).min(1, "Choose at least one court."),
  starts_at: z.string().min(1, "Choose a start time."),
  minutes: z.string(),
  reason: z.string().trim().min(1, "Say why, e.g. floor repair or private event.").max(100, "Use at most 100 characters."),
});

export type BlockInput = z.infer<typeof blockSchema>;

export function toBlockPayload(values: BlockInput) {
  return {
    court_ids: values.court_ids.map(Number),
    starts_at: values.starts_at,
    minutes: Number(values.minutes),
    reason: values.reason.trim(),
  };
}
