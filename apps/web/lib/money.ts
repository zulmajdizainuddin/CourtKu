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

/** 2500 -> "RM 25.00", 125050 -> "RM 1,250.50" (the same as App\Support\Money in the API) */
export function formatRinggit(sen: number): string {
  const ringgit = Math.floor(sen / 100).toLocaleString("en-MY");
  return `RM ${ringgit}.${String(sen % 100).padStart(2, "0")}`;
}

/** 2550 -> "25.50": the plain number shown in a price input. */
export function senToInput(sen: number): string {
  return `${Math.floor(sen / 100)}.${String(sen % 100).padStart(2, "0")}`;
}
