import { describe, expect, it } from "vitest";
import { fitWithin } from "./resize-image";

describe("fitWithin", () => {
  it("shrinks a big phone photo to 2000 pixels on its longest side, keeping the shape", () => {
    expect(fitWithin(4032, 3024)).toEqual({ width: 2000, height: 1500 }); // landscape
    expect(fitWithin(3024, 4032)).toEqual({ width: 1500, height: 2000 }); // portrait
  });

  it("never makes a small photo bigger", () => {
    expect(fitWithin(1200, 900)).toEqual({ width: 1200, height: 900 });
  });
});
