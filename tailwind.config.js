import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    theme: {
        extend: {
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
        typography(),
    ],
};
