// The sample photo shown for a venue without its own photos (ADR-031): an empty court or hall of its
// main sport, picked from the venue's address so each venue always shows the same one and
// neighbours differ. All are free Unsplash photos (Unsplash License), linked from images.unsplash.com.

export type StockSport = "badminton" | "futsal" | "pickleball";

// Each entry is the image id; the photo's page is noted beside it.
const PHOTOS: Record<StockSport, string[]> = {
  badminton: [
    "photo-1775993167284-8e6a6e56ab69", // unsplash.com/photos/R3S6YdeHA6A
    "photo-1775993167393-f2add1f8eec2", // unsplash.com/photos/AILEpOyczIk
    "photo-1564769353575-73f33a36d84f", // unsplash.com/photos/Bt-oCv_YI3E
    "photo-1787705190608-352c19d2ba26", // unsplash.com/photos/6qTZBVH2h4Y
    "photo-1771909720886-a90afd1b37f5", // unsplash.com/photos/okCrVJ6hI7o
    "photo-1771909715670-083a55b7f354", // unsplash.com/photos/Mll0kFmv8y4
  ],
  futsal: [
    "photo-1553627220-92f0446b6a5f", // unsplash.com/photos/IxtM5H-l1rI
    "photo-1789280421673-9a452f4d01e1", // unsplash.com/photos/xN-eFbiisZA
    "photo-1774296245164-aae88288f893", // unsplash.com/photos/LHyiIwDATOI
    "photo-1788880459150-616382777c8b", // unsplash.com/photos/zZeRAFgGBsg
    "photo-1701277050203-eabfa03e64eb", // unsplash.com/photos/lTydd5V_cUE
    "photo-1774599467191-04e2c399a117", // unsplash.com/photos/HZzNCojYV0k
  ],
  pickleball: [
    "photo-1778180883807-19962eda4d99", // unsplash.com/photos/YIIvX8SIr-U
    "photo-1778180883944-9b8403391d85", // unsplash.com/photos/cNuo2I6bznQ
    "photo-1778180883854-fc3093702a4f", // unsplash.com/photos/PAfdAY_aCzY
    "photo-1703376827787-899a034ac43e", // unsplash.com/photos/O0Sv-I54awE
    "photo-1782035177581-1a5b3c4b8b39", // unsplash.com/photos/YcO2IEYnnq0
  ],
};

/** The sample photo's address, cropped to the size asked for. Badminton when the sport is unknown. */
export function stockPhoto(sport: string | null | undefined, seed: string, width: number, height: number): string {
  const kind: StockSport = sport === "futsal" || sport === "pickleball" ? sport : "badminton";
  // A small, steady number from the address: the same venue always gets the same photo.
  let hash = 0;
  for (const char of seed) hash = (hash * 31 + char.charCodeAt(0)) >>> 0;
  const photos = PHOTOS[kind];
  const id = photos[hash % photos.length];
  return `https://images.unsplash.com/${id}?w=${width}&h=${height}&fit=crop&auto=format&q=70`;
}

/** The sport most of a venue's courts offer (alphabetical on a tie), or null without courts. */
export function mainSport(courts: { sports: string[] }[]): string | null {
  const counts = new Map<string, number>();
  for (const court of courts) for (const sport of court.sports) counts.set(sport, (counts.get(sport) ?? 0) + 1);
  return [...counts.entries()].sort((a, b) => b[1] - a[1] || a[0].localeCompare(b[0]))[0]?.[0] ?? null;
}
