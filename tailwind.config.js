import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                servx: {
                    black: '#0B0B0D',
                    'black-soft': '#111111',
                    'black-card': '#151515',
                    red: '#DC2626',
                    'red-hover': '#EF4444',
                    silver: '#B8B8B8',
                    'silver-light': '#E5E5E5',
                },
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                servx: ['Rajdhani', 'Tajawal', 'system-ui', 'sans-serif'],
            },
            boxShadow: {
                soft: '0 12px 30px rgba(0,0,0,.08)',
                'servx-card': '0 8px 32px rgba(0,0,0,0.5)',
            },
            minHeight: {
                touch: '44px',
            },
        },
    },

    plugins: [forms],
};
