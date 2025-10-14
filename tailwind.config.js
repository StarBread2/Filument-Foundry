/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './assets/**/*.js',
    './assets/**/*.css',
    './templates/**/*.html.twig',
  ],
  safelist: [
    'grid-cols-1',
    'grid-cols-2',
    'grid-cols-3',
    'grid-cols-4',
    'sm:grid-cols-2',
    'md:grid-cols-3',
    'md:grid-cols-4',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}

