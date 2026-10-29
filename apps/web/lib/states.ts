// Malaysia's 13 states and 3 federal territories, for venue addresses.
// The values must match App\Enums\MalaysianState in the API, which checks them again.

export const MALAYSIAN_STATES = [
  { value: "johor", label: "Johor" },
  { value: "kedah", label: "Kedah" },
  { value: "kelantan", label: "Kelantan" },
  { value: "melaka", label: "Melaka" },
  { value: "negeri-sembilan", label: "Negeri Sembilan" },
  { value: "pahang", label: "Pahang" },
  { value: "perak", label: "Perak" },
  { value: "perlis", label: "Perlis" },
  { value: "pulau-pinang", label: "Pulau Pinang" },
  { value: "sabah", label: "Sabah" },
  { value: "sarawak", label: "Sarawak" },
  { value: "selangor", label: "Selangor" },
  { value: "terengganu", label: "Terengganu" },
  { value: "kuala-lumpur", label: "Kuala Lumpur" },
  { value: "labuan", label: "Labuan" },
  { value: "putrajaya", label: "Putrajaya" },
] as const;

export function isMalaysianState(value: string): boolean {
  return MALAYSIAN_STATES.some((s) => s.value === value);
}

/** "negeri-sembilan" -> "Negeri Sembilan" */
export function stateLabel(value: string): string {
  return MALAYSIAN_STATES.find((s) => s.value === value)?.label ?? value;
}
