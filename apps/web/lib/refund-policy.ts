// FR-40: a venue's cancellation and refund policy, in plain words, shown before players pay (ADR-032).
// CourtKu writes the rule from the venue's cancel cutoff, so it always matches how cancelling really
// works; the owner can add a short note of their own.

export type RefundRules = {
  cancel_cutoff_hours: number;
  pay_online: boolean;
  refund_note?: string | null;
};

/** The policy as short sentences, in the order players should read them. */
export function refundPolicy(rules: RefundRules): string[] {
  const hours = `${rules.cancel_cutoff_hours} ${rules.cancel_cutoff_hours === 1 ? "hour" : "hours"}`;
  const lines = [`Cancel online for free up to ${hours} before the start.`];
  if (rules.pay_online) {
    lines.push("Paid online? The venue refunds the full amount when you cancel in time. It takes a few days to reach your card or bank.");
  }
  lines.push(`Less than ${hours} before, you can't cancel online. Contact the venue; any refund is up to them.`);
  if (rules.pay_online) {
    lines.push("If you don't turn up, an online payment is not refunded.");
  }
  return lines;
}
