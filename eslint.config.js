import js from '@eslint/js';
import eslintConfigPrettier from 'eslint-config-prettier';
import globals from 'globals';
import tseslint from 'typescript-eslint';
import pluginVue from 'eslint-plugin-vue';

export default tseslint.config(
    { ignores: ['node_modules', 'public/build', 'vendor'] },
    js.configs.recommended,
    ...tseslint.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    eslintConfigPrettier,
    {
        files: ['resources/js/**/*.{ts,vue}'],
        linterOptions: {
            reportUnusedDisableDirectives: false,
        },
        languageOptions: {
            globals: globals.browser,
            parserOptions: { parser: tseslint.parser },
        },
        rules: {
            'no-undef': 'off',
            'vue/multi-word-component-names': 'off',
            'vue/attributes-order': 'off',
            '@typescript-eslint/no-explicit-any': 'off',
        },
    },
);
