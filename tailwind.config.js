import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'violet': {
                    DEFAULT: '#7c3aed',
                    'dk': '#6d28d9',
                    'lt': '#ede9fe',
                },
                'navy': '#111827',
            },
            fontSize: {
                'tiny': '10px',
                'dense': '9px',
            },
            borderRadius: {
                'mega': '2.5rem',
                'ultra': '2rem',
            },
        },
    },
    plugins: [],
};
