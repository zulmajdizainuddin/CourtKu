import { AuthCard } from "@/components/auth/auth-card";
import { ButtonLink } from "@/components/ui/button-link";

/** No live venue at this address: it doesn't exist, or it isn't approved yet or any more. */
export default function VenueNotFound() {
  return (
    <AuthCard
      title="Venue not found"
      description="There's no venue at this address. Check the link, or ask the venue for their CourtKu page."
    >
      <ButtonLink href="/" className="h-11 w-full">
        Go to the home page
      </ButtonLink>
    </AuthCard>
  );
}
