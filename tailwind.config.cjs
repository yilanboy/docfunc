import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/livewire/livewire/src/Features/SupportPagination/views/*.blade.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.ts',
    ],
    theme: {
        extend: {
            colors: {
                lividus: {
                    50: '#e4ecf5',
                    100: '#cddeee',
                    200: '#abc7e3',
                    300: '#88b0d7',
                    400: '#6699cc',
                    500: '#4482c1',
                    600: '#366ba1',
                    700: '#2a547e',
                    800: '#1d3d5b',
                    900: '#111e38',
                },
            },
            fontFamily: {
                sans: [
                    'Noto Sans TC',
                    'Roboto',
                    ...defaultTheme.fontFamily.sans,
                ],
                'jetbrains-mono': [
                    'JetBrains Mono',
                    'Noto Sans TC',
                    ...defaultTheme.fontFamily.sans,
                ],
            },
            keyframes: {
                'fade-in': {
                    from: {
                        opacity: 0,
                        transform: 'translateY(20px)',
                    },
                    to: {
                        opacity: 1,
                        transform: 'translateY(0)',
                    },
                },
                'grow-width': {
                    from: {
                        width: 0,
                    },
                    to: {
                        width: '100%',
                    },
                },
            },
            animation: {
                'fade-in': 'fade-in 0.5s ease-in-out',
                'grow-width': 'grow-width 1s forwards',
            },
            typography: (theme) => ({
                DEFAULT: {
                    css: {
                        // blockquote
                        'blockquote p:first-of-type::before': null,
                        'blockquote p:last-of-type::after': null,
                        // inline code
                        ':not(pre) > code': {
                            backgroundColor: theme('colors.green.200'),
                            color: theme('colors.green.900'),
                            padding: '0.25rem',
                            fontWeight: '600',
                            borderRadius: '0.25rem',
                        },
                        '.dark :not(pre) > code': {
                            backgroundColor: theme('colors.lividus.700'),
                            color: theme('colors.gray.50'),
                        },
                    },
                },
            }),
        },
    },
    plugins: [
        forms({
            strategy: 'class',
        }),
        typography,
    ],
};
