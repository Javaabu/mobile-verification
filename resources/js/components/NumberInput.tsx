import { Select, Tel } from "@javaabu/forms";
import { t } from "../lib/strings";
import type { MobileSettings, MobileStrings } from "../types";

export interface NumberInputProps {
  mobile: MobileSettings;
  strings: MobileStrings;
  /** The label of the number; "Mobile number" by default */
  label?: string;
}

/** The country code (a select when there's a choice) and the number, as the Blade partial */
export function NumberInput({ mobile, strings, label }: NumberInputProps) {
  const codes = Object.entries(mobile.country_codes);
  const numberLabel = label ?? t(strings, "mobile_number");
  const help = t(strings, "mobile_number_help");

  if (codes.length > 1) {
    return (
      <div className="grid grid-cols-3 gap-2">
        <Select
          name="country_code"
          label={t(strings, "country_code")}
          options={mobile.country_codes}
          default={mobile.default_country_code}
          model={false}
          required
        />
        <div className="col-span-2">
          <Tel name="number" label={numberLabel} model={false} required autoFocus autoComplete="tel-national" inputMode="tel" dir="ltr" help={help} />
        </div>
      </div>
    );
  }

  const [code, prefix] = codes[0] ?? [mobile.default_country_code, `${mobile.number_prefix}${mobile.default_country_code}`];

  return (
    <>
      <input type="hidden" name="country_code" value={code} />
      <Tel
        name="number"
        label={numberLabel}
        model={false}
        required
        autoFocus
        autoComplete="tel-national"
        inputMode="tel"
        dir="ltr"
        prepend={<span dir="ltr">{prefix}</span>}
        help={help}
      />
    </>
  );
}
