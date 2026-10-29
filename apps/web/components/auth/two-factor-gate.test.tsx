import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import type { User } from "@/features/auth/hooks";
import { TwoFactorGate } from "./two-factor-gate";

vi.mock("next/navigation", () => ({ usePathname: () => "/admin" }));

const user = (overrides: Partial<User>): User => ({
  id: 1,
  name: "Aina",
  email: "aina@example.com",
  phone: null,
  email_verified: true,
  is_platform_admin: false,
  two_factor_enabled: false,
  two_factor_required: false,
  ...overrides,
});

describe("TwoFactorGate", () => {
  it("sends an owner without two-factor to set it up, then back to this page", () => {
    render(<TwoFactorGate user={user({ two_factor_required: true })}>Dashboard</TwoFactorGate>);

    expect(screen.queryByText("Dashboard")).not.toBeInTheDocument();
    expect(screen.getByRole("link", { name: /set it up/i })).toHaveAttribute("href", "/me/security?next=%2Fadmin");
  });

  it("lets an owner with two-factor through", () => {
    render(
      <TwoFactorGate user={user({ two_factor_required: true, two_factor_enabled: true })}>Dashboard</TwoFactorGate>,
    );

    expect(screen.getByText("Dashboard")).toBeInTheDocument();
  });

  it("lets staff and players through without two-factor", () => {
    render(<TwoFactorGate user={user({})}>Dashboard</TwoFactorGate>);

    expect(screen.getByText("Dashboard")).toBeInTheDocument();
  });

  it("asks everyone when the page makes them an owner (always)", () => {
    render(
      <TwoFactorGate user={user({})} always>
        List your venue
      </TwoFactorGate>,
    );

    expect(screen.queryByText("List your venue")).not.toBeInTheDocument();
  });
});
