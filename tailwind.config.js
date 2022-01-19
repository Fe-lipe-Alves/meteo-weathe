const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            backdropBlur: {
                xs: '2px',
            },
            fontsize: {
                'xs': '.75rem',
                'sm': '.875rem',
            }
        },
        fontFamily: {
            sans: [
                'Raleway',
                ...defaultTheme.fontFamily['sans']
            ]
        },
    },
    plugins: [],
}
