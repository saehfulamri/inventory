/**
 * Tailwind CSS Configuration
 */
module.exports = {
  darkMode: 'class',
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      colors: {
        primary: '#0066cc', // Action Blue
        "primary-focus": '#0071e3',
        "primary-on-dark": '#2997ff',
        ink: '#1d1d1f',
        "surface-black": '#000000',
        "surface-pearl": '#fafafc',
        "surface-tile-1": '#272729',
        "surface-tile-2": '#2a2a2c',
        "surface-tile-3": '#252527',
        canvas: '#ffffff',
        "canvas-parchment": '#f5f5f7',
        "on-primary": '#ffffff',
        "on-dark": '#ffffff',
        "divider-soft": '#f0f0f0',
        hairline: '#e0e0e0',
      },
      borderRadius: {
        none: '0px',
        xs: '5px',
        sm: '8px',
        md: '11px',
        lg: '18px',
        pill: '9999px',
        full: '9999px',
      },
      spacing: {
        xxs: '4px',
        xs: '8px',
        sm: '12px',
        md: '17px',
        lg: '24px',
        xl: '32px',
        xxl: '48px',
        section: '80px',
      },
      fontFamily: {
        sans: ['"SF Pro Text"', 'system-ui', '-apple-system', 'sans-serif'],
        display: ['"SF Pro Display"', 'system-ui', '-apple-system', 'sans-serif'],
      },
      screens: {
        'sm-phone': { 'max': '419px' },
        'phone': { 'min': '420px', 'max': '640px' },
        'tablet-portrait': { 'min': '641px', 'max': '833px' },
        'tablet-landscape': { 'min': '834px', 'max': '1068px' },
        'desktop': { 'min': '1069px', 'max': '1440px' },
        'wide': { 'min': '1441px' },
      },
    },
  },
  plugins: [],
};
