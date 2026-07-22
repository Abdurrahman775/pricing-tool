# Design System — Extracted from Screenshot

## Color Palette

| Token | Hex | Tailwind | Usage |
|-------|-----|----------|-------|
| `purple-gradient-start` | `#b17df4` | `purple-400` | Top header gradient start |
| `purple-gradient-end` | `#ae7cf4` | `purple-400` | Top header gradient end |
| `navy-primary` | `#51459e` | `indigo-800` | Hero cards, section backgrounds, buttons |
| `cyan-accent` | `#84e8f4` | `cyan-400` | Icons, badges, progress indicators, stat highlights |
| `bg-page` | `#f2f9fe` | `blue-50` | Page/site background |
| `bg-card` | `#ffffff` | `white` | Card and content area backgrounds |
| `text-dark` | `#1a1a2e` | `gray-900` | Body text |
| `text-muted` | `#6b7280` | `gray-500` | Secondary labels, descriptions |

## Layout

- **Full-width purple gradient header** at the very top of the page (~60px height)
- **Centered content layout** — the page content is centered within a max-width container
- **Navy hero/stats card** centered on the page, roughly 460-500px wide, containing:
  - White text titles
  - Cyan accent icons/badges (decorative elements)
  - Stats numbers or key metrics
- **Content sections below** the hero card on white/light background
- **Generous whitespace and padding** for a clean, modern look

## Typography & Style

- Clean, modern, minimal aesthetic
- Dark text on light backgrounds, white text on navy backgrounds
- Rounded corners on cards and buttons assumed (standard modern UI convention)
- Responsive: stacked layout on mobile, centered card on tablet/desktop

## Tailwind Configuration

```js
// tailwind.config.js
module.exports = {
  content: ['./public/**/*.{html,js}'],
  theme: {
    extend: {
      colors: {
        navy: {
          DEFAULT: '#51459e',
          50: '#f0effa',
          100: '#d5d1f0',
          200: '#b8b1e4',
          300: '#9b91d8',
          400: '#7e71cc',
          500: '#51459e',  // navy-primary
          600: '#3f357e',
          700: '#2d265e',
          800: '#1c173e',
          900: '#0b091f',
        },
        accent: {
          cyan: '#84e8f4',
        },
      },
      backgroundImage: {
        'gradient-header': 'linear-gradient(135deg, #b17df4, #ae7cf4)',
      },
    },
  },
  plugins: [],
};
```

## Page Templates & Agent Assignments

Each page follows this design language:

| Page | Layout Pattern | Agent |
|------|---------------|-------|
| `index.html` (landing/login) | Centered card on blue-50 bg | Frontend |
| `register.html` | Centered card on blue-50 bg | Frontend |
| `dashboard.html` | Purple header + centered hero card + content list below | Frontend |
| `wizard.html` (7-step) | Stepped form, purple header, white card body | Frontend |
| `intake.html` | Form with sections, white card on bg | Frontend |
| `quote.html` | Package tier cards, pricing breakdown | Frontend |
