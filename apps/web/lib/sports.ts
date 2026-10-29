// The sports a court can offer. The values must match the "sports" table in the API
// (added by its migration: badminton, futsal, pickleball).

export const SPORTS = [
  { value: "badminton", label: "Badminton" },
  { value: "futsal", label: "Futsal" },
  { value: "pickleball", label: "Pickleball" },
] as const;

export function sportLabel(value: string): string {
  return SPORTS.find((s) => s.value === value)?.label ?? value;
}
