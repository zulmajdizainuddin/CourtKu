/** The API's rule for venue web addresses: lowercase letters and numbers, joined by single hyphens. */
export const SLUG_PATTERN = /^[a-z0-9]+(?:-[a-z0-9]+)*$/;

/**
 * Suggests a web address from a venue name: "Arena Sg. Buloh!" -> "arena-sg-buloh".
 * The owner can still change it before saving.
 */
export function slugify(name: string): string {
  return name
    .normalize("NFKD")
    .replace(/[̀-ͯ]/g, "") // "é" -> "e"
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .slice(0, 60)
    .replace(/-+$/, ""); // cutting at 60 can leave a hyphen at the end
}
