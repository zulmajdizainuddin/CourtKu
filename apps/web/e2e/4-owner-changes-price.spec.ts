import { expect, test } from "@playwright/test";
import { account, choose, logIn, malaysiaDate, ownerCode, screen } from "./support";

// Flow 4: an owner logs in with two-factor, changes a court's price, and players see the new price.
test("an owner changes a price, and the booking page shows it", async ({ page }, info) => {
  await logIn(page, account("owner", info), "/dashboard");
  await expect(page).toHaveURL(/\/two-factor/);
  await page.getByLabel("6-digit code").fill(ownerCode(info));
  await page.getByRole("button", { name: "Log in" }).click();
  await expect(page).toHaveURL(/\/dashboard/);

  await page.getByRole("link", { name: /E2E Arena/ }).click();
  await page.getByRole("link", { name: "Courts", exact: true }).click();
  await page.getByRole("button", { name: "Edit Pickleball 1" }).click();
  const price = screen(info) === "desktop" ? "33.00" : "34.00";
  await page.getByLabel("Price per hour (RM)").fill(price);
  await page.getByRole("button", { name: "Save court" }).click();
  await expect(page.getByText(`RM ${price} an hour`)).toBeVisible();

  await page.goto("/v/e2e-arena/book");
  const day = malaysiaDate(4);
  await choose(page, day);
  await choose(page, "pickleball");
  await choose(page, `${day}T10:00:00+08:00`);
  await expect(page.locator("label").filter({ hasText: "Pickleball 1" })).toContainText(`RM ${price}`);
});
