/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        cafe: {
          50: '#FDF8F5',
          100: '#F9EDE5',
          200: '#F2D8C7',
          300: '#E6BDA0',
          400: '#D59B74',
          500: '#B86F3E',
          600: '#9E572D',
          700: '#814122',
          800: '#64301B',
          900: '#4D2415',
          950: '#2A1108',
        },
        ethiopia: {
          green: '#009B48',
          yellow: '#FED100',
          red: '#EF3340',
        }
      },
      fontFamily: {
        sans: ['Inter', 'Noto Sans Ethiopic', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
