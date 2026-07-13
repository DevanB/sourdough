---
name: ultracite
description: "Ultracite is a zero-config linting and formatting preset for JavaScript/TypeScript projects. Use when: (1) Setting up or initializing Ultracite in a project (ultracite init), (2) Running linting or formatting commands (check, fix, doctor), (3) Writing or reviewing JS/TS code in a project that uses Ultracite — to follow its code standards, (4) Troubleshooting linting/formatting issues, (5) User mentions 'ultracite', 'lint', 'format', 'code quality', or 'oxlint' in a project with Ultracite installed."
---

# Ultracite

Zero-config linting and formatting for JS/TS projects. Supports **Oxlint** + Oxfmt.

## Detecting Ultracite

Check if `ultracite` is in `package.json` devDependencies. Detect the active linter by looking for`.oxlintrc.json`.

## CLI Commands

```bash
# Check for issues (read-only)
bun run test:lint

# Auto-fix issues
bun run lint

# Diagnose setup problems
bun run doctor

# Initialize in a new project
bunx ultracite init
```

`test:lint` and `lint` use `vp fmt` and `vp lint` with ultracite's oxlint presets via `.oxlintrc.json`.

## Initialization

`bunx ultracite init` runs an interactive setup. For non-interactive (CI) use, pass flags:

```bash
bunx ultracite init \
  --pm bun \
  --linter oxlint \
  --editors cursor \
  --agents cursor-cli \
  --frameworks react \
  --integrations lefthook \
  --quiet
```

**Flags:**

- `--pm` — `npm` | `yarn` | `pnpm` | `bun`
- `--linter` — `biome` (recommended) | `eslint` | `oxlint`
- `--editors` — `vscode` | `zed` | `cursor` | `windsurf` | `antigravity` | `kiro` | `trae` | `void`
- `--agents` — `claude` | `codex` | `copilot` | `cline` | `amp` | `gemini` | `cursor-cli` + 19 more
- `--frameworks` — `react` | `next` | `solid` | `vue` | `svelte` | `qwik` | `remix` | `angular` | `astro` | `nestjs`
- `--integrations` — `husky` | `lefthook` | `lint-staged` | `pre-commit`
- `--hooks` — Enable auto-fix hooks for supported agents/editors
- `--type-aware` — Enable type-aware linting (oxlint only)
- `--skip-install` — Skip dependency installation
- `--quiet` — Suppress prompts (auto-detected when `CI=true`)

Init creates config that extends Ultracite presets:

```jsonc
// .oxlintrc.json
{
  "$schema": "./node_modules/oxlint/configuration_schema.json",
  "extends": [
    "./node_modules/ultracite/config/oxlint/core/.oxlintrc.json"
  ]
}
```

Framework presets available per linter: `core`, `react`, `next`, `solid`, `vue`, `svelte`, `qwik`, `remix`, `angular`, `astro`, `nestjs`.

## Code Standards

When writing code in a project with Ultracite, follow these standards. For the full rules reference, see [references/code-standards.md](references/code-standards.md).

Key rules at a glance:

**Formatting:** 2-space indent, semicolons, double quotes, 100-char width, all trailing commas, LF line endings.

**Style:** Arrow functions preferred. `const` by default, never `var`. `for...of` over `.forEach()`. Template literals over concatenation. No enums (use objects with `as const`). No nested ternaries. Kebab-case filenames.

**Correctness:** No unused imports/variables. No `any` (use `unknown`). Always `await` promises in async functions. No `console.log`/`debugger`/`alert` in production.

**React:** Function components only. Hooks at top level. Exhaustive deps. `key` on iterables (no array index). No nested component definitions. Semantic HTML + ARIA.

**Performance:** No accumulating spread in loops. No barrel files. No namespace imports. Top-level regex.

**Security:** `rel="noopener"` on `target="_blank"`. No `dangerouslySetInnerHTML`. No `eval()`.

## Troubleshooting

Run `bunx ultracite doctor` to diagnose. It checks:

1. Linter installation (oxlint binary available)
2. Config validity (extends ultracite presets correctly)
3. Ultracite in package.json dependencies
4. Conflicting tools (old `.eslintrc.*`, `.prettierrc.*` files)

Common fixes:

- **Conflicting configs**: Delete legacy `.eslintrc.*` and `.prettierrc.*` files after migrating to Ultracite
- **Missing dependency**: Run `bunx ultracite init` again or manually add `ultracite` to devDependencies
- **Rules not applying**: Ensure config file extends the correct presets for your framework
