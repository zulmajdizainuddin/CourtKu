// Money is whole sen in CourtKu (RM 25.00 = 2500), never a decimal number (NFR-13).
// These turn what an owner types into sen, and sen back into text, using whole numbers only.

/**
 * "25" -> 2500, "25.5" -> 2550, "RM 1,250.50" -> 125050.
 * null when it isn't an amount of ringgit (letters, more than 2 decimals, negative).
 */
export function parseRinggit(text: string): number | null {
  const match = text.trim().replace(/^RM\s*/i, "").replace(/,/g, "").match(/^(\d+)(?:\.(\d{1,2}))?$/);
  if (!match) return null;
  const [, ringgit, cents = ""] = match;
  return Number(ringgit) * 100 + Number(cents.padEnd(2, "0"));
}

/**
 * What is wrong with a typed price per hour, or undefined when it is fine.
 * The same rules as the API: 10-sen steps (ADR-017), at most RM 1,000.00.
 */
export function hourlyPriceProblem(text: string): string | undefined {
  const sen = parseRinggit(text);
  if (sen === null) return "Enter a price like 25.00.";
  if (sen % 10 !== 0) return "Use a price in 10-sen steps, e.g. 25.00 or 25.50.";
  if (sen > 100000) return "The price can be at most RM 1,000.00 an hour.";
  return undefined;
}

/** 2500 -> "RM 25.00", 125050 -> "RM 1,250.50" (the same as App\Support\Money in the API) */
export function formatRinggit(sen: number): string {
  const ringgit = Math.floor(sen / 100).toLocaleString("en-MY");
  return `RM ${ringgit}.${String(sen % 100).padStart(2, "0")}`;
}

/** 2550 -> "25.50": the plain number shown in a price input. */
export function senToInput(sen: number): string {
  return `${Math.floor(sen / 100)}.${String(sen % 100).padStart(2, "0")}`;
}
