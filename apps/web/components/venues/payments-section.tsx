"use client";

import { useSearchParams } from "next/navigation";
import { useState } from "react";
import { FormAlert, FormField, SelectField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import type { ManageVenue, PaymentMode } from "@/features/venues/hooks";
import {
  type AttentionPayment,
  type PaymentAccount,
  useDisconnectPaymentAccount,
  useGoToStripe,
  usePaymentAccount,
  usePaymentsNeedingAttention,
  useRecordRefund,
  useUpdatePaymentSettings,
} from "@/features/venues/payments";
import { ApiError } from "@/lib/api";
import { formatDate, formatDayTime } from "@/lib/format";
import { formatRinggit, parseRinggit, senToInput } from "@/lib/money";
import { formatPhone } from "@/lib/phone";

const NETWORK_ERROR = "Could not reach CourtKu. Check your connection and try again.";

/** The API's first field message, or its message, for a form-level alert. */
function errorText(err: unknown): string {
  return err instanceof ApiError ? (Object.values(err.fields).flat()[0] ?? err.message) : NETWORK_ERROR;
}

/**
 * The venue's own Stripe account, connected through Stripe Connect (ADR-024). The owner gives their details
 * to Stripe on Stripe's own pages; CourtKu never asks for a key.
 */
export function StripeAccountSection({ venueId }: { venueId: string }) {
  // Stripe sends the owner back here: ?stripe=return when done (or stopped), ?stripe=expired when the link got old.
  const back = useSearchParams().get("stripe");
  const linkExpired = back === "expired";
  const account = usePaymentAccount(venueId, { justBackFromStripe: back === "return" });

  if (account.isPending) return <p className="text-sm text-muted-foreground">Loading...</p>;
  if (account.isError)
    return <p className="text-sm text-destructive">Could not load the payment account. Please refresh the page.</p>;

  return (
    <div className="grid gap-4">
      {linkExpired && !account.data?.ready && (
        <FormAlert message="That Stripe link expired (they only work for a few minutes). Click the button below for a new one." />
      )}
      {account.data ? (
        <ConnectedAccount venueId={venueId} account={account.data} checking={account.waitingForStripe} />
      ) : (
        <NotConnected venueId={venueId} />
      )}
    </div>
  );
}

function NotConnected({ venueId }: { venueId: string }) {
  return (
    <div className="grid gap-3">
      <p className="text-sm text-muted-foreground">
        Stripe asks for your details on its own pages: who you are and the bank account for your payouts. When
        you&apos;re done, Stripe sends you back here.
      </p>
      <GoToStripeButton venueId={venueId} label="Connect with Stripe" />
    </div>
  );
}

/** checking: the owner just came back from Stripe, which may still be switching payments on. */
function ConnectedAccount({
  venueId,
  account,
  checking,
}: {
  venueId: string;
  account: PaymentAccount;
  checking: boolean;
}) {
  const disconnect = useDisconnectPaymentAccount(venueId);

  const onDisconnect = () => {
    if (
      window.confirm(
        "Disconnect Stripe? Players will pay at the counter until you connect again. Your Stripe account itself stays yours.",
      )
    ) {
      disconnect.mutate();
    }
  };

  return (
    <div className="grid gap-4">
      {account.ready ? (
        <FormAlert
          tone="success"
          message="Connected. Players can pay online, and the money goes to your Stripe account."
        />
      ) : checking ? (
        <FormAlert
          tone="success"
          message="Thanks! Stripe is finishing setting up your account. This page updates by itself in a moment."
        />
      ) : (
        <FormAlert message="Almost there: Stripe needs a few more details before players can pay online." />
      )}
      <dl className="grid gap-2 text-sm sm:grid-cols-[10rem_1fr]">
        <dt className="text-muted-foreground">Stripe account</dt>
        <dd>
          {account.account_name ?? "Your venue"}
          <span className="ml-2 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-950 dark:bg-amber-950 dark:text-amber-100">
            {account.is_live ? "Live" : "Test mode"}
          </span>
        </dd>
        <dt className="text-muted-foreground">Account id</dt>
        <dd className="font-mono break-all">{account.account_id}</dd>
        <dt className="text-muted-foreground">Connected</dt>
        <dd>{formatDate(account.connected_at)}</dd>
      </dl>
      <div className="flex flex-wrap gap-2">
        {account.ready ? (
          <a
            href="https://dashboard.stripe.com/"
            target="_blank"
            rel="noreferrer"
            className="inline-flex h-11 items-center rounded-lg px-4 text-sm font-medium ring-1 ring-foreground/15 hover:bg-muted"
          >
            Open your Stripe dashboard
          </a>
        ) : (
          <GoToStripeButton venueId={venueId} label="Continue on Stripe" />
        )}
        <Button
          variant="ghost"
          className="h-11 px-6 text-destructive"
          disabled={disconnect.isPending}
          onClick={onDisconnect}
        >
          {disconnect.isPending ? "Disconnecting..." : "Disconnect"}
        </Button>
      </div>
      <FormAlert message={disconnect.error ? errorText(disconnect.error) : undefined} />
    </div>
  );
}

/** Gets a one-time link from CourtKu and opens Stripe's own pages. */
function GoToStripeButton({ venueId, label }: { venueId: string; label: string }) {
  const goToStripe = useGoToStripe(venueId);
  return (
    <div className="grid gap-2">
      <Button
        className="h-11 justify-self-start px-6"
        disabled={goToStripe.isPending || goToStripe.isSuccess}
        onClick={() => goToStripe.mutate()}
      >
        {goToStripe.isPending || goToStripe.isSuccess ? "Opening Stripe..." : label}
      </Button>
      <FormAlert message={goToStripe.error ? errorText(goToStripe.error) : undefined} />
    </div>
  );
}

const MODE_OPTIONS: { value: PaymentMode; label: string }[] = [
  { value: "counter", label: "At the counter only" },
  { value: "online", label: "Online only" },
  { value: "both", label: "Online or at the counter (player chooses)" },
];

const HOLD_OPTIONS = [10, 15, 20, 30].map((m) => ({
  value: String(m),
  label: `${m} minutes`,
}));

/** How players pay (FR-17) and how long they have to pay online (FR-16). */
export function PaymentSettingsForm({ venue }: { venue: ManageVenue }) {
  const save = useUpdatePaymentSettings(String(venue.id));
  const [mode, setMode] = useState<PaymentMode>(venue.payment_mode);
  const [hold, setHold] = useState(venue.hold_minutes);
  const [saved, setSaved] = useState(false);
  const [error, setError] = useState<string>();

  const onSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(undefined);
    setSaved(false);
    try {
      await save.mutateAsync({ payment_mode: mode, hold_minutes: hold });
      setSaved(true);
    } catch (err) {
      setError(errorText(err));
    }
  };

  return (
    <form onSubmit={onSubmit} className="grid gap-4" noValidate>
      <SelectField
        label="Players pay"
        name="payment_mode"
        // Online goes to your own Stripe account, so it needs one first (ADR-009).
        options={venue.payment_connected ? MODE_OPTIONS : MODE_OPTIONS.filter((o) => o.value === "counter")}
        hint={
          venue.payment_connected
            ? "Paid at the counter: the booking is confirmed straight away. Paid online: the time is held while they pay."
            : "Connect your Stripe account above to let players pay online."
        }
        value={mode}
        onChange={(e) => {
          setSaved(false);
          setMode(e.target.value as PaymentMode);
        }}
      />
      <SelectField
        label="Time to pay online"
        name="hold_minutes"
        hint="The court is kept for the player while they pay. If they don't pay in time, it goes back to other players. FPX bank login and OTP can take a while, so 15 minutes is a good start."
        options={HOLD_OPTIONS}
        value={String(hold)}
        onChange={(e) => {
          setSaved(false);
          setHold(Number(e.target.value));
        }}
      />
      <FormAlert message={error} />
      {saved && <FormAlert tone="success" message="Payment settings saved." />}
      <Button type="submit" size="lg" className="h-11 justify-self-start px-6" disabled={save.isPending}>
        {save.isPending ? "Saving..." : "Save payment settings"}
      </Button>
    </form>
  );
}

