import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Brand palette (reskins every existing `indigo-*` class site-wide)
                indigo: {
                    50: '#f0f6ff',
                    100: '#dcebff',
                    200: '#c0dbff',
                    300: '#93c2ff',
                    400: '#5fa1ff',
                    500: '#357eff',
                    600: '#1a5cf2',
                    700: '#1547c4',
                    800: '#173c9c',
                    900: '#17357b',
                },
                amber: {
                    50: '#fffaeb',
                    100: '#fef0c7',
                    200: '#fedf89',
                    300: '#fdc94b',
                    400: '#fbb521',
                    500: '#f59808',
                    600: '#da7404',
                    700: '#b45307',
                    800: '#92400d',
                    900: '#78350e',
                },
            },
        },
    },

    plugins: [forms],
};
