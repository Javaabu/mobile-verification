---
title: Dhivehi and translations
sidebar_position: 6
---

Every user-facing string is an English `__()` key, with Dhivehi in the package's `lang/dv.json` (and the English in `lang/en.json`). The Blade pages, the React pages (their `strings` prop, from `Support\Strings`), validation messages and the SMS all use them, so one file covers both stacks. A test fails when a string has no Dhivehi or its placeholders differ.

Change a line in your app's `lang/dv.json` with the same key, or publish the package's:

```bash
php artisan vendor:publish --tag=mobile-verification-translations
```

Strings javaabu/auth also uses ("Log in", "Sign up", "Name", "Email address", the two-factor messages) have javaabu/auth's Dhivehi, so the pages read the same.

The SMS goes out in the locale of the request that asked for it. Numbers and codes stay left to right in Dhivehi pages; see [Pages](../basic-usage/pages.md#right-to-left).

The admin permission descriptions are registered in English ("Change the mobile numbers of users") and translated by javaabu/permissions when it shows them.

# For review

The Dhivehi was written without a native speaker's review. These choices especially need one:

| English | Dhivehi | Question |
| --- | --- | --- |
| Mobile number | މޯބައިލް ނަންބަރު | v2 spelling kept; some apps write މޮބައިލް |
| Your :app verification code is :code. … | :app ގެ ވެރިފިކޭޝަން ކޯޑަކީ :code. … | Natural for an SMS? Shorter is cheaper in Unicode |
| This number is locked after too many wrong codes. … | ގިނަ ފަހަރު ކޯޑު ގޯސްކޮށް ޖެއްސުމުން މި ނަންބަރު ބަންދުކުރެވިފައިވެއެވެ. … | "ބަންދު" for locked |
| The code is incorrect. You have :count attempts left. | ކޯޑު ރަނގަޅެއް ނޫނެވެ. އިތުރަށް :count ފަހަރު މަސައްކަތްކުރެވިދާނެއެވެ. | One form for singular and plural |
| Verify | ވެރިފައި ކުރޭ | Or ތަސްދީޤުކުރޭ? |
| Country code | ޤައުމީ ކޯޑު | |
| Optional | ލާޒިމެއް ނޫން | |
| We will send you a code by SMS. | އެސްއެމްއެސްއިން ކޯޑެއް ފޮނުވާނަމެވެ. | |
| Your number :number is verified. Tell us a little about yourself. | … ތިޔަ ފަރާތާ ބެހޭ މަޢުލޫމާތު ދެއްވާ. | |
