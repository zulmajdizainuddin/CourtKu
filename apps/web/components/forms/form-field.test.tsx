import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { FormField } from "./form-field";

describe("FormField", () => {
  it("links the error message to the input so screen readers announce it", () => {
    render(<FormField label="Email" name="email" error="Enter a valid email address." />);

    const input = screen.getByLabelText("Email");
    expect(input).toHaveAttribute("aria-invalid", "true");
    expect(input).toHaveAccessibleDescription("Enter a valid email address.");
  });

  it("shows no error when there is none", () => {
    render(<FormField label="Email" name="email" />);

    expect(screen.getByLabelText("Email")).not.toHaveAttribute("aria-invalid");
  });
});
