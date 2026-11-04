import type { Metadata } from "next";
import Link from "next/link";
import { CONTACT_EMAIL, LegalPage } from "@/components/legal/legal-page";

export const metadata: Metadata = {
  title: "Terms of Use",
  description: "The rules for booking courts and running a venue on CourtKu.",
};

// FR-40: the Terms of Use (ADR-032). Plain English, written for players and venue owners alike.
export default function TermsPage() {
  return (
    <LegalPage title="Terms of Use" updated="November 2026">
      <p>
        These terms apply when you use CourtKu at courtku.vercel.app to book courts or to run a venue. By creating an
        account or making a booking, you agree to them. Please also read the{" "}
        <Link href="/privacy">Privacy Notice</Link>, which explains what happens to your personal data.
      </p>

      <h2 id="about">1. What CourtKu is</h2>
      <p>
        CourtKu is an online booking system for badminton, futsal and pickleball venues in Malaysia. Players see free
        times and book; venue owners and their staff manage their courts, prices and bookings. CourtKu is run by an
        independent developer as a portfolio project.
      </p>

      <h2 id="demo">2. This is a demonstration site</h2>
      <ul>
        <li>
          Online payments run in Stripe&apos;s test mode. No real money is charged, and no real card should be used.
        </li>
        <li>
          Venues that say so on their page are made up, to show how CourtKu works. Bookings at them are not real and
          are cleared regularly.
        </li>
        <li>Don&apos;t rely on a booking here for a real game unless the venue has confirmed it with you directly.</li>
      </ul>

      <h2 id="account">3. Your account</h2>
      <ul>
        <li>Give your real name and an email address you can read. One account per person.</li>
        <li>Keep your password to yourself. You are responsible for what is done with your account.</li>
        <li>Venue owners and the CourtKu admin must use two-factor login.</li>
        <li>
          You can delete your account at any time from{" "}
          <Link href="/me/security">My account → Security</Link>, once you have no upcoming bookings and own no venue.
        </li>
        <li>We may suspend an account that breaks these terms.</li>
      </ul>

      <h2 id="bookings">4. Bookings, payments and refunds</h2>
      <ul>
        <li>
          When you book, your agreement is with the venue. CourtKu provides the booking system; the venue provides the
          court and is responsible for it.
        </li>
        <li>All times are Malaysia time. The price shown before you book is the price you pay.</li>
        <li>
          Paying online: the court is held for you for the venue&apos;s hold time (shown before you pay) while you pay
          on Stripe&apos;s page. If you don&apos;t finish in time, the hold ends and the court is offered to others.
        </li>
        <li>
          Online payments go straight to the venue&apos;s own Stripe account. CourtKu never sees your card number and
          never holds your money.
        </li>
        <li>Paying at the venue: you pay at the counter when you arrive.</li>
        <li>
          Each venue&apos;s cancellation and refund policy is shown on its page and next to the Pay button, before you
          pay. You can cancel online until the venue&apos;s cut-off time; after that, contact the venue.
        </li>
        <li>
          The venue makes refunds, through Stripe, to the card or bank you paid with. Refunds take a few days to arrive.
        </li>
        <li>
          If your payment reaches the venue but your booking could not be confirmed (for example, the hold had ended and
          someone else took the time), the venue refunds you in full.
        </li>
      </ul>

      <h2 id="venues">5. For venue owners and staff</h2>
      <ul>
        <li>Keep your venue&apos;s details, opening hours, prices and photos accurate. Use only photos you may use.</li>
        <li>Honour the bookings made through CourtKu, and follow the refund policy players saw before they paid.</li>
        <li>Make the refunds you owe promptly, and record them in CourtKu.</li>
        <li>
          Use players&apos; names, phone numbers and emails only to manage their bookings. For this data your venue is
          the data user (controller) under the Personal Data Protection Act 2010, and CourtKu processes it for you.
        </li>
        <li>CourtKu approves venues before they appear, and may suspend a venue that misleads players.</li>
      </ul>

      <h2 id="use">6. Fair use</h2>
      <p>Don&apos;t:</p>
      <ul>
        <li>make bookings you don&apos;t mean to use, to block courts from others;</li>
        <li>give false details, or use someone else&apos;s account or payment card;</li>
        <li>try to break, overload or copy the site in bulk, or get at data that isn&apos;t yours.</li>
      </ul>

      <h2 id="liability">7. Our responsibility</h2>
      <p>
        We work to keep CourtKu running and correct, but it is provided as it is, and it may sometimes be unavailable.
        CourtKu is not responsible for the venues themselves: their courts, facilities, safety or what happens during a
        game. As far as Malaysian law allows, CourtKu is not liable for indirect losses from using the site. Nothing in
        these terms takes away your rights under the Consumer Protection Act 1999.
      </p>

      <h2 id="changes">8. Changes</h2>
      <p>
        We may update these terms. The date at the top shows the latest version; if a change matters to you, we will
        say so on the site or by email. Using CourtKu after a change means you accept it.
      </p>

      <h2 id="law">9. Law</h2>
      <p>These terms are governed by the laws of Malaysia.</p>

      <h2 id="contact">10. Contact</h2>
      <p>
        Questions about these terms: <a href={`mailto:${CONTACT_EMAIL}`}>{CONTACT_EMAIL}</a>. Questions about a booking:
        contact the venue, with your booking code.
      </p>
    </LegalPage>
  );
}
