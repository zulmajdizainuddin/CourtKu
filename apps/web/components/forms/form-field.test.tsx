import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { FormField, SelectField } from "./form-field";

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

  it("reads out the help line and the error together", () => {
    render(<FormField label="Web address" name="slug" hint="Your page's address." error="Already taken." />);

    expect(screen.getByLabelText("Web address")).toHaveAccessibleDescription("Your page's address. Already taken.");
  });
});

describe("field ids", () => {
  it("keeps each label on its own field, even when two fields share a name", () => {
    // e.g. the hidden login form's "email" and the staff invite form's "email" on one page.
    render(
      <>
        <FormField label="Email" name="email" />
        <FormField label="Invite staff by email" name="email" />
      </>,
    );

    expect(screen.getByLabelText("Email")).not.toBe(screen.getByLabelText("Invite staff by email"));
    expect(screen.getByLabelText("Invite staff by email")).toHaveAttribute("name", "email");
  });
});

describe("SelectField", () => {
  it("offers the options under a label, with a placeholder first", () => {
    render(
      <SelectField
        label="State"
        name="state"
        placeholder="Choose a state"
        options={[{ value: "selangor", label: "Selangor" }]}
        error="Choose a state."
      />,
    );

    const select = screen.getByLabelText("State");
    expect(screen.getAllByRole("option").map((o) => o.textContent)).toEqual(["Choose a state", "Selangor"]);
    expect(select).toHaveAccessibleDescription("Choose a state.");
  });
});
