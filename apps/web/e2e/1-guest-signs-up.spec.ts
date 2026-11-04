import { expect, test } from "@playwright/test";
import { linkInEmail, PASSWORD, screen } from "./support";

// Flow 1: a guest finds a venue on the home page, reads its refund policy, and signs up to book.
test("a guest finds a venue and signs up", async ({ page }, info) => {
  await page.goto("/");
  await expect(page.getByRole("heading", { name: "Find a court" })).toBeVisible();

  await page.getByLabel("Search").fill("E2E");
  await page.getByRole("link", { name: /E2E Arena/ }).click();
  await expect(page.getByRole("heading", { level: 1, name: "E2E Arena" })).toBeVisible();
  await expect(page.getByText("Cancellation and refunds")).toBeVisible();

  await page.getByRole("link", { name: "Book a court" }).click();
  await expect(page.getByRole("link", { name: "Log in to book" })).toBeVisible();
  await page.getByRole("main").getByRole("link", { name: "Sign up" }).click();

  const email = `guest-${screen(info)}-${Date.now()}@example.com`;
  await page.getByLabel("Full name").fill("Guest Player");
  await page.getByLabel("Email").fill(email);
  await page.getByLabel("Password", { exact: true }).fill(PASSWORD);
  await page.getByLabel("Confirm password").fill(PASSWORD);
  await expect(page.getByText("By creating an account you agree to the")).toBeVisible();
  await page.getByRole("button", { name: "Create account" }).click();
  await expect(page).toHaveURL(/\/verify-email/);

  // The link in the verification email, as the player would click it.
  await page.goto(await linkInEmail(email, /https?:\/\/\S+\/verify\/\S+/));
  await expect(page.getByText("Your email is verified.")).toBeVisible();
});
