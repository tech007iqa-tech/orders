# 🏬 IQA Warehouse Inventory & Order Manager

A modern, responsive web application for managing warehouse hardware inventory and customer orders. Built with a focus on speed, security, and a premium "app-like" user experience — fully optimized for iOS Safari and desktop.

---

## ✨ Key Features

-   **CRM & Relationship Hub**: Full-page lead management with automated balance tracking and a detailed **Historical Interaction Timeline**.
-   **Priority Follow-ups**: Automated "Call Today" logic that highlights accounts requiring immediate attention based on follow-up dates.
-   **Warehouse Location Status**: Track the operational state of every zone (Working, Audit, Warehoused, Idle) with color-coded visual cues.
-   **Smart Sorting Engine**: Advanced sorting in the Warehouse Gate by status priority, shelf density (item count), or alphabetical order, with persistent memory.
-   **Intelligent Customer Registry**: Sort the registry by Date, Name, Total Orders, or LTV, with session-based memory.
-   **Enhanced Warehouse Exports**: CSVs now include active location headers (📍), smart category mapping, and detailed battery health metadata.
-   **Anti-Refresh Pattern (PRG)**: Implements the **Post/Redirect/Get** pattern for zero-error form submissions.
-   **Zero-Config Backend**: Utilizes **SQLite** — completely portable, no server setup required.
-   **iOS Safari Optimized**: `16px` input enforcement, `100dvh` viewport fix, and momentum scrolling support.

---

## 🛠️ Technology Stack

| Layer | Technology |
| :--- | :--- |
| **Backend** | PHP 8.x with Scalable Route Mapping |
| **Database** | SQLite v3 (Multiple DB Architecture: `customers`, `orders`, `users`, `warehouse`) |
| **Security** | `.htaccess` protected SQLite databases & Session-guarded routes |
| **Frontend UI** | Modern HTML5 & Vanilla CSS (glassmorphism, CSS Variables) |
| **Logic** | Vanilla JavaScript (ES6+) with dedicated /api/ AJAX endpoints |
| **Cache Busting**| Automated dynamic versioning using `filemtime()` for all JS/CSS assets |
| **State** | PHP Sessions + Secure JSON DOM injection (no global namespace pollution) |

---

## 📂 Project Structure

```text
├── api/                        # Decoupled JSON API endpoints for status updates & transfers
├── index.php                   # Scalable application router & entry point
├── checkout.php                # Finalized B2B manifest, modal editor & export hub
├── generate_odt.php            # 2×1 Thermal Label ODT generator (Flat XML, no ZipArchive)
├── pages/
│   ├── customer_registry.php   # Customer list, search, and selection UI
│   ├── leads.php               # CRM Hub: Lead tracking & interaction logs
│   ├── new_customer.php        # Detailed customer registration module
│   ├── new_order.php           # Core hardware intake & batch builder
│   ├── orders.php              # Global batch fulfillment registry
│   ├── warehouse.php           # Warehouse stock & location management
│   └── settings.php           # Admin controls & maintenance tools
├── core/
│   ├── auth.php                # Session guard
│   ├── login.php               # Login form
│   └── logout.php             # Session destroyer
├── assets/
│   ├── styles/
│   │   ├── style.css           # Universal design system tokens & base styles
│   │   ├── checkout.css        # Manifest layout, modal animations & iOS fixes
│   │   ├── orders.css          # Batch registry & card grid styles
│   │   ├── warehouse.css       # Warehouse UI and table styles
│   │   ├── customer_registry.css # Account list & sidebar styles
│   │   └── new_order.css       # Batch builder specific styling
│   ├── js/
│   │   ├── checkout.js         # Manifest JS logic & modal editor
│   │   ├── warehouse.js        # Warehouse logic, sorting & persistence
│   │   ├── leads.js            # CRM interaction & timeline logic
│   │   └── new_order.js        # Hardware inventory & UI logic
│   ├── ts/                     # TypeScript source (legacy/dev reference)
│   └── db/
│       ├── customers.db        # SQLite: customer records (gitignored)
│       └── orders.db           # SQLite: hardware orders & items (gitignored)
├── DOCS/
│   └── DOCUMENTATION.md        # Full technical documentation
└── README.md
```

---

## 🚀 Getting Started

### 1. Requirements
-   A local PHP server (XAMPP, WAMP, Laragon, or `php -S localhost:8000`).
-   SQLite3 extension enabled in your `php.ini`.
-   **No ZipArchive needed** — label generation uses the Flat XML ODT format.

### 2. Installation
1.  Clone or download this repository to your `htdocs` or public directory.
2.  Ensure the `assets/db/` directory has **write permissions** (necessary for SQLite to generate and update database files).
3.  Open your browser and navigate to the project URL (e.g., `http://localhost/orders/`).

### 3. Usage
1.  **Register a Customer**: Start by adding a new company in the registration view.
2.  **Select Customer**: Pick an active customer from the Registry searchable list.
3.  **Build Order**: Add hardware specifications on the left; view and search the live summary on the right. Use the ✏️ icon to quickly adjust Qty/Price inline.
4.  **Checkout**: Review the full manifest, use the search bar to locate specific items, click any row to edit full metadata or print a label.
5.  **Finalize**: Save changes and finalize the batch to mark it complete.

---

## 🔧 Maintenance

-   **Database**: The `.db` files are in `assets/db/`, which is protected from public HTTP access via `.htaccess`. Open with any SQLite browser for manual audit.
-   **Styling**: All design tokens (colors, spacing, shadows) are CSS Variables in `:root` inside `style.css`.
-   **JavaScript**: Logic is modularized. State is passed from PHP to JS via secure JSON blobs parsed by specific modules (e.g., `checkout.js`), ensuring no global variable collisions.
-   **Routing**: `index.php` handles routing through a centralized route map, making the addition of new views simple and clean.
-   **Label Printing**: `generate_odt.php` produces Flat OpenDocument XML. If labels don't open, ensure LibreOffice is set as the default `.odt` handler.

---

> [!TIP]
> Built with ❤️ for speed and reliability. For developer support, refer to `DOCS/DOCUMENTATION.md` and inline source comments.
