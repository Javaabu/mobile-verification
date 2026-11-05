import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import Login from "../../resources/js/Pages/Login";
import Register from "../../resources/js/Pages/Register";
import RegisterDetails from "../../resources/js/Pages/RegisterDetails";
import MobileNumber from "../../resources/js/Pages/Settings/MobileNumber";
import Verify from "../../resources/js/Pages/Verify";
import { page, strings } from "./fixtures";

describe("Login", () => {
  it("asks for the number with the only country code as a prefix, in Dhivehi", () => {
    render(<Login {...page} can_register />);

    expect(screen.getByText(strings.log_in_with_mobile)).toBeTruthy();

    const number = document.querySelector<HTMLInputElement>('form[action="/login/mobile"] input[name="number"]');
    expect(number?.getAttribute("type")).toBe("tel");
    expect(number?.getAttribute("dir")).toBe("ltr");
    expect(document.querySelector<HTMLInputElement>('input[name="country_code"]')?.value).toBe("960");
    expect(screen.getByText("+960")).toBeTruthy();
    expect(screen.getByText(strings.sign_up).getAttribute("href")).toBe("/register/mobile");
    expect(document.querySelector("[data-password-login]")?.getAttribute("href")).toBe("/login");
  });

  it("shows a country code select when there is a choice", () => {
    render(<Login {...page} mobile={{ ...page.mobile, country_codes: { "960": "+960", "94": "+94" } }} can_register={false} />);

    expect(document.querySelector('select[name="country_code"]')).toBeTruthy();
    expect(screen.queryByText(strings.sign_up)).toBeNull();
  });
});

describe("Register", () => {
  it("asks for the number and links to the mobile login", () => {
    render(<Register {...page} />);

    expect(document.querySelector('form[action="/register/mobile"] input[name="number"]')).toBeTruthy();
    expect(screen.getByText(strings.log_in).getAttribute("href")).toBe("/login/mobile");
  });
});

describe("Verify", () => {
  const props = {
    ...page,
    purpose: "login" as const,
    title: "ލޮގިން ވުމަށް ކޯޑު ޖައްސަވާ",
    mobile_number: { formatted_number: "+9607771234", expires_in: 600, resend_in: 30, remaining_attempts: 5 },
    verify_url: "/login/mobile/verify",
    resend_url: "/login/mobile/resend",
    change_url: "/login/mobile",
  };

  it("asks for the code and keeps the number left to right", () => {
    render(<Verify {...props} />);

    const code = document.querySelector<HTMLInputElement>('form[action="/login/mobile/verify"] input[name="verification_code"]');
    expect(code?.getAttribute("autocomplete")).toBe("one-time-code");
    expect(code?.getAttribute("inputmode")).toBe("numeric");
    expect(code?.getAttribute("maxlength")).toBe("6");

    expect(document.querySelector("bdi")?.textContent).toBe("+9607771234");
    expect(document.querySelector("bdi")?.getAttribute("dir")).toBe("ltr");
    expect(document.querySelector('form[action="/login/mobile/resend"]')).toBeTruthy();
    expect(document.querySelector("[data-change-number]")?.getAttribute("href")).toBe("/login/mobile");
    expect(document.querySelector("[data-resend-in]")?.textContent).toContain("30");
    expect(document.querySelector('[data-mobile-page="verify"]')).toBeTruthy();
  });

  it("uses the account layout when changing a number", () => {
    render(<Verify {...props} purpose="update" />);

    expect(document.querySelector('[data-auth-page="mobile-number-verify"]')).toBeTruthy();
    expect(document.querySelector("[data-account-nav]")).toBeTruthy();
    expect(document.querySelector("[data-resend-in]")).toBeTruthy();
  });

  it("hides the countdown when a new code can be sent", () => {
    render(<Verify {...props} mobile_number={{ ...props.mobile_number, resend_in: 0 }} />);

    expect(document.querySelector("[data-resend-in]")).toBeNull();
  });
});

describe("RegisterDetails", () => {
  it("renders the stencil form", () => {
    render(
      <RegisterDetails
        {...page}
        mobile_number={{ formatted_number: "+9607771234" }}
        form={{
          stencil: 1,
          id: "mobile-registration-form",
          action: "/register/mobile/details",
          method: "POST",
          operation: "create",
          submit: "އެކައުންޓް ހަދާ",
          state: { name: null, email: null },
          live: null,
          components: [
            { type: "text", key: "name", name: "name", component: "Text", props: { label: "ނަން", required: true, thaana: true } },
            { type: "text", key: "email", name: "email", component: "Email", props: { label: "އީމެއިލް އެޑްރެސް" } },
          ],
        }}
      />,
    );

    expect(document.querySelector('form[action="/register/mobile/details"] input[name="name"]')).toBeTruthy();
    expect(document.querySelector('input[name="email"]')).toBeTruthy();
    expect(screen.getByText("އެކައުންޓް ހަދާ")).toBeTruthy();
    expect(document.querySelector("bdi")?.textContent).toBe("+9607771234");
  });
});

describe("Settings/MobileNumber", () => {
  it("shows the current number and a form for a new one", () => {
    render(<MobileNumber {...page} current_number={{ number: "7771234", country_code: "960", formatted_number: "+9607771234" }} />);

    expect(document.querySelector("[data-current-number] bdi")?.textContent).toBe("+9607771234");
    expect(screen.getByText(strings.new_mobile_number)).toBeTruthy();
    expect(document.querySelector('form[action="/settings/mobile-number"] input[name="number"]')).toBeTruthy();
    expect(document.querySelector("[data-account-nav]")).toBeTruthy();
  });

  it("shows the account links, this page among them", () => {
    render(<MobileNumber {...page} current_number={null} />);

    const link = document.querySelector('[data-account-nav] a[href="/settings/mobile-number"]');
    expect(link?.textContent).toBe("މޯބައިލް ނަންބަރު");
    expect(link?.getAttribute("aria-current")).toBe("page");
    expect(document.querySelector('[data-account-nav] a[href="/settings/profile"]')).toBeTruthy();
  });

  it("warns users without a number", () => {
    render(<MobileNumber {...page} current_number={null} />);

    expect(document.querySelector("[data-no-number]")?.textContent).toBe(strings.no_number);
  });
});
