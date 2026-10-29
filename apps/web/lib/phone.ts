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
