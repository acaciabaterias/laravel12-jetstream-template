import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/View/Components/**/*.php',
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    50: '#eef5fb',
                    100: '#d8e8f6',
                    200: '#b5d2ec',
                    300: '#88b3de',
                    400: '#548dc9',
                    500: '#2f6fb0',
                    600: '#1f588f',
                    700: '#123b66',
                    800: '#102f51',
                    900: '#0f2944',
                },
                accent: {
                    50: '#fff8eb',
                    100: '#feefc7',
                    200: '#fddd89',
                    300: '#fdc950',
                    400: '#f8af1d',
                    500: '#f59e0b',
                    600: '#dd7702',
                    700: '#b75406',
                    800: '#94410c',
                    900: '#7a360d',
                },
                success: {
                    50: '#f0fdf4',
                    500: '#16a34a',
                    700: '#15803d',
                },
                warning: {
                    50: '#fefce8',
                    500: '#eab308',
                    700: '#a16207',
                },
                danger: {
                    50: '#fef2f2',
                    500: '#dc2626',
                    700: '#b91c1c',
                },
                neutral: {
                    50: '#f8fafc',
                    100: '#f1f5f9',
                    200: '#e2e8f0',
                    300: '#cbd5e1',
                    400: '#94a3b8',
                    500: '#64748b',
                    600: '#475569',
                    700: '#334155',
                    800: '#1e293b',
                    900: '#0f172a',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            boxShadow: {
                brand: '0 24px 60px -24px rgba(18, 59, 102, 0.45)',
            },
        },
    },
    plugins: [forms, typography],
};
