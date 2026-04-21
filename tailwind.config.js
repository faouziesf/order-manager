/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './resources/**/*.scss',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        sans:  ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        inter: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
      colors: {
        // Deep indigo palette — explicit full scale for cinematic dark UI
        indigo: {
          50:  '#eef2ff',
          100: '#e0e7ff',
          200: '#c7d2fe',
          300: '#a5b4fc',
          400: '#818cf8',
          500: '#6366f1',
          600: '#4f46e5', // brand primary
          700: '#4338ca',
          800: '#3730a3',
          900: '#312e81',
          950: '#1e1b4b', // deep dark variant for overlays
        },
      },
      keyframes: {
        // Soft radial glow pulse (indigo)
        'glow-pulse': {
          '0%, 100%': {
            opacity: '0.45',
            transform: 'scale(1)',
            filter: 'blur(20px)',
          },
          '50%': {
            opacity: '0.85',
            transform: 'scale(1.08)',
            filter: 'blur(28px)',
          },
        },
        // Soft glow pulse (violet)
        'glow-pulse-violet': {
          '0%, 100%': { opacity: '0.4',  filter: 'blur(24px)' },
          '50%':       { opacity: '0.8',  filter: 'blur(34px)' },
        },
        // Slower, softer breathe for background ambiance
        'glow-breathe': {
          '0%, 100%': { opacity: '0.2', transform: 'scale(0.96)' },
          '50%':       { opacity: '0.6', transform: 'scale(1.04)' },
        },
      },
      animation: {
        'glow-pulse':        'glow-pulse 4s ease-in-out infinite',
        'glow-pulse-violet': 'glow-pulse-violet 4s ease-in-out infinite 1.5s',
        'glow-pulse-slow':   'glow-pulse 6s ease-in-out infinite',
        'glow-breathe':      'glow-breathe 5s ease-in-out infinite',
        'glow-breathe-slow': 'glow-breathe 8s ease-in-out infinite 2s',
      },
    },
  },
  plugins: [
    // ── 3D Perspective & transform utilities ──────────────────────────────
    function ({ addUtilities }) {
      addUtilities({
        // Perspective distance on parent containers
        '.perspective-none': { perspective: 'none' },
        '.perspective-sm':   { perspective: '500px' },
        '.perspective-md':   { perspective: '1000px' },
        '.perspective-lg':   { perspective: '1200px' },
        '.perspective-xl':   { perspective: '1400px' },
        '.perspective-2xl':  { perspective: '2000px' },
        // Transform style
        '.preserve-3d':      { 'transform-style': 'preserve-3d' },
        '.flat':             { 'transform-style': 'flat' },
        // Backface
        '.backface-hidden':  { 'backface-visibility': 'hidden' },
        '.backface-visible': { 'backface-visibility': 'visible' },
        // rotateX steps
        '.rotate-x-0':   { transform: 'rotateX(0deg)' },
        '.rotate-x-6':   { transform: 'rotateX(6deg)' },
        '.rotate-x-12':  { transform: 'rotateX(12deg)' },
        '.rotate-x-15':  { transform: 'rotateX(15deg)' },
        '.rotate-x-20':  { transform: 'rotateX(20deg)' },
        '.rotate-x-30':  { transform: 'rotateX(30deg)' },
        '.rotate-x-45':  { transform: 'rotateX(45deg)' },
        '.-rotate-x-6':  { transform: 'rotateX(-6deg)' },
        '.-rotate-x-12': { transform: 'rotateX(-12deg)' },
        '.-rotate-x-20': { transform: 'rotateX(-20deg)' },
        // Transform origin helpers
        '.origin-top-center': { 'transform-origin': 'top center' },
      });
    },
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
};
