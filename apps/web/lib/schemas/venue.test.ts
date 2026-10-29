import { describe, expect, it } from "vitest";
import { toProfilePayload, venueProfileSchema } from "./venue";

const valid = {
  name: "Arena Sungai Buloh",
  slug: "arena-sg-buloh",
  state: "selangor",
  description: "",
  address_line: "",
  city: "",
  postcode: "",
  phone: "",
  facilities: [],
};

describe("venueProfileSchema", () => {
  it("allows every optional field to be empty", () => {
    expect(venueProfileSchema.safeParse(valid).success).toBe(true);
  });

  it("explains a wrong postcode or phone number", () => {
    const result = venueProfileSchema.safeParse({ ...valid, postcode: "4700", phone: "+65 9123 4567" });
    const messages = result.error?.issues.map((i) => `${String(i.path[0])}: ${i.message}`);

    expect(messages).toEqual([
      "postcode: Use the 5-digit postcode, e.g. 47000.",
      "phone: Enter a Malaysian phone number, e.g. 012-345 6789 or 03-1234 5678.",
    ]);
  });
});

describe("toProfilePayload", () => {
  it("sends empty fields as null, so the API clears them", () => {
    expect(toProfilePayload({ ...valid, city: "  ", phone: " 012-345 6789 " })).toMatchObject({
      city: null,
      phone: "012-345 6789",
      postcode: null,
    });
  });
});
