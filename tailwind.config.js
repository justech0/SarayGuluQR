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
        noise: "url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%224%22 height=%224%22 viewBox=%220 0 4 4%22%3E%3Cpath fill=%22%23fff%22 fill-opacity=%220.04%22 d=%22M0 0h1v1H0zM2 2h1v1H2z%22/%3E%3C/svg%3E')",
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
