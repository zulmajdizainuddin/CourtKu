/**
 * Malaysian phone numbers in one format: +60, then the number without its leading 0.
 * "012-345 6789" -> "+60123456789". Returns null for anything that isn't Malaysian.
 * The same rule as App\Support\MalaysianPhone in the API, which checks it again.
 */
export function normalizePhone(input: string): string | null {
  const number = input.replace(/[\s\-().]/g, "");

  let national: string | null = null;
  if (number.startsWith("+60")) national = number.slice(3);
  else if (number.startsWith("60")) national = number.slice(2);
  else if (number.startsWith("0")) national = number.slice(1);

  // After the 0: 8 to 10 digits, never starting with 0.
  return national !== null && /^[1-9]\d{7,9}$/.test(national) ? `+60${national}` : null;
}

/**
 * A stored number the way Malaysians write it:
 * "+60123456789" -> "012-345 6789", "+601123456789" -> "011-2345 6789", "+60312345678" -> "03-1234 5678".
 */
export function formatPhone(stored: string): string {
  const local = `0${stored.replace(/^\+60/, "")}`;
  if (local.startsWith("01")) {
    // Mobile: 3-digit prefix, then the rest split before the last 4.
    return `${local.slice(0, 3)}-${local.slice(3, -4)} ${local.slice(-4)}`;
  }
  // Landline: 2-digit area code (03 Klang Valley, 04 Penang...), then the rest split before the last 4.
  return `${local.slice(0, 2)}-${local.slice(2, -4)} ${local.slice(-4)}`;
}
