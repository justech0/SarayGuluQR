/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: ['./index.html', './index.tsx', './App.tsx', './components/**/*.{ts,tsx}', './context.tsx'],
  theme: {
    extend: {
      colors: {
        saray: {
          gold: '#D4AF37',
          darkGold: '#B8860B',
          olive: '#556B2F',
          black: '#111111',
          surface: '#1C1C1C',
          text: '#F5F5DC',
          muted: '#A8A29E',
        },
      },
      fontFamily: {
        serif: ['"Cinzel"', 'serif'],
        sans: ['"Lato"', 'sans-serif'],
      },
      backgroundImage: {
        noise: "url('https://www.transparenttextures.com/patterns/stardust.png')",
        'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
      },
      animation: {
        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        float: 'float 6s ease-in-out infinite',
      },
      keyframes: {
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-10px)' },
        },
      },
    },
  },
  plugins: [],
};
