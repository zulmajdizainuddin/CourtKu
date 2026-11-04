import { expect, test } from "@playwright/test";
import { account, choose, chooseCourt, logIn, malaysiaDate, screen } from "./support";

// Flow 2: a player books, pays online (on the pretend Stripe page, ADR-034), and then cancels.
test("a player pays online, then cancels", async ({ page }, info) => {
  await logIn(page, account("player", info), "/v/e2e-arena/book");
  await expect(page.getByRole("heading", { name: "Book a court" })).toBeVisible();

  // Each screen size books its own day, so the two runs never want the same court.
  const day = malaysiaDate(screen(info) === "desktop" ? 2 : 3);
  await choose(page, day);
  await choose(page, "badminton");
  await choose(page, `${day}T10:00:00+08:00`);
  await chooseCourt(page, "Court 1");

  // FR-40: the refund policy is shown before paying.
  await expect(page.getByText("Cancel online for free up to 24 hours before the start.")).toBeVisible();
  await page.getByRole("button", { name: "Pay RM 20.00 online" }).click();

  await expect(page.getByRole("heading", { name: "Pretend Stripe" })).toBeVisible();
  await page.getByRole("link", { name: "Pay RM 20.00" }).click();

  await expect(page.getByText("Paid. You're booked!")).toBeVisible();
  await page.getByRole("link", { name: "View booking" }).click();

  page.once("dialog", (dialog) => dialog.accept());
  await page.getByRole("button", { name: "Cancel booking" }).click();
  await expect(page.getByText(/The time is free for others again/)).toBeVisible();
  await expect(page.getByText(/The venue will refund your online payment/)).toBeVisible();
});
