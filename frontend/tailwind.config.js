/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./public/**/*.{html,js,php}'],
  theme: {
    extend: {
      colors: {
        sidebar: {
          DEFAULT: '#1e1b4b',
          hover: '#312e81',
          active: '#3730a3',
        },
      },
    },
  },
  plugins: [],
};
