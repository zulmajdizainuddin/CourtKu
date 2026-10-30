/** 30 -> "30 minutes", 60 -> "1 hour", 90 -> "1 hour 30 minutes", 180 -> "3 hours" */
export function formatDuration(minutes: number): string {
  const hours = Math.floor(minutes / 60);
  const rest = minutes % 60;
  const parts = [
    hours > 0 ? `${hours} hour${hours === 1 ? "" : "s"}` : null,
    rest > 0 ? `${rest} minutes` : null,
  ].filter(Boolean);
  return parts.join(" ");
}

/** Every whole number of slots from one slot up to `max` minutes: 60-minute slots -> 60, 120, 180... */
export function durationOptions(slotMinutes: number, maxMinutes: number): number[] {
  return Array.from({ length: Math.floor(maxMinutes / slotMinutes) }, (_, i) => (i + 1) * slotMinutes);
}
