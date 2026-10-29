import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { cn } from "cn";
import { type ComponentProps, useId } from "react";

type FieldProps = {
  label: string;
  name: string;
  error?: string;
  /** A short help line under the input, e.g. what the field is for. */
  hint?: React.ReactNode;
};

/**
 * Ids that link a field's label, help line and error together, unique on the whole page.
 * Not just the field's name: Next.js keeps recently visited pages hidden in the page
 * (cacheComponents), so the login form's "email" field may still be there, and a label
 * pointing at "email" could find that hidden field instead of this one.
 */
function useFieldIds(name: string, hint: React.ReactNode, error?: string) {
  const id = `${name}-${useId()}`;
  const hintId = `${id}-hint`;
  const errorId = `${id}-error`;
  const ids = [hint ? hintId : null, error ? errorId : null].filter(Boolean);
  return { id, hintId, errorId, describedBy: ids.length > 0 ? ids.join(" ") : undefined };
}

function FieldMessages({ hint, error, hintId, errorId }: Pick<FieldProps, "hint" | "error"> & { hintId: string; errorId: string }) {
  return (
    <>
      {hint && (
        <p id={hintId} className="text-sm text-muted-foreground">
          {hint}
        </p>
      )}
      {error && (
        <p id={errorId} className="text-sm text-destructive">
          {error}
        </p>
      )}
    </>
  );
}

/** A label, an input and its error message, wired together for screen readers. */
export function FormField({ label, name, error, hint, ...inputProps }: ComponentProps<typeof Input> & FieldProps) {
  const ids = useFieldIds(name, hint, error);
  return (
    <div className="grid gap-1.5">
      <Label htmlFor={ids.id}>{label}</Label>
      <Input
        id={ids.id}
        name={name}
        aria-invalid={error ? true : undefined}
        aria-describedby={ids.describedBy}
        className="h-11 text-base"
        {...inputProps}
      />
      <FieldMessages hint={hint} error={error} hintId={ids.hintId} errorId={ids.errorId} />
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
  const ids = useFieldIds(name, hint, error);
  return (
    <div className="grid gap-1.5">
      <Label htmlFor={ids.id}>{label}</Label>
      <select
        id={ids.id}
        name={name}
        aria-invalid={error ? true : undefined}
        aria-describedby={ids.describedBy}
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
      <FieldMessages hint={hint} error={error} hintId={ids.hintId} errorId={ids.errorId} />
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
  const ids = useFieldIds(name, hint, error);
  return (
    <div className="grid gap-1.5">
      <Label htmlFor={ids.id}>{label}</Label>
      <textarea
        id={ids.id}
        name={name}
        aria-invalid={error ? true : undefined}
        aria-describedby={ids.describedBy}
        className={cn(
          "min-h-24 w-full rounded-lg border border-input bg-transparent px-2.5 py-2 text-base outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 aria-invalid:border-destructive aria-invalid:ring-3 aria-invalid:ring-destructive/20 dark:bg-input/30",
          className,
        )}
        {...textareaProps}
      />
      <FieldMessages hint={hint} error={error} hintId={ids.hintId} errorId={ids.errorId} />
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
