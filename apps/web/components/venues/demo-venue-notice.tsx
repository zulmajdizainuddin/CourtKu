/**
 * On every page of a demo venue (ADR-030): it is made up, so nobody mistakes it for a real place,
 * and bookings made here are cleared overnight.
 */
export function DemoVenueNotice() {
  return (
    <p role="note" className="rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-950 ring-1 ring-amber-300 dark:bg-amber-950 dark:text-amber-100 dark:ring-amber-800">
      <span className="font-medium">Demo venue:</span> this venue and its bookings are made up, so you can try CourtKu. Book
      a court to see how it works; anything you book here is cleared overnight.
    </p>
  );
}
