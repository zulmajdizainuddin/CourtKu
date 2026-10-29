// The API sends times in UTC (ISO 8601); people read them in Malaysia time (NFR-12).

const dateFormat = new Intl.DateTimeFormat("en-MY", {
  dateStyle: "medium",
  timeZone: "Asia/Kuala_Lumpur",
});

/** "2026-10-29T16:30:00+00:00" -> "30 Oct 2026" (already the next day in Malaysia) */
export function formatDate(iso: string): string {
  return dateFormat.format(new Date(iso));
}
