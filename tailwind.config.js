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
                    black: '#1A1A1A',
                    'black-soft': '#1E1E1E',
                    'black-card': '#252525',
                    'inner': 'rgba(37,37,37,0.8)',
                    'inner-hover': 'rgba(45,45,45,0.9)',
                    'border': 'rgba(255,255,255,0.1)',
                    red: '#EF4444',
                    'red-hover': '#F87171',
                    silver: '#B8B8B8',
                    'silver-light': '#E5E5E5',
                    blue: '#3B82F6',
                    'blue-hover': '#60A5FA',
                    green: '#22C55E',
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
