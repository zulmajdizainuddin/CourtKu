import { describe, expect, it } from "vitest";
import { refundPolicy } from "./refund-policy";

describe("refundPolicy", () => {
  it("states the venue's cancel cutoff", () => {
    const lines = refundPolicy({ cancel_cutoff_hours: 12, pay_online: false });
    expect(lines[0]).toBe("Cancel online for free up to 12 hours before the start.");
    expect(lines).toContain("Less than 12 hours before, you can't cancel online. Contact the venue; any refund is up to them.");
  });

  it("explains refunds and no-shows only when the venue takes online payment", () => {
    expect(refundPolicy({ cancel_cutoff_hours: 24, pay_online: false }).join(" ")).not.toMatch(/refunds the full amount|turn up/);

    const online = refundPolicy({ cancel_cutoff_hours: 24, pay_online: true }).join(" ");
    expect(online).toMatch(/refunds the full amount/);
    expect(online).toMatch(/don't turn up/);
  });
});
