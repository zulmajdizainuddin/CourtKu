// What a venue offers besides its courts. The values must match App\Enums\Facility in the API.

export const FACILITIES = [
  { value: "parking", label: "Parking" },
  { value: "surau", label: "Surau" },
  { value: "toilets", label: "Toilets" },
  { value: "showers", label: "Showers" },
  { value: "changing_rooms", label: "Changing rooms" },
  { value: "lockers", label: "Lockers" },
  { value: "canteen", label: "Canteen" },
  { value: "wifi", label: "Wi-Fi" },
  { value: "air_conditioning", label: "Air-conditioning" },
  { value: "equipment_rental", label: "Racket and ball rental" },
] as const;

export function facilityLabel(value: string): string {
  return FACILITIES.find((f) => f.value === value)?.label ?? value;
}
