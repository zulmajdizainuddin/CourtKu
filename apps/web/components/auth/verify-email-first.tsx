import { ButtonLink } from "@/components/ui/button-link";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";

/** Shown instead of the dashboard until the user has verified their email (the API requires it). */
export function VerifyEmailFirst() {
  return (
    <div className="mx-auto w-full max-w-md px-4 py-10">
      <Card>
        <CardHeader>
          <CardTitle className="text-xl">Verify your email first</CardTitle>
          <CardDescription>
            To list or manage a venue, confirm your email address with the link we sent you.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <ButtonLink href="/verify-email">
            Get a new link
          </ButtonLink>
        </CardContent>
      </Card>
    </div>
  );
}
