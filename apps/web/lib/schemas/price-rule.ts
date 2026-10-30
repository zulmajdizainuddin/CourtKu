import { z } from "zod";
import { hourlyPriceProblem, parseRinggit } from "@/lib/money";

// The same rules as the API's PriceRuleRequest. The API checks them again,
// and also that the new price doesn't overlap another one for the same courts (ADR-020).

export const priceRuleSchema = z
  .object({
    name: z.string().trim().max(50, "Use at most 50 characters."),
    /** "" = all courts, otherwise a court id. */
    court_id: z.string(),
    /** Checkbox values: "0" = Sunday ... "6" = Saturday. */
    weekdays: z.array(z.string()).min(1, "Choose at least one day."),
    starts_at: z.string(),
    ends_at: z.string(),
    price: z.string().superRefine((value, ctx) => {
      const problem = value.trim() === "" ? "Enter the price per hour." : hourlyPriceProblem(value);
      if (problem) ctx.addIssue({ code: "custom", message: problem });
    }),
  })
  .refine((values) => values.starts_at !== values.ends_at, {
    path: ["ends_at"],
    message: "The end time must be different from the start time.",
  });

export type PriceRuleInput = z.infer<typeof priceRuleSchema>;

/** What the API receives: numbers for the court, days and price; null for "all courts" or no name. */
export function toPriceRulePayload(values: PriceRuleInput) {
  return {
    name: values.name.trim() === "" ? null : values.name.trim(),
    court_id: values.court_id === "" ? null : Number(values.court_id),
    weekdays: values.weekdays.map(Number).sort((a, b) => a - b),
    starts_at: values.starts_at,
    ends_at: values.ends_at,
    price_sen: parseRinggit(values.price) ?? 0,
  };
}

export type PriceRulePayload = ReturnType<typeof toPriceRulePayload>;
