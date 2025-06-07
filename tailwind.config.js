// tailwind.config.js
/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    // Penting: Tambahkan Livewire views agar Tailwind memprosesnya
    "./app/Http/Livewire/**/*.php",
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}