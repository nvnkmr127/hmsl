# HMS Offline Audit Checklist

We have audited all Blade files, CSS sheets, and JavaScript files in this project to identify and replace all external CDN dependencies with locally hosted equivalents. This guarantees that the application runs completely offline within the Tauri WebView wrapper.

## 🔍 Audited Dependencies & Replacements

| Dependency | Original CDN Source | Replacement Status / Location | Notes |
|---|---|---|---|
| **Outfit Font** | `fonts.googleapis.com` | Locally hosted at [outfit.css](file:///E:/hmsl/public/fonts/outfit.css) and [outfit-*.ttf](file:///E:/hmsl/public/fonts/) | Removed Google Fonts preconnect and stylesheet links from `bill-print.blade.php`, `bill-ipd-print.blade.php`, `bill-opd-print.blade.php`, and `layouts/print-telugu.blade.php`. |
| **Tailwind CSS** | `cdn.tailwindcss.com` | Locally compiled Tailwind CSS using Vite [app.css](file:///E:/hmsl/resources/css/app.css) | Replaced in `first-run.blade.php` using the `@vite` directive. |
| **Figtree / Noto Sans Telugu** | `fonts.bunny.net` / `fonts.googleapis.com` | Replaced with locally served Outfit font at `/fonts/outfit.css` | Updated `welcome.blade.php` to reference `/fonts/outfit.css` and use Outfit font family locally. |
| **Laravel Welcome Background** | `laravel.com` (external SVG) | Downloaded to [background.svg](file:///E:/hmsl/public/images/background.svg) | Modified `welcome.blade.php` image tag source to point to `/images/background.svg`. |
| **Livewire JS** | Laravel backend (Livewire v3 standard) | Fully self-hosted locally via `@livewireScripts` | Served natively by the local PHP server instance (port 8000), requires zero internet. |
| **Chart.js** | Locally hosted | Locally served at [chart.js](file:///E:/hmsl/public/js/chart.js) | Linked in layouts via `<script src="/js/chart.js"></script>`. |

## 🚫 Irreplaceable Dependencies

None. All external asset dependencies (stylesheets, javascript, icon libraries, fonts, and images) have been successfully localized. The application is 100% self-contained and operates completely offline.
