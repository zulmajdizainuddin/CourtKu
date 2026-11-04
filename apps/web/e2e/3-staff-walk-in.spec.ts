import { expect, test } from "@playwright/test";
import { account, logIn, screen } from "./support";

// Flow 3: a staff member adds a walk-in for the next free half hour and checks the customer in.
test("staff add a walk-in and check it in", async ({ page }, info) => {
  await logIn(page, account("staff", info), "/dashboard");
  await page.getByRole("link", { name: /E2E Arena/ }).click();
  await page.getByRole("link", { name: "Schedule", exact: true }).click();
  await expect(page.getByRole("button", { name: "Add booking" })).toBeVisible();

  // The next start time that hasn't begun (one later on a retry, in case the first try took it).
  // Late at night there may be none left today, so look at tomorrow.
  const startOptions = async () => {
    await page.getByRole("button", { name: "Add booking" }).click();
    const values = await page.getByLabel("Start time").locator("option").evaluateAll((options) =>
      options.map((o) => (o as HTMLOptionElement).value).filter((v) => new Date(v).getTime() > Date.now()),
    );
    return values;
  };
  let times = await startOptions();
  if (times.length <= info.retry) {
    await page.getByRole("button", { name: "Cancel", exact: true }).click();
    await page.getByRole("button", { name: "Next day" }).click();
    times = await startOptions();
  }

  const customer = `Walk-in ${screen(info)}`;
  // Each screen size uses its own court, so the two runs never want the same half hour.
  await page.getByLabel("Court").selectOption({ label: screen(info) === "desktop" ? "Court 1" : "Court 2" });
  await page.getByLabel("Start time").selectOption(times[info.retry]);
  await page.getByLabel("Customer's name").fill(customer);
  await page.getByLabel("Customer's phone").fill("012-345 6789");
  await page.getByRole("button", { name: "Book, paid at the counter" }).click();

  await page.getByRole("button", { name: new RegExp(customer) }).click();
  const details = page.getByRole("region", { name: /^Booking CK-/ });
  await details.getByRole("button", { name: "Check in" }).click();
  await expect(details.getByText("Checked in")).toBeVisible();
});
