/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.jsx',
    ],
    theme: {
        extend: {
            fontFamily: {
                display: ['"Space Grotesk"', 'sans-serif'],
                sans: ['"DM Sans"', 'sans-serif'],
            },
            colors: {
                brand: {
                    50: '#eff9ff',
                    100: '#d7f0ff',
                    200: '#b8e5ff',
                    300: '#88d4ff',
                    400: '#49b8ff',
                    500: '#1d97f5',
                    600: '#0f76d7',
                    700: '#115fae',
                    800: '#144f8f',
                    900: '#164377',
                },
                coral: {
                    50: '#fff5f0',
                    100: '#ffe8dd',
                    200: '#ffd0bc',
                    300: '#ffad8f',
                    400: '#ff7e5d',
                    500: '#ff5a34',
                    600: '#f0451d',
                    700: '#c73616',
                    800: '#9f2f17',
                    900: '#812a18',
                },
            },
            boxShadow: {
                floating: '0 12px 36px -16px rgba(17, 40, 64, 0.35)',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-8px)' },
                },
                reveal: {
                    from: { opacity: '0', transform: 'translateY(16px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
            },
            animation: {
                float: 'float 6s ease-in-out infinite',
                reveal: 'reveal 500ms ease-out',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
