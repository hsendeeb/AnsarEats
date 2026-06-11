/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    darkMode: 'class',
    theme: {
        extend: {
            fontFamily: {
                sans: ['Instrument Sans', 'Inter', 'ui-sans-serif', 'system-ui'],
                arabic: ['Tajawal', 'sans-serif'],
            },
        },
    },
    plugins: [],
}
