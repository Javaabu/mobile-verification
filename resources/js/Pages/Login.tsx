import { Checkbox, Form, LinkButton, Submit } from "@javaabu/forms";
import { MobileCard } from "../components/MobileCard";
import { NumberInput } from "../components/NumberInput";
import { StatusAlert } from "../components/StatusAlert";
import { t } from "../lib/strings";
import type { MobileLoginPageProps } from "../types";

/** mobile-verification::Login */
export default function Login({ urls, strings, status, mobile, can_register }: MobileLoginPageProps) {
  return (
    <MobileCard title={t(strings, "log_in_with_mobile")} page="login">
      <StatusAlert status={status} />

      <Form action={urls["login.mobile.store"]} className="flex flex-col gap-4" data-mobile-number-form>
        <NumberInput mobile={mobile} strings={strings} />
        <Checkbox name="remember" label={t(strings, "remember_me")} value="1" model={false} />
        <Submit className="w-full">{t(strings, "send_code")}</Submit>
      </Form>

      {urls.login ? (
        <LinkButton url={urls.login} color="secondary" className="w-full" data-password-login>
          {t(strings, "back_to_login")}
        </LinkButton>
      ) : null}

      {can_register && urls["register.mobile"] ? (
        <p className="text-center text-sm">
          {t(strings, "no_account")}{" "}
          <a href={urls["register.mobile"]} className="underline">
            {t(strings, "sign_up")}
          </a>
        </p>
      ) : null}
    </MobileCard>
  );
}
