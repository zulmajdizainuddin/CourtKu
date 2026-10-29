import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { cn } from "cn";
import type { ComponentProps } from "react";

type FieldProps = {
  label: string;
  name: string;
  error?: string;
  /** A short help line under the input, e.g. what the field is for. */
  hint?: React.ReactNode;
};

/** Links the help line and the error to the input, so screen readers announce them. */
function describedBy(name: string, hint: React.ReactNode, error?: string) {
  const ids = [hint ? `${name}-hint` : null, error ? `${name}-error` : null].filter(Boolean);
  return ids.length > 0 ? ids.join(" ") : undefined;
}

function FieldMessages({ name, hint, error }: Omit<FieldProps, "label">) {
  return (
    <>
      {hint && (
        <p id={`${name}-hint`} className="text-sm text-muted-foreground">
          {hint}
        </p>
      )}
      {error && (
        <p id={`${name}-error`} className="text-sm text-destructive">
          {error}
        </p>
      )}
    </>
  );
}

/** A label, an input and its error message, wired together for screen readers. */
export function FormField({ label, name, error, hint, ...inputProps }: ComponentProps<typeof Input> & FieldProps) {
  return (
    <div className="grid gap-1.5">
      <Label htmlFor={name}>{label}</Label>
      <Input
        id={name}
        name={name}
        aria-invalid={error ? true : undefined}
        aria-describedby={describedBy(name, hint, error)}
        className="h-11 text-base"
        {...inputProps}
      />
      <FieldMessages name={name} hint={hint} error={error} />
    </div>
  );
}

/**
 * The same as FormField, with a dropdown. A plain <select> on purpose:
 * phones show their own easy picker for it.
 */
export function SelectField({
  label,
  name,
  error,
  hint,
  options,
  placeholder,
  className,
  ...selectProps
}: ComponentProps<"select"> &
  FieldProps & { options: readonly { value: string; label: string }[]; placeholder?: string }) {
  return (
    <div className="grid gap-1.5">
      <Label htmlFor={name}>{label}</Label>
      <select
        id={name}
        name={name}
        aria-invalid={error ? true : undefined}
        aria-describedby={describedBy(name, hint, error)}
        className={cn(
          "h-11 w-full rounded-lg border border-input bg-transparent px-2.5 text-base outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-3 aria-invalid:ring-destructive/20 dark:bg-input/30",
          className,
        )}
        {...selectProps}
      >
        {placeholder && <option value="">{placeholder}</option>}
        {options.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
      <FieldMessages name={name} hint={hint} error={error} />
    </div>
  );
}

/** The same as FormField, for longer text over several lines. */
export function TextareaField({
  label,
  name,
  error,
  hint,
  className,
  ...textareaProps
}: ComponentProps<"textarea"> & FieldProps) {
  return (
    <div className="grid gap-1.5">
      <Label htmlFor={name}>{label}</Label>
      <textarea
        id={name}
        name={name}
        aria-invalid={error ? true : undefined}
        aria-describedby={describedBy(name, hint, error)}
        className={cn(
          "min-h-24 w-full rounded-lg border border-input bg-transparent px-2.5 py-2 text-base outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-3 aria-invalid:ring-destructive/20 dark:bg-input/30",
          className,
        )}
        {...textareaProps}
      />
      <FieldMessages name={name} hint={hint} error={error} />
    </div>
  );
}

/** A message for errors that don't belong to one field, e.g. "Too many attempts". */
export function FormAlert({ message, tone = "error" }: { message?: string; tone?: "error" | "success" }) {
  if (!message) return null;
  return (
    <p
      role={tone === "error" ? "alert" : "status"}
      className={
        tone === "error"
          ? "rounded-lg bg-destructive/10 px-3 py-2 text-sm text-destructive"
          : "rounded-lg bg-emerald-500/10 px-3 py-2 text-sm text-emerald-700 dark:text-emerald-400"
      }
    >
      {message}
    </p>
  );
}
