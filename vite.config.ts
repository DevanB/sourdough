import inertia from '@inertiajs/vite';
import {wayfinder} from '@laravel/vite-plugin-wayfinder';
import babel from '@rolldown/plugin-babel';
import tailwindcss from '@tailwindcss/vite';
import react, {reactCompilerPreset} from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import oxlintCore from 'ultracite/oxlint/core';
import oxlintReact from 'ultracite/oxlint/react';
import {defineConfig} from 'vite-plus';

/**
 * Ultracite's oxlint presets target a newer oxlint than the one Vite+ bundles
 * (vite-plus 0.2.6 ships oxlint 1.66.0; ultracite 7.9.x targets ~1.75). Oxlint
 * hard-fails on unknown rule names, so these are dropped until Vite+ catches
 * up. Re-check this list whenever vite-plus is upgraded.
 */
const rulesUnknownToBundledOxlint = [
    'jsdoc/require-yields-description',
    'no-unreachable-loop',
    'node/callback-return',
    'node/no-mixed-requires',
    'node/no-sync',
    'prefer-named-capture-group',
    'react/jsx-no-literals',
    'react/react-compiler',
    'typescript/method-signature-style',
    'unicorn/explicit-timer-delay',
    'unicorn/import-style',
    'unicorn/max-nested-calls',
    'unicorn/no-array-fill-with-reference-type',
    'unicorn/no-confusing-array-with',
    'unicorn/prefer-export-from',
    'unicorn/prefer-number-coercion',
    'unicorn/prefer-single-call',
];

const ultraciteRules = Object.fromEntries(
    Object.entries({...oxlintCore.rules, ...oxlintReact.rules}).filter(
        ([rule]) => !rulesUnknownToBundledOxlint.includes(rule),
    ),
);

export default defineConfig({
    lint: {
        options: {
            typeAware: true,
            typeCheck: true,
        },
        env: {
            ...oxlintCore.env,
        },
        plugins: [
            'eslint',
            'typescript',
            'unicorn',
            'oxc',
            'import',
            'jsdoc',
            'node',
            'promise',
            'react',
            'react-perf',
            'jsx-a11y',
        ],
        rules: {
            ...ultraciteRules,
            'func-style': ['error', 'declaration', {allowArrowFunctions: true}],
            'import/no-named-as-default-member': 'off',
            'react-perf/jsx-no-new-function-as-prop': 'off',
        },
        overrides: oxlintCore.overrides,
        ignorePatterns: [
            'bootstrap/ssr',
            'node_modules',
            'public',
            'resources/js/actions/**',
            'resources/js/components/ui/*',
            'resources/js/routes/**',
            'resources/js/wayfinder/**',
            'resources/views/mail/*',
            'tailwind.config.js',
            'vendor',
            'vite.config.ts',
        ],
    },
    fmt: {
        printWidth: 80,
        tabWidth: 4,
        useTabs: false,
        semi: true,
        singleQuote: true,
        overrides: [
            {
                files: ['**/*.yml'],
                options: {
                    tabWidth: 2,
                },
            },
        ],
        sortTailwindcss: {
            functions: ['clsx', 'cn'],
            stylesheet: 'resources/css/app.css',
        },
        sortImports: {
            groups: [
                'builtin',
                'external',
                'internal',
                'parent',
                'sibling',
                'index',
            ],
            newlinesBetween: false,
        },
        ignorePatterns: [
            'resources/js/components/ui/*',
            'resources/views/mail/*',
            'resources/js/actions/*',
            'resources/js/routes/*',
            'resources/js/wayfinder/*',
        ],
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
        }),
        inertia({
            ssr: {
                entry: 'resources/js/app.tsx',
                host: '127.0.0.1',
                port: 13714,
                cluster: false,
                sourcemap: false,
            },
        }),
        react(),
        babel({
            presets: [reactCompilerPreset()],
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
});
