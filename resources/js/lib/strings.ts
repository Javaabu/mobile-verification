import type { MobileStrings } from "../types";

/** A translated string, with :placeholders replaced */
export function t(strings: MobileStrings, key: string, replace: Record<string, string | number> = {}): string {
  let text = strings[key] ?? key;

  for (const [name, value] of Object.entries(replace)) {
    text = text.replaceAll(`:${name}`, String(value));
  }

  return text;
}
