---
title: AI assistants
sidebar_position: 1.35
---

The docs are plain Markdown, written for people and AI coding assistants alike. The package ships them, so they always match the version you have installed.

| File | What |
| --- | --- |
| `vendor/javaabu/mobile-verification/llms.txt` | The rules for using the package, and every page |
| `vendor/javaabu/mobile-verification/docs/` | The pages |
| `vendor/javaabu/mobile-verification/resources/boost/` | The Laravel Boost skill and guideline |

# Laravel Boost

[Laravel Boost](https://github.com/laravel/boost) finds the skill and guideline by itself in Composer packages. Run this after installing or updating the package:

```bash
php artisan boost:update
```

Choose `javaabu/mobile-verification` (and `javaabu/auth`) if Boost asks which third-party packages to use.

# Other assistants

Point your assistant at the docs from the project's instructions file (`AGENTS.md`, `CLAUDE.md`, `.cursor/rules`, `.github/copilot-instructions.md`):

```md
## Mobile numbers

This app uses javaabu/mobile-verification on top of javaabu/auth. Read vendor/javaabu/mobile-verification/llms.txt before changing mobile login, registration, verification codes, SMS or users' mobile numbers.
```
