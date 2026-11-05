import type { AuthPageProps } from "@javaabu/auth";
import type { StencilSchema } from "@javaabu/stencil";

/** What the pages know about numbers (Javaabu\MobileVerification\Views\ViewRenderer::sharedData) */
export interface MobileSettings {
  /** Country code → shown prefix: `{ "960": "+960" }` */
  country_codes: Record<string, string>;
  default_country_code: string;
  number_prefix: string;
  code_length: number;
}

/** The translated strings, keyed as in Javaabu\MobileVerification\Support\Strings (and javaabu/auth's) */
export type MobileStrings = Record<string, string>;

/** Props every page gets from the server: javaabu/auth's, plus the number settings */
export interface MobilePageProps extends AuthPageProps {
  mobile: MobileSettings;
  strings: MobileStrings;
}

export interface MobileLoginPageProps extends MobilePageProps {
  can_register: boolean;
}

export type MobileRegisterPageProps = MobilePageProps;

export interface PendingNumber {
  formatted_number: string;
  /** Seconds until the code expires */
  expires_in: number;
  /** Seconds before another code can be sent */
  resend_in: number;
  remaining_attempts: number;
}

export interface VerifyPageProps extends MobilePageProps {
  purpose: "login" | "register" | "update";
  title: string;
  mobile_number: PendingNumber;
  verify_url: string;
  resend_url: string;
  change_url: string;
}

export interface RegisterDetailsPageProps extends MobilePageProps {
  form: StencilSchema;
  mobile_number: { formatted_number: string };
}

export interface MobileNumberSettingsPageProps extends MobilePageProps {
  current_number: { number: string; country_code: string; formatted_number: string } | null;
}