/**
 * Online payments whose money came in but couldn't confirm the booking (plan section 9): a wrong amount,
 * paid after the time ran out and was taken, paid after a cancel, or paid twice. The owner refunds them
 * in their Stripe dashboard (FR-25).
 */
export function PaymentsNeedingAttention({ venueId }: { venueId: string }) {
  const payments = usePaymentsNeedingAttention(venueId);

  if (payments.isPending) return <p className="text-sm text-muted-foreground">Loading...</p>;
  if (payments.isError) return <p className="text-sm text-destructive">Could not load the payments. Please refresh the page.</p>;
  if (payments.data.length === 0) {
    return <p className="text-sm text-muted-foreground">Nothing needs your attention: no online payments to refund.</p>;
  }

  return (
    <ul className="grid gap-3">
      {payments.data.map((payment) => (
        <AttentionItem key={payment.id} venueId={venueId} payment={payment} />
      ))}
    </ul>
  );
}

function AttentionItem({ venueId, payment }: { venueId: string; payment: AttentionPayment }) {
  const { booking } = payment;
  const customer = booking.customer;
  return (
    <li className="grid gap-1 rounded-lg px-3 py-2 text-sm ring-1 ring-amber-500/40">
      <p className="font-medium">
        {payment.paid_amount ?? "Payment"} for {booking.code} · {booking.court}, {formatDayTime(booking.starts_at)}
      </p>
      <p>{payment.problem_text}</p>
      <p className="text-muted-foreground">
        {customer.name ?? "Player"}
        {customer.email && ` · ${customer.email}`}
        {customer.phone && ` · ${formatPhone(customer.phone)}`}
        {payment.paid_at && ` · paid ${formatDayTime(payment.paid_at)}`}
      </p>
      <p className="text-muted-foreground">
        Refund it in your Stripe dashboard (search for <span className="font-mono break-all">{payment.checkout_id}</span>),
        then record it here.
        {payment.refunded_sen > 0 && ` Already refunded: ${payment.refunded}.`}
      </p>
      <RecordRefundForm venueId={venueId} payment={payment} />
    </li>
  );
}

