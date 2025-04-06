/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/**/*.{js,jsx,ts,tsx}"],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: "#3E3069",
          dark: "#302353",
          darker: "#291D48",
          light: "#4A3980",
          lighter: "#564397"
        },
        accent: {
          DEFAULT: "#AA53A4",
          light: "#CE4895",
          dark: "#8F4D9C"
        },
        light: "#F6F3EE"
      },
    },
  },
  plugins: [],
}; 