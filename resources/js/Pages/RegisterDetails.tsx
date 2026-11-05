import { StencilForm } from "@javaabu/stencil";
import { MobileCard } from "../components/MobileCard";
import { StatusAlert } from "../components/StatusAlert";
import { t } from "../lib/strings";
import type { RegisterDetailsPageProps } from "../types";

/** mobile-verification::RegisterDetails — the javaabu/stencil registration form */
export default function RegisterDetails({ form, mobile_number, strings, status }: RegisterDetailsPageProps) {
  const [before, after] = t(strings, "register_details_help").split(":number");

  return (
    <MobileCard title={t(strings, "register_details_title")} page="register-details">
      <StatusAlert status={status} />

      <p className="text-sm">
        {before}
        <bdi dir="ltr">{mobile_number.formatted_number}</bdi>
        {after}
      </p>

      <StencilForm schema={form} />
    </MobileCard>
  );
}
