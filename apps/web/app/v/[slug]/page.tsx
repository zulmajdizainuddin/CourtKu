import type { Metadata } from "next";
import Image from "next/image";
import { notFound } from "next/navigation";
import { Suspense } from "react";
import { ButtonLink } from "@/components/ui/button-link";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { facilityLabel } from "@/lib/facilities";
import { formatDay } from "@/lib/format";
import { DAY_NAMES, formatPeriod, WEEK_ORDER, weekFromPeriods } from "@/lib/opening-hours";
import { formatPhone } from "@/lib/phone";
import { directionsUrl, fullAddress, getPublicVenue, type PublicVenue } from "@/lib/public-venue";
import { sportLabel } from "@/lib/sports";
import { stateLabel } from "@/lib/states";

// A venue's public page (FR-06): what guests see without logging in. Built on the Next.js
// server, so it loads fast and search engines can read it. Only approved venues have one.

export async function generateMetadata({ params }: PageProps<"/v/[slug]">): Promise<Metadata> {
  const venue = await getPublicVenue((await params).slug);
  if (!venue) return { title: "Venue not found" };

  const where = [venue.city, stateLabel(venue.state)].filter(Boolean).join(", ");
  return {
    title: venue.name,
    description: venue.description ?? `Courts at ${venue.name}, ${where}. Opening hours, courts and directions on CourtKu.`,
  };
}

export default function VenuePublicPage({ params }: PageProps<"/v/[slug]">) {
  // The web address is only known when someone opens the page, so the venue streams in.
  return (
    <Suspense fallback={<p className="mx-auto w-full max-w-5xl px-4 py-10 text-sm text-muted-foreground">Loading...</p>}>
      <VenueContent params={params} />
    </Suspense>
  );
}

async function VenueContent({ params }: { params: PageProps<"/v/[slug]">["params"] }) {
  const venue = await getPublicVenue((await params).slug);
  if (!venue) notFound();

  const [cover, ...gallery] = venue.photos;
  const where = [venue.city, stateLabel(venue.state)].filter(Boolean).join(", ");

  return (
    <div className="mx-auto grid w-full max-w-5xl gap-6 px-4 py-8">
      {cover && (
        <Image
          src={cover.url}
          alt={`${venue.name}, cover photo`}
          width={cover.width}
          height={cover.height}
          unoptimized // Cloudinary already sends the right size and format.
          priority
          className="aspect-video w-full rounded-xl object-cover"
        />
      )}

      <header className="grid gap-1">
        <h1 className="text-3xl font-semibold tracking-tight">{venue.name}</h1>
        <p className="text-muted-foreground">{where}</p>
      </header>

      {/* Booking online works from M3; payment is at the counter until online payment in M4 (ADR-021). */}
      <div className="flex flex-wrap items-center gap-x-4 gap-y-3 rounded-lg border border-dashed px-4 py-3 text-sm">
        {venue.courts.length > 0 ? (
          <>
            <ButtonLink href={`/v/${venue.slug}/book`} className="h-11 px-6">
              Book a court
            </ButtonLink>
            <p>See free times and prices, book online, and pay at the counter when you arrive.</p>
          </>
        ) : (
          <p>
            Courts will be bookable here soon.
            {venue.phone && (
              <>
                {" "}
                For now, call the venue:{" "}
                <a href={`tel:${venue.phone}`} className="font-medium underline">
                  {formatPhone(venue.phone)}
                </a>
              </>
            )}
          </p>
        )}
      </div>

      <div className="grid gap-6 md:grid-cols-[1fr_20rem]">
        <div className="grid content-start gap-6">
          {venue.description && <p className="whitespace-pre-line">{venue.description}</p>}
          <Courts courts={venue.courts} />
          {venue.facilities.length > 0 && (
            <section aria-labelledby="facilities-heading" className="grid gap-2">
              <h2 id="facilities-heading" className="text-lg font-semibold">
                Facilities
              </h2>
              <ul className="flex flex-wrap gap-2">
                {venue.facilities.map((facility) => (
                  <li key={facility} className="rounded-full bg-muted px-3 py-1 text-sm">
                    {facilityLabel(facility)}
                  </li>
                ))}
              </ul>
            </section>
          )}
          {gallery.length > 0 && <Gallery venueName={venue.name} photos={gallery} />}
        </div>

        <aside className="grid content-start gap-4">
          <VisitCard venue={venue} />
          <HoursCard venue={venue} />
        </aside>
      </div>
    </div>
  );
}

