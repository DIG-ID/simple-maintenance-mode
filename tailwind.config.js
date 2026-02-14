/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './public/partials/**/*.php',
  ],
  theme: {

    extend: {
      colors: {
        'blue-shade-1': '#EFEFEF',
        'blue-shade-2': '#474B61',
        'blue-shade-3': '#212331',
        'blue-shade-4': '#181828',
        'yellow': '#E0CB00',
        'red': '#F44336',
      },
      fontFamily: {
        sourcesanspro: ['Source Sans Pro', 'sans-serif'],
        montserrat: ['Montserrat', 'sans-serif'],
      },
    }
  },
  plugins: [],
}