/** FR-25: the owner records a refund they made in Stripe. Part refunds are fine; never more than what is left. */
function RecordRefundForm({ venueId, payment }: { venueId: string; payment: AttentionPayment }) {
  const record = useRecordRefund(venueId);
  const [amount, setAmount] = useState(senToInput(payment.refundable_sen));
  const [reason, setReason] = useState(payment.problem_text.replace(/\.$/, ""));
  const [error, setError] = useState<string>();

  const onSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(undefined);
    const sen = parseRinggit(amount);
    if (sen === null || sen <= 0) return setError("Enter the amount you refunded, e.g. 20.00.");
    if (sen > payment.refundable_sen) return setError(`You can record at most ${formatRinggit(payment.refundable_sen)}.`);
    try {
      await record.mutateAsync({ paymentId: payment.id, amount_sen: sen, reason });
    } catch (err) {
      setError(errorText(err));
    }
  };

  return (
    <form onSubmit={onSubmit} className="mt-2 grid gap-2 sm:grid-cols-[8rem_1fr_auto] sm:items-end" noValidate>
      <FormField label="Refunded (RM)" name={`refund-amount-${payment.id}`} inputMode="decimal" value={amount} onChange={(e) => setAmount(e.target.value)} />
      <FormField label="Reason" name={`refund-reason-${payment.id}`} maxLength={200} value={reason} onChange={(e) => setReason(e.target.value)} />
      <Button type="submit" variant="outline" className="h-11 px-4" disabled={record.isPending}>
        {record.isPending ? "Saving..." : "Record refund"}
      </Button>
      <div className="sm:col-span-3">
        <FormAlert message={error} />
      </div>
    </form>
  );
}
