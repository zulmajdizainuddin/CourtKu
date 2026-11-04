import Link from "next/link";
import { type RefundRules, refundPolicy } from "@/lib/refund-policy";

/**
 * FR-40: the venue's cancellation and refund policy, shown on its page and next to the Pay button
 * (ADR-032). The rule comes from the venue's cancel setting; the owner's note, if any, follows it.
 */
export function RefundPolicy({ rules, className }: { rules: RefundRules; className?: string }) {
  return (
    <div className={className}>
      <ul className="grid list-disc gap-1 pl-5">
        {refundPolicy(rules).map((line) => (
          <li key={line}>{line}</li>
        ))}
      </ul>
      {rules.refund_note && (
        <p className="mt-2 whitespace-pre-line">
          <span className="font-medium">From the venue:</span> {rules.refund_note}
        </p>
      )}
      <p className="mt-2 text-muted-foreground">
        More in the{" "}
        <Link href="/terms#bookings" className="underline">
          Terms of Use
        </Link>
        .
      </p>
    </div>
  );
}
