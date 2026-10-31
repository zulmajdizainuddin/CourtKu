/**
 * Plan section 9: wherever a player pays, say plainly that this is a demo. Players always pay on
 * Stripe's own page; CourtKu never imitates a bank or card page.
 */
export function DemoNotice() {
  return (
    <strong className="font-medium text-amber-700 dark:text-amber-400">
      Demo mode: no real money is charged. Pay with the test card 4242 4242 4242 4242, any future date and any CVC.
    </strong>
  );
}
