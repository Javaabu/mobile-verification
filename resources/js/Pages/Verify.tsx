import { Button, Form, Submit, Text } from "@javaabu/forms";
import { AccountLayout } from "@javaabu/auth";
import type { ReactNode } from "react";
import { MobileCard } from "../components/MobileCard";
import { StatusAlert } from "../components/StatusAlert";
import { t } from "../lib/strings";
import type { VerifyPageProps } from "../types";

/** mobile-verification::Verify — the code page of every flow */
export default function Verify({ purpose, title, mobile_number, verify_url, resend_url, change_url, mobile, urls, account_links, strings, status }: VerifyPageProps) {
  const [before, after] = t(strings, "code_help", {
    length: mobile.code_length,
    minutes: Math.max(1, Math.ceil(mobile_number.expires_in / 60)),
  }).split(":number");

  const content: ReactNode = (
    <div className="flex flex-col gap-4" data-purpose={purpose}>
      <StatusAlert status={status} />

      <p className="text-sm">
        {before}
        <bdi dir="ltr">{mobile_number.formatted_number}</bdi>
        {after}
      </p>

      <Form action={verify_url} className="flex flex-col gap-4" data-verification-code-form>
        <Text
          name="verification_code"
          label={t(strings, "code")}
          model={false}
          required
          autoFocus
          autoComplete="one-time-code"
          inputMode="numeric"
          pattern="[0-9]*"
          maxLength={mobile.code_length}
          dir="ltr"
          className="text-center tracking-widest"
        />
        <Submit className="w-full">{t(strings, "verify")}</Submit>
      </Form>

      <div className="flex flex-wrap items-center justify-between gap-2">
        <Form action={resend_url} data-resend-form>
          <Button type="submit" color="secondary">
            {t(strings, "resend_code")}
          </Button>
        </Form>

        <a href={change_url} className="text-sm underline" data-change-number>
          {t(strings, "change_number")}
        </a>
      </div>

      {mobile_number.resend_in > 0 ? (
        <p className="text-sm" data-resend-in>
          {t(strings, "resend_in", { seconds: mobile_number.resend_in })}
        </p>
      ) : null}
    </div>
  );

  if (purpose === "update") {
    return (
      <AccountLayout title={title} page="mobile-number-verify" urls={urls} strings={strings} links={account_links}>
        {content}
      </AccountLayout>
    );
  }

  return (
    <MobileCard title={title} page="verify">
      {content}
    </MobileCard>
  );
}
