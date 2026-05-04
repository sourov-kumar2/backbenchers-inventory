# Reports Module — Development Track Map

## Section 1: Objective & Overview

**Goal:** Provide dedicated, multi-page business intelligence reports.
The Reports module is now split into 4 specific pages for granular analysis:
- **Sales Report** — Detailed transaction ledger with date filters.
- **Profit/Loss Analysis** — Performance metrics based on markup and sales volume.
- **Stock Valuation** — Real-time asset value and inventory health monitoring.
- **Customer Insights** — Behavioral analysis of top clients and collection status.

Architecture follows the existing pattern: PHP + PDO, partials (sidebar, navbar, footer), and glassmorphism UI.

---

## Section 2: File Activity Log

| File | Location | Activity |
|------|----------|----------|
| `reports/sales.php` | `/reports/` | **[NEW]** Dedicated Sales Performance Report page. |
| `reports/profit_loss.php` | `/reports/` | **[NEW]** Dedicated Profit & Loss Analysis report page. |
| `reports/inventory.php` | `/reports/` | **[NEW]** Dedicated Inventory Valuation report page. |
| `reports/customers.php` | `/reports/` | **[NEW]** Dedicated Customer Intelligence report page. |
| `reports/ai_analyst.php` | `/reports/` | **[NEW]** AI Business Analyst powered by Groq (LLAMA-3). |
| `partials/sidebar.php` | `/partials/` | **[MODIFIED]** Updated sidebar with links to all 4 specific report pages + AI link. |
| `config.php` | `/` | **[MODIFIED]** Added GROQ API key and model configuration. |

---

## Section 3: Edit History

| 1 | 2026-05-04 14:04 | Antigravity AI | Initial split into 4 dedicated reporting pages. |
| 2 | 2026-05-04 14:13 | Antigravity AI | Integrated Groq AI (LLAMA-3) Strategic Analyst. Created `ai_analyst.php`, updated `config.php` and `sidebar.php`. |
| 3 | 2026-05-04 14:17 | Antigravity AI | Migrated Groq settings to Database (`system_settings`). Updated `settings.php` for dynamic updates and `config.php` for dynamic fetching. |
| 4 | 2026-05-04 14:22 | Antigravity AI | Enhanced AI Analyst design with premium UI components (stat cards, insight boxes) and added PDF export functionality. Refined AI prompt for cleaner HTML output. |
