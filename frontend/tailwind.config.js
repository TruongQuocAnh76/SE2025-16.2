/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./components/**/*.{js,vue,ts}",
    "./layouts/**/*.vue",
    "./pages/**/*.vue",
    "./plugins/**/*.{js,ts}",
    "./app.vue",
    "./error.vue",
    "./app/components/**/*.{vue,ts}",
    "./app/domains/*/components/**/*.vue",
    "./app/domains/*/pages/**/*.vue",
    "./app/domains/*/layouts/**/*.vue",
    "./app/base/**/*.{vue,ts}"
  ],
  theme: {
    extend: {
      fontFamily: {
        'inter': ['Inter', 'system-ui', 'sans-serif']
      },
      fontSize: {
        'h1': ['3rem', { lineHeight: '1.2', fontWeight: '700' }],
        'h2': ['2.25rem', { lineHeight: '1.25', fontWeight: '700' }],
        'h3': ['1.875rem', { lineHeight: '1.3', fontWeight: '600' }],
        'h4': ['1.5rem', { lineHeight: '1.35', fontWeight: '600' }],
        'h5': ['1.25rem', { lineHeight: '1.4', fontWeight: '600' }],
        'h6': ['1rem', { lineHeight: '1.5', fontWeight: '600' }],
        'body': ['1rem', { lineHeight: '1.6', fontWeight: '400' }],
        'body-sm': ['0.875rem', { lineHeight: '1.5', fontWeight: '400' }],
        'caption': ['0.75rem', { lineHeight: '1.4', fontWeight: '500' }]
      },
      spacing: {
        '1': '0.25rem',  // 4px - Micro spacing
        '2': '0.5rem',   // 8px - Small gaps, inner padding
        '4': '1rem',     // 16px - Default spacing
        '6': '1.5rem',   // 24px - Medium spacing, container padding
        '8': '2rem',     // 32px - Large spacing, section gaps
        '12': '3rem'     // 48px - Extra large spacing
      },
      colors: {
        'primary': {
          '500': '#0ea5aa',
          '600': '#0c8e93'
        },
        'accent': {
          '500': '#8534c2'
        },
        'success': {
          '500': '#34c178'
        },
        'error': {
          '500': '#ee3434'
        },
        'warning': {
          '500': '#ff9000'
        },
        'neutral': {
          '0': '#ffffff',
          '50': '#fafbfc',
          '600': '#566a80',
          '700': '#3d4f60',
          '900': '#0b1a20'
        }
      },
      animation: {
        'chain-pulse': 'chainPulse 3s ease-in-out infinite',
        'cap-bounce': 'capBounce 0.6s ease-in-out',
        'link-float': 'linkFloat 2s ease-in-out infinite',
      },
      keyframes: {
        chainPulse: {
          '0%, 100%': {
            opacity: '1',
            transform: 'translateY(0)'
          },
          '50%': {
            opacity: '0.8',
            transform: 'translateY(-1px)'
          }
        },
        capBounce: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-2px)' }
        },
        linkFloat: {
          '0%, 100%': {
            opacity: '1',
            transform: 'scale(1)'
          },
          '50%': {
            opacity: '0.7',
            transform: 'scale(1.1)'
          }
        }
      }
    },
  },
  plugins: [],
}