function Courts({ courts }: { courts: PublicVenue["courts"] }) {
  return (
    <section aria-labelledby="courts-heading" className="grid gap-2">
      <h2 id="courts-heading" className="text-lg font-semibold">
        Courts
      </h2>
      {courts.length === 0 ? (
        <p className="text-sm text-muted-foreground">Courts will be listed here soon.</p>
      ) : (
        <ul className="grid gap-2 sm:grid-cols-2">
          {courts.map((court) => (
            <li key={court.id} className="rounded-lg px-3 py-2 ring-1 ring-foreground/10">
              <p className="font-medium">{court.name}</p>
              <p className="text-sm text-muted-foreground">
                {court.sports.map(sportLabel).join(", ")} · {court.is_indoor ? "Indoor" : "Outdoor"}
              </p>
              {court.base_price && <p className="text-sm">{court.base_price} an hour</p>}
            </li>
          ))}
        </ul>
      )}
    </section>
  );
}

function VisitCard({ venue }: { venue: PublicVenue }) {
  return (
    <Card size="sm">
      <CardHeader>
        <CardTitle>Visit</CardTitle>
      </CardHeader>
      <CardContent className="grid gap-3 text-sm">
        <p>{fullAddress(venue)}</p>
        <a
          href={directionsUrl(venue)}
          target="_blank"
          rel="noopener noreferrer"
          className="font-medium underline"
        >
          Get directions (opens Google Maps)
        </a>
        {venue.phone && (
          <a href={`tel:${venue.phone}`} className="underline">
            Call {formatPhone(venue.phone)}
          </a>
        )}
      </CardContent>
    </Card>
  );
}

function HoursCard({ venue }: { venue: PublicVenue }) {
  const week = weekFromPeriods(venue.opening_hours);
  return (
    <Card size="sm">
      <CardHeader>
        <CardTitle>Opening hours</CardTitle>
      </CardHeader>
      <CardContent className="grid gap-4 text-sm">
        {venue.opening_hours.length === 0 ? (
          <p className="text-muted-foreground">Opening hours will be listed here soon.</p>
        ) : (
          <dl className="grid grid-cols-[6rem_1fr] gap-x-2 gap-y-1">
            {WEEK_ORDER.map((weekday) => (
              <div key={weekday} className="contents">
                <dt className="font-medium">{DAY_NAMES[weekday]}</dt>
                <dd className={week[weekday].open ? undefined : "text-muted-foreground"}>
                  {week[weekday].open ? week[weekday].periods.map(formatPeriod).join(", ") : "Closed"}
                </dd>
              </div>
            ))}
          </dl>
        )}
        {venue.closures.length > 0 && (
          <div className="grid gap-1 border-t pt-3">
            <p className="font-medium">Closed on</p>
            <ul className="grid gap-1">
              {venue.closures.map((closure) => (
                <li key={closure.id}>
                  {closure.starts_on === closure.ends_on
                    ? formatDay(closure.starts_on)
                    : `${formatDay(closure.starts_on)} – ${formatDay(closure.ends_on)}`}
                  <span className="text-muted-foreground"> · {closure.reason}</span>
                </li>
              ))}
            </ul>
          </div>
        )}
      </CardContent>
    </Card>
  );
}

function Gallery({ venueName, photos }: { venueName: string; photos: PublicVenue["photos"] }) {
  return (
    <section aria-labelledby="photos-heading" className="grid gap-2">
      <h2 id="photos-heading" className="text-lg font-semibold">
        Photos
      </h2>
      <ul className="grid grid-cols-2 gap-2 sm:grid-cols-3">
        {photos.map((photo, index) => (
          <li key={photo.id}>
            <a href={photo.url} target="_blank" rel="noopener noreferrer">
              <Image
                src={photo.thumbnail_url}
                alt={`${venueName}, photo ${index + 2} (opens full size)`}
                width={400}
                height={300}
                unoptimized
                className="aspect-4/3 w-full rounded-lg object-cover"
              />
            </a>
          </li>
        ))}
      </ul>
    </section>
  );
}
