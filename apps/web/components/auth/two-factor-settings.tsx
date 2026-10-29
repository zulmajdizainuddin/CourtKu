"use client";

import Image from "next/image";
import { useState } from "react";
import { FormAlert, FormField } from "@/components/forms/form-field";
import { Button } from "@/components/ui/button";
import type { User } from "@/features/auth/hooks";
import {
  type TwoFactorSetup,
  useConfirmPassword,
  useConfirmTwoFactor,
  useDisableTwoFactor,
  useNewRecoveryCodes,
  useStartTwoFactor,
} from "@/features/auth/two-factor";
import { ApiError } from "@/lib/api";

function errorMessage(e: unknown): string {
  return e instanceof ApiError ? e.message : "Could not reach CourtKu. Check your connection and try again.";
}

/**
 * Turn two-factor login on or off, and get new recovery codes.
 * Every change needs the password typed in the last 3 hours, so someone at an
 * unlocked laptop can't quietly switch it off.
 */
export function TwoFactorSettings({ user }: { user: User }) {
  const start = useStartTwoFactor();
  const newCodes = useNewRecoveryCodes();
  const disable = useDisableTwoFactor();
  const [setup, setSetup] = useState<TwoFactorSetup | null>(null);
  const [codes, setCodes] = useState<string[] | null>(null);
  const [error, setError] = useState<string>();
  // The action to run again once the user has typed their password.
  const [waitingForPassword, setWaitingForPassword] = useState<(() => Promise<void>) | null>(null);

  const withPassword = async (action: () => Promise<void>) => {
    setError(undefined);
    try {
      await action();
    } catch (e) {
      if (e instanceof ApiError && e.code === "PASSWORD_CONFIRMATION_REQUIRED") {
        setWaitingForPassword(() => action);
      } else {
        setError(errorMessage(e));
      }
    }
  };

  const turnOn = () => withPassword(async () => setSetup(await start.mutateAsync()));
  const showNewCodes = () => withPassword(async () => setCodes(await newCodes.mutateAsync()));
  const turnOff = () => {
    const warning = user.two_factor_required
      ? "You run a venue or CourtKu itself, so your dashboard stays locked until you turn two-factor login on again. Turn it off?"
      : "Turn off two-factor login? Your account will only be protected by your password.";
    if (window.confirm(warning)) void withPassword(async () => void (await disable.mutateAsync()));
  };

  if (waitingForPassword) {
    return (
      <PasswordPrompt
        onCancel={() => setWaitingForPassword(null)}
        onConfirmed={() => {
          const action = waitingForPassword;
          setWaitingForPassword(null);
          void withPassword(action);
        }}
      />
    );
  }

  if (codes) {
    return <RecoveryCodes codes={codes} onDone={() => setCodes(null)} />;
  }

  if (setup) {
    return (
      <SetupSteps
        setup={setup}
        onCancel={() => setSetup(null)}
        onPasswordNeeded={(retry) => setWaitingForPassword(() => retry)}
        onDone={(recoveryCodes) => {
          setSetup(null);
          setCodes(recoveryCodes);
        }}
      />
    );
  }

  return (
    <div className="grid gap-4">
      <FormAlert message={error} />
      {user.two_factor_enabled ? (
        <>
          <p className="text-sm">
            Two-factor login is <span className="font-medium text-emerald-700 dark:text-emerald-400">on</span>. After
            your password, CourtKu asks for the code from your authenticator app.
          </p>
          <div className="flex flex-wrap gap-2">
            <Button variant="outline" disabled={newCodes.isPending} onClick={showNewCodes}>
              Get new recovery codes
            </Button>
            <Button variant="destructive" disabled={disable.isPending} onClick={turnOff}>
              Turn off
            </Button>
          </div>
        </>
      ) : (
        <>
          <p className="text-sm">
            Two-factor login is <span className="font-medium">off</span>. Turn it on so a stolen password alone
            can&apos;t get into your account. You&apos;ll need an authenticator app such as Google Authenticator or
            Microsoft Authenticator.
          </p>
          <Button className="justify-self-start" disabled={start.isPending} onClick={turnOn}>
            {start.isPending ? "Starting..." : "Turn on two-factor login"}
          </Button>
        </>
      )}
    </div>
  );
}

