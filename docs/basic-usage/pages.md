---
title: Pages (Blade and React)
sidebar_position: 6
---

Every page exists in Blade ([javaabu/forms](https://github.com/Javaabu/forms) v2 components) and in Inertia React ([@javaabu/forms](https://github.com/Javaabu/react-forms) and [@javaabu/onu](https://www.npmjs.com/package/@javaabu/onu)), with the same props. Which one renders follows the user type (`views` on the type, or `javaabu-auth.views`), so the mobile pages match the rest of the login pages. Set `mobile-verification.views` to `blade` or `inertia` to choose for this package alone.

| Page | Blade view | React page | Layout |
| --- | --- | --- | --- |
| Mobile login | `mobile-verification::login` | `mobile-verification::Login` | `auth` |
| Mobile registration | `mobile-verification::register` | `mobile-verification::Register` | `auth` |
| The code (every flow) | `mobile-verification::verify` | `mobile-verification::Verify` | `auth`, or `account` when changing a number |
| Registration details | `mobile-verification::register-details` | `mobile-verification::RegisterDetails` | `auth` |
| Account page | `mobile-verification::settings.mobile-number` | `mobile-verification::Settings/MobileNumber` | `account` |

# Props

Every page gets javaabu/auth's shared props (`auth_type`, `urls`, `account_links`, `status`) and:

```ts
mobile: {
  country_codes: Record<string, string>; // { "960": "+960" }
  default_country_code: string;
  number_prefix: string;
  code_length: number;
}
strings: Record<string, string>; // translated, React only
```

| Page | Props |
| --- | --- |
| Login | `can_register` |
| Verify | `purpose` (`login`, `register`, `update`), `title`, `mobile_number` (`formatted_number`, `expires_in`, `resend_in`, `remaining_attempts`), `verify_url`, `resend_url`, `change_url` |
| RegisterDetails | `form` (the stencil schema), `mobile_number.formatted_number` |
| Settings/MobileNumber | `current_number` (`number`, `country_code`, `formatted_number`) or `null` |

The form fields are `country_code`, `number`, `remember` (login), and `verification_code`.

# Blade

The pages extend the user type's layouts from javaabu/auth (`javaabu-auth.layouts.auth` and `.account`, or the type's `layouts`), filling `javaabu-auth.layout_section`. The layout needs `@manifestStyles` and `@manifestScripts` (or manifest's auto-injection), as for javaabu/auth.

The number input, as Blade and React render it:

```blade
@include('mobile-verification::partials.number-input', ['label' => __('Mobile number')])
```

```tsx
import { NumberInput } from "@javaabu/mobile-verification";

<NumberInput mobile={mobile} strings={strings} label={strings.mobile_number} />
```

With one allowed country code it's a `tel` input with the prefix in front and a hidden `country_code`; with more, a country code select next to it.

Override a view with `php artisan vendor:publish --tag=mobile-verification-views`, then edit `resources/views/vendor/mobile-verification/…`.

# Inertia React

Set up Inertia as for javaabu/auth's pages: install `inertiajs/inertia-laravel`, the npm packages (`@javaabu/forms`, `@javaabu/onu`, `@inertiajs/react`, `react`, `react-dom`), and resolve package pages with javaabu/manifest:

```tsx
// resources/js/app.tsx
import { createInertiaApp } from "@inertiajs/react";
import { FormsProvider } from "@javaabu/forms";
import { inertia } from "@javaabu/forms/inertia";
import { resolvePage } from "@javaabu/manifest/inertia";
import { createRoot } from "react-dom/client";

createInertiaApp({
  resolve: (name) => resolvePage(name, import.meta.glob("./pages/**/*.tsx")),
  setup({ el, App, props }) {
    createRoot(el).render(
      <FormsProvider router={inertia}>
        <App {...props} />
      </FormsProvider>,
    );
  },
});
```

The account pages wrap themselves in javaabu/auth's `AccountLayout`, passing `account_links` as its `links`, so the navigation matches auth's own pages (see [the account navigation](verifying-numbers.md#the-account-navigation)). The pages import `@javaabu/auth` (for `AccountLayout`) and `@javaabu/stencil` (for `StencilForm`), which javaabu/manifest's Vite plugin resolves from the Composer packages. `php artisan manifest:check` lists missing npm packages. It asks for the React packages only in apps with `@inertiajs/react` in their `package.json`; Blade-only apps install nothing from npm for this package.

Replace a page with `php artisan manifest:override mobile-verification Pages/Login.tsx`, or in `resolvePage`. Apps can reuse the components:

```tsx
import { MobileCard, NumberInput, StatusAlert, t } from "@javaabu/mobile-verification";
import type { VerifyPageProps } from "@javaabu/mobile-verification";
```

# Right to left

The pages use logical utilities only (`ms-`, `text-start`; a test rejects `ml-` and `text-left`). Numbers and codes are `dir="ltr"`, and numbers inside Dhivehi sentences are wrapped in `<bdi dir="ltr">`, so `+9607771234` doesn't turn into `9607771234+`. The registration form's name field types Thaana on Dhivehi pages.
