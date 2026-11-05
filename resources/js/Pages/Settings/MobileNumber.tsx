import { Alert, Card, Form, Submit } from "@javaabu/forms";
import { AccountLayout } from "@javaabu/auth";
import { NumberInput } from "../../components/NumberInput";
import { StatusAlert } from "../../components/StatusAlert";
import { t } from "../../lib/strings";
import type { MobileNumberSettingsPageProps } from "../../types";

/** mobile-verification::Settings/MobileNumber — verify or change the signed-in user's number */
export default function MobileNumber({ current_number, mobile, urls, account_links, strings, status }: MobileNumberSettingsPageProps) {
  const [before, after] = t(strings, "current_number").split(":number");

  return (
    <AccountLayout title={t(strings, "settings_mobile_title")} page="mobile-number" urls={urls} strings={strings} links={account_links}>
      <StatusAlert status={status} />

      <Card title={t(strings, "settings_mobile_title")}>
        <div className="flex flex-col gap-4" data-mobile-page="settings">
          <p className="text-sm">{t(strings, "settings_mobile_help")}</p>

          {current_number ? (
            <p data-current-number>
              {before}
              <bdi dir="ltr">{current_number.formatted_number}</bdi>
              {after}
            </p>
          ) : (
            <div data-no-number>
              <Alert type="warning">{t(strings, "no_number")}</Alert>
            </div>
          )}

          <Form action={urls["mobile-number.store"]} className="flex flex-col gap-4" data-mobile-number-form>
            <NumberInput mobile={mobile} strings={strings} label={t(strings, current_number ? "new_mobile_number" : "mobile_number")} />
            <Submit>{t(strings, "send_code")}</Submit>
          </Form>
        </div>
      </Card>
    </AccountLayout>
  );
}
