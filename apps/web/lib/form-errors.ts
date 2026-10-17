import type { FieldValues, Path, UseFormSetError } from "react-hook-form";
import { ApiError } from "@/lib/api";

/**
 * Shows the API's errors in the form.
 * Field errors (422) go under the matching input; anything else is returned
 * as one message for the top of the form.
 */
export function applyApiErrors<T extends FieldValues>(
  error: unknown,
  setError: UseFormSetError<T>,
): string | undefined {
  if (!(error instanceof ApiError)) return "Could not reach CourtKu. Check your connection and try again.";

  const fields = Object.entries(error.fields);
  if (error.code === "VALIDATION_FAILED" && fields.length > 0) {
    for (const [field, messages] of fields) {
      setError(field as Path<T>, { message: messages[0] });
    }
    return undefined;
  }
  return error.message;
}
