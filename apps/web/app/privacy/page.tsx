import type { Metadata } from "next";
import Link from "next/link";
import { CONTACT_EMAIL, LegalPage } from "@/components/legal/legal-page";

export const metadata: Metadata = {
  title: "Privacy Notice",
  description: "What personal data CourtKu collects, why, who sees it and how long it is kept.",
};

// FR-40 and NFR-07: the Privacy Notice under Malaysia's Personal Data Protection Act 2010 (ADR-032).
export default function PrivacyPage() {
  return (
    <LegalPage title="Privacy Notice" updated="November 2026">
      <p>
        This notice explains what personal data CourtKu collects, why, who sees it and how long it is kept, as the
        Personal Data Protection Act 2010 (PDPA) requires. It applies to courtku.vercel.app. See also the{" "}
        <Link href="/terms">Terms of Use</Link>.
      </p>

      <h2 id="collect">1. What we collect</h2>
      <ul>
        <li>
          <strong>Your account:</strong> your name, email address and password (stored only as a one-way hash, so nobody
          can read it).
        </li>
        <li>
          <strong>Your bookings:</strong> the venue, court, sport, time, price, booking code, and whether it is paid,
          cancelled or checked in.
        </li>
        <li>
          <strong>Counter and phone bookings:</strong> when you book through venue staff, they record your name and phone
          number for that booking.
        </li>
        <li>
          <strong>Payments:</strong> Stripe takes your card or bank details on its own page. CourtKu only learns the
          amount and whether it was paid or refunded.
        </li>
        <li>
          <strong>Venue owners and staff:</strong> which venues you work at and in what role, and the venue details you
          enter.
        </li>
        <li>
          <strong>Technical data:</strong> your IP address, which our servers use briefly to block abuse, and the cookies
          described below.
        </li>
      </ul>
      <p>We collect only what booking needs. We don&apos;t ask for your IC number, address or date of birth.</p>

      <h2 id="why">2. Why we use it</h2>
      <ul>
        <li>to make, change and cancel your bookings, and to show them to the venue you booked;</li>
        <li>to send you emails about your bookings: confirmed, a reminder before the game, cancelled, or a problem with a payment;</li>
        <li>to keep your account safe: email verification, password reset and two-factor login;</li>
        <li>to keep the site working and stop abuse.</li>
      </ul>
      <p>We don&apos;t sell your data, show you adverts, or send marketing emails.</p>

      <h2 id="who">3. Who sees it</h2>
      <ul>
        <li>
          <strong>The venue you book:</strong> its owner and staff see your name, email, phone number (if any) and the
          booking. For this data the venue is the data user (controller), and CourtKu processes it for the venue.
        </li>
        <li>
          <strong>The CourtKu admin</strong>, only to run the site and help when something goes wrong.
        </li>
        <li>
          <strong>Service providers that run CourtKu for us:</strong> Vercel (the website), Render (the server), Aiven
          (the database), Brevo (emails), Stripe (payments), Cloudinary (venue photos), Sentry (error reports, sent
          without your name, email, cookies or what you typed) and GitHub (locked nightly backups). They may use your
          data only to provide their service.
        </li>
        <li>Authorities, only when Malaysian law requires it.</li>
      </ul>

      <h2 id="where">4. Where it is kept</h2>
      <p>
        CourtKu&apos;s website and server run in Singapore, and some providers above store data in other countries, such
        as the United States or the European Union. The PDPA allows this where the other country has similar laws or the
        data is protected to the same standard; we use providers that commit to that.
      </p>

      <h2 id="keep">5. How long we keep it</h2>
      <ul>
        <li>Your account: until you delete it.</li>
        <li>
          When you delete your account, your name, email and phone number are removed straight away. Your past bookings
          stay in the venue&apos;s records, shown as &quot;Deleted user&quot;, so the venue&apos;s history and totals
          still add up.
        </li>
        <li>Locked backups are kept for 30 days, then deleted, so a deleted account is gone from them within 30 days.</li>
      </ul>

      <h2 id="safe">6. How we protect it</h2>
      <p>
        Every page uses HTTPS. Passwords are hashed. Venue owners and the admin must use two-factor login. Each venue can
        see only its own bookings, and backups are locked with a password before they leave the server. If a data breach
        ever puts your data at risk, we will tell you and the Personal Data Protection Commissioner, as the law requires.
      </p>

      <h2 id="cookies">7. Cookies</h2>
      <p>
        CourtKu uses only the cookies it needs: one that keeps you logged in, and one that protects your forms from
        forged requests. There are no advertising or tracking cookies. Visit counts come from Vercel Web Analytics,
        which works without cookies.
      </p>

      <h2 id="rights">8. Your rights</h2>
      <ul>
        <li>
          <strong>See and correct your data:</strong> your details are on <Link href="/me">My account</Link>; email us
          to change them or to get a copy of everything we hold about you.
        </li>
        <li>
          <strong>Delete your account:</strong> on <Link href="/me/security">My account → Security</Link>, once you have
          no upcoming bookings.
        </li>
        <li>
          <strong>Complain:</strong> to us first, or to the Personal Data Protection Commissioner of Malaysia.
        </li>
      </ul>
      <p>
        We answer requests within 21 days. If you don&apos;t want to give the data above, you can still look at venues,
        but you can&apos;t book online.
      </p>

      <h2 id="contact">9. Contact</h2>
      <p>
        Questions about your data: <a href={`mailto:${CONTACT_EMAIL}`}>{CONTACT_EMAIL}</a>.
      </p>
    </LegalPage>
  );
}
