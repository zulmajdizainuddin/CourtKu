import { createHmac } from "node:crypto";
import { expect, type Page, type TestInfo } from "@playwright/test";

// Shared bits for the browser tests (ADR-034). The accounts come from apps/api/database/seeders/E2eSeeder.php.

/** The test password of every seeded account (a test value, also in E2eSeeder::PASSWORD). */
export const PASSWORD = "E2e-Test-Pass-2026";

/** The owners' two-factor secrets, by screen size (also in E2eSeeder::OWNER_SECRETS). */
const OWNER_SECRETS: Record<string, string> = { desktop: "JBSWY3DPEHPK3PXP", phone: "KRSXG5CTMVRXEZLU" };

/** "desktop" or "phone": each screen size has its own accounts, so the two runs never clash. */
export function screen(info: TestInfo): string {
  return info.project.name;
}

export function account(role: "player" | "staff" | "owner", info: TestInfo): string {
  return `${role}-${screen(info)}@example.com`;
}

/** Logs in through the login page, then waits until it has moved on (to `next` or the two-factor page). */
export async function logIn(page: Page, email: string, next: string) {
  await page.goto(`/login?next=${encodeURIComponent(next)}`);
  await page.getByLabel("Email").fill(email);
  await page.getByLabel("Password").fill(PASSWORD);
  await page.locator("form").getByRole("button", { name: "Log in" }).click();
  await expect(page).not.toHaveURL(/\/login/);
}

/** The owner's current two-factor code, like an authenticator app (RFC 6238: 30 seconds, 6 digits). */
export function ownerCode(info: TestInfo, at = Date.now()): string {
  const secret = OWNER_SECRETS[screen(info)];
  const alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ234567";
  let bits = "";
  for (const char of secret) bits += alphabet.indexOf(char).toString(2).padStart(5, "0");
  const key = Buffer.from(bits.match(/.{8}/g)!.map((b) => parseInt(b, 2)));
  const counter = Buffer.alloc(8);
  counter.writeBigUInt64BE(BigInt(Math.floor(at / 30_000)));
  const hmac = createHmac("sha1", key).update(counter).digest();
  const offset = hmac[hmac.length - 1] & 0xf;
  return String((hmac.readUInt32BE(offset) & 0x7fffffff) % 1_000_000).padStart(6, "0");
}

/** A date in Malaysia, `days` from today, as "2026-11-06". */
export function malaysiaDate(days: number): string {
  return new Date(Date.now() + 8 * 3_600_000 + days * 86_400_000).toISOString().slice(0, 10);
}

/** Picks one of the booking page's choices (date, sport, length, start time, court) by its value. */
export async function choose(page: Page, value: string) {
  await page.locator(`label:has(input[type="radio"][value="${value}"])`).click();
}

/** Picks a court on the booking page by its name, e.g. "Court 1". */
export async function chooseCourt(page: Page, name: string) {
  await page.locator("label:has(input[type=radio])").filter({ hasText: name }).click();
}

/** The link in the newest email to `to`, from Mailpit (the API's local mail catcher). */
export async function linkInEmail(to: string, pattern: RegExp): Promise<string> {
  const mailpit = process.env.MAILPIT_URL ?? "http://127.0.0.1:8025";
  let link: string | undefined;
  await expect
    .poll(
      async () => {
        const found = await (await fetch(`${mailpit}/api/v1/search?query=${encodeURIComponent(`to:"${to}"`)}`)).json();
        const id = found.messages?.[0]?.ID;
        if (!id) return undefined;
        const message = await (await fetch(`${mailpit}/api/v1/message/${id}`)).json();
        link = (message.Text as string).match(pattern)?.[0];
        return link;
      },
      { message: `an email to ${to} with a link`, timeout: 20_000 },
    )
    .toBeTruthy();
  return link!;
}
