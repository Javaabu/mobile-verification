import { defineConfig } from "vitest/config";

// @javaabu/auth and @javaabu/stencil are those packages' React code in vendor/ (composer install first),
// as javaabu/manifest's Vite plugin resolves them in apps
export default defineConfig({
  resolve: {
    alias: {
      "@javaabu/mobile-verification": new URL("./resources/js", import.meta.url).pathname,
      "@javaabu/auth": new URL("./vendor/javaabu/auth/resources/js", import.meta.url).pathname,
      "@javaabu/stencil": new URL("./vendor/javaabu/stencil/resources/js", import.meta.url).pathname,
    },
  },
  test: {
    environment: "jsdom",
    globals: true,
    include: ["tests/js/**/*.test.{ts,tsx}"],
    setupFiles: ["tests/js/setup.ts"],
  },
});
