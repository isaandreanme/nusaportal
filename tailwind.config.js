/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/bezhansalleh/filament-language-switch/resources/views/language-switch.blade.php',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                "takenaka-red": "#7D1B3D",
            },
        },
    },
    plugins: [],
};
