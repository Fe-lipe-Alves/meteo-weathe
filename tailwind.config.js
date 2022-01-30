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
            },
            colors: {
                'grayish-white': {
                    100: '#F8F8F8',
                    200: '#F2F2F2',
                },
                'minimum': '#404040',
            }
        },
        fontFamily: {
            sans: [
                'Raleway',
                ...defaultTheme.fontFamily['sans']
            ],
            roboto: [
                'Roboto',
                ...defaultTheme.fontFamily['sans']
            ]
        },
    },
    plugins: [],
}