/** Step 1: scan the QR code. Step 2: type the first code to prove it worked. */
function SetupSteps({
  setup,
  onCancel,
  onPasswordNeeded,
  onDone,
}: {
  setup: TwoFactorSetup;
  onCancel: () => void;
  onPasswordNeeded: (retry: () => Promise<void>) => void;
  onDone: (recoveryCodes: string[]) => void;
}) {
  const confirm = useConfirmTwoFactor();
  const [code, setCode] = useState("");
  const [error, setError] = useState<string>();

  const submit = async () => {
    setError(undefined);
    try {
      onDone(await confirm.mutateAsync(code.replace(/\s/g, "")));
    } catch (e) {
      if (e instanceof ApiError && e.code === "PASSWORD_CONFIRMATION_REQUIRED") {
        onPasswordNeeded(submit);
      } else if (e instanceof ApiError && e.code === "VALIDATION_FAILED") {
        setError("That code didn't work. Check the app shows CourtKu and type the newest code.");
      } else {
        setError(errorMessage(e));
      }
    }
  };

  return (
    <form
      className="grid gap-4"
      noValidate
      onSubmit={(e) => {
        e.preventDefault();
        void submit();
      }}
    >
      <ol className="grid list-decimal gap-1 pl-5 text-sm">
        <li>Open your authenticator app and add a new account.</li>
        <li>Scan this QR code with it.</li>
        <li>Type the 6-digit code the app shows for CourtKu.</li>
      </ol>
      {/* The QR code comes from our own API as an SVG; shown as an image, never as raw HTML. */}
      <Image
        src={`data:image/svg+xml;charset=utf-8,${encodeURIComponent(setup.svg)}`}
        alt="QR code to scan with your authenticator app"
        width={192}
        height={192}
        unoptimized
        className="rounded-lg bg-white p-2"
      />
      <p className="text-sm text-muted-foreground">
        Can&apos;t scan it? Type this key into the app instead:{" "}
        <code className="font-mono text-foreground break-all">{setup.secretKey.match(/.{1,4}/g)?.join(" ")}</code>
      </p>
      <FormField
        label="6-digit code"
        name="code"
        inputMode="numeric"
        autoComplete="one-time-code"
        maxLength={7}
        value={code}
        onChange={(e) => setCode(e.target.value)}
        error={error}
      />
      <div className="flex flex-wrap gap-2">
        <Button type="submit" disabled={confirm.isPending || code.trim() === ""}>
          {confirm.isPending ? "Checking..." : "Turn on"}
        </Button>
        <Button type="button" variant="outline" onClick={onCancel}>
          Cancel
        </Button>
      </div>
    </form>
  );
}

/** Shown once: codes that log you in if you lose your phone. Each works one time. */
function RecoveryCodes({ codes, onDone }: { codes: string[]; onDone: () => void }) {
  const [copied, setCopied] = useState(false);

  const copy = async () => {
    try {
      await navigator.clipboard.writeText(codes.join("\n"));
      setCopied(true);
    } catch {
      setCopied(false);
    }
  };

  return (
    <div className="grid gap-4">
      <FormAlert tone="success" message="Two-factor login is on." />
      <p className="text-sm">
        Save these recovery codes somewhere safe, like a password manager. If you lose your phone, each code lets
        you log in once. <span className="font-medium">You won&apos;t see them again.</span>
      </p>
      <ul className="grid grid-cols-1 gap-1 rounded-lg bg-muted p-3 font-mono text-sm sm:grid-cols-2">
        {codes.map((c) => (
          <li key={c}>{c}</li>
        ))}
      </ul>
      <div className="flex flex-wrap gap-2">
        <Button variant="outline" onClick={copy}>
          {copied ? "Copied" : "Copy codes"}
        </Button>
        <Button onClick={onDone}>I&apos;ve saved them</Button>
      </div>
    </div>
  );
}

/** "Enter your password again" before a change to two-factor login. */
function PasswordPrompt({ onConfirmed, onCancel }: { onConfirmed: () => void; onCancel: () => void }) {
  const confirmPassword = useConfirmPassword();
  const [password, setPassword] = useState("");
  const [error, setError] = useState<string>();

  const submit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(undefined);
    try {
      await confirmPassword.mutateAsync(password);
      onConfirmed();
    } catch (err) {
      setError(
        err instanceof ApiError && err.code === "VALIDATION_FAILED"
          ? "That password is not right."
          : errorMessage(err),
      );
    }
  };

  return (
    <form className="grid gap-4" onSubmit={submit} noValidate>
      <p className="text-sm">For your safety, enter your password again to continue.</p>
      <FormField
        label="Password"
        name="password"
        type="password"
        autoComplete="current-password"
        autoFocus
        value={password}
        onChange={(e) => setPassword(e.target.value)}
        error={error}
      />
      <div className="flex flex-wrap gap-2">
        <Button type="submit" disabled={confirmPassword.isPending || password === ""}>
          {confirmPassword.isPending ? "Checking..." : "Continue"}
        </Button>
        <Button type="button" variant="outline" onClick={onCancel}>
          Cancel
        </Button>
      </div>
    </form>
  );
}
