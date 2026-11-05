import { Form, Submit } from "@javaabu/forms";
import { MobileCard } from "../components/MobileCard";
import { NumberInput } from "../components/NumberInput";
import { StatusAlert } from "../components/StatusAlert";
import { t } from "../lib/strings";
import type { MobileRegisterPageProps } from "../types";

/** mobile-verification::Register */
export default function Register({ urls, strings, status, mobile }: MobileRegisterPageProps) {
  return (
    <MobileCard title={t(strings, "register_with_mobile")} page="register">
      <StatusAlert status={status} />

      <Form action={urls["register.mobile.store"]} className="flex flex-col gap-4" data-mobile-number-form>
        <NumberInput mobile={mobile} strings={strings} />
        <Submit className="w-full">{t(strings, "send_code")}</Submit>
      </Form>

      <p className="text-center text-sm">
        {t(strings, "have_account")}{" "}
        <a href={urls["login.mobile"]} className="underline">
          {t(strings, "log_in")}
        </a>
      </p>
    </MobileCard>
  );
}
