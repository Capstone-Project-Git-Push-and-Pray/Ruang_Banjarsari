import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/**/*.php',
    ],
    safelist: [
        'bg-amber-600',
        'border-amber-700',
        'bg-blue-600',
        'border-blue-700',
        'bg-indigo-600',
        'border-indigo-700',
        'bg-[#1F6B4F]',
        'border-[#174B39]',
        'bg-red-600',
        'border-red-700',
        'bg-zinc-700',
        'border-zinc-800',
        'bg-sky-600',
        'border-sky-700',
        'text-white',
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: '#1F6B4F',
                    dark: '#174B39',
                    light: '#EAF5EE',
                    50: '#EAF5EE',
                    100: '#D5EBE0',
                    200: '#ABD7C1',
                    300: '#81C3A2',
                    400: '#57AF83',
                    500: '#1F6B4F',
                    600: '#1B5E45',
                    700: '#174B39',
                    800: '#12382B',
                    900: '#0D261D',
                },
                canvas: '#F7F9F7',
                surface: '#FFFFFF',
                border: '#E2E8E4',
                success: '#15803D',
                warning: '#D97706',
                danger: '#DC2626',
                info: '#2563EB',
            },
            fontFamily: {
                display: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                'card': '0.75rem',
            },
            boxShadow: {
                'card': '0 1px 3px 0 rgba(31, 41, 55, 0.05)',
                'card-hover': '0 4px 6px -1px rgba(31, 41, 55, 0.08), 0 2px 4px -2px rgba(31, 41, 55, 0.04)',
                'modal': '0 20px 25px -5px rgba(31, 41, 55, 0.1), 0 8px 10px -6px rgba(31, 41, 55, 0.04)',
            },
        },
    },

    plugins: [forms],
};
