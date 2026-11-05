import strings from "./strings.dv.json";
import type { MobilePageProps } from "../../resources/js/types";

export { strings };

export const urls = {
  home: "/dashboard",
  login: "/login",
  "login.store": "/login",
  logout: "/logout",
  "profile.edit": "/settings/profile",
  "security.edit": "/settings/security",
  "login.mobile": "/login/mobile",
  "login.mobile.store": "/login/mobile",
  "register.mobile": "/register/mobile",
  "register.mobile.store": "/register/mobile",
  "mobile-number": "/settings/mobile-number",
  "mobile-number.store": "/settings/mobile-number",
};

export const page: MobilePageProps = {
  auth_type: { name: "user", features: { registration: true }, two_factor: "optional", username: "email", login_methods: [] },
  urls,
  account_links: [
    { key: "profile", label: "ޕްރޮފައިލް", url: "/settings/profile", active: false },
    { key: "security", label: "ސެކިއުރިޓީ", url: "/settings/security", active: false },
    { key: "mobile-number", label: "މޯބައިލް ނަންބަރު", url: "/settings/mobile-number", active: true },
  ],
  strings,
  status: null,
  mobile: { country_codes: { "960": "+960" }, default_country_code: "960", number_prefix: "+", code_length: 6 },
};
