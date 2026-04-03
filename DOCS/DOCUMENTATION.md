# 📦 IQA Metal: Multi-Order Management System
## 📋 Project Overview
A robust, high-performance order procurement application designed for building and managing complex inventory batches. Optimized for both rapid warehouse intake on mobile devices and professional desktop manifest generation.

---

## 🚀 Core Features

### 1. Active Customer Registry
Manage your entire B2B database from a centralized dashboard.
- **Smart Search**: Instantly filter customers by name or ID.
- **Dynamic Detail View**: Select a customer to see their order history, active drafts, and account details.
- **In-Place Editing**: Update company profiles without navigating away.

### 2. Batch Builder (Order Entry)
The central tool for adding hardware to active orders.
- **Dynamic Logic**: Intelligent dropdowns that filter models and series based on the selected brand (Dell, HP, Apple, etc.).
- **Live Summary**: Real-time ticker showing recently added items.
- **Optimized for Mobile**: Touch-friendly inputs and instant loading.

### 3. Verification & Checkout
The final stage before manifest delivery.
- **Adaptive Layout**: Tables transform into mobile-friendly cards on small screens to prevent horizontal scrolling.
- **Live Recalculation**: Subtotals and grand totals update instantly as you adjust quantities or pricing.
- **Export Formats**:
  - 🖨 **Print Manifest**: Professional PDF-ready layout with approval signature lines.
  - 📊 **CSV Export**: Clean, Excel-ready data distribution.

### 4. System Settings
Administrative control panel for system-wide configuration.
- **Staff Management**: Add or remove access for employees.
- **Invoice Signatures**: Define the "Approved By" name used on official documents.
- **Maintenance Tools**: One-click cleanup of inactive customers with zero orders.

---

## 🛠 Technical Architecture

| Component | Technology |
| :--- | :--- |
| **Backend** | PHP 8.x (XAMPP Environment) |
| **Database** | SQLite 3 (Distributed: `customers`, `orders`, `users`) |
| **Frontend** | Vanilla JavaScript (Pre-compiled for Mobile) |
| **Styling** | Modern Vanilla CSS with high-concurrency flex layouts |

### Mobile-First Optimizations
- **Compiled Assets**: TypeScript source in `assets/ts/` is compiled to high-performance native JavaScript in `assets/js/` to eliminate browser-side compilation delays on mobile.
- **Responsive Stacking**: Complex inventory tables automatically collapse into vertically-stacked cards on devices with width < 650px.

---

## 📂 Project Structure

```bash
├── assets/
│   ├── db/        # SQLite databases (Critical Data)
│   ├── js/        # Native high-performance scripts
│   ├── styles/    # Aesthetic and layout CSS
│   └── ts/        # TypeScript source (Legacy/Dev)
├── core/          # Authentication and shared logic
├── pages/         # Page fragments (Registry, Builder, etc.)
├── index.php      # Main application gateway
└── checkout.php   # Verification and export hub
```

---

## ⚙️ Maintenance & Troubleshooting

### Database Cleanup
To keep the registry tidy, admins can use the **Cleanup Tool** in the Settings menu. This utility cross-references the `customers` and `orders` databases to identify and safely remove profiles that have never been used in a transaction.

### Critical Paths
The application utilizes `realpath()` for all database attachments. This ensures that the SQLite `ATTACH DATABASE` commands resolve correctly, even when pages are included deep within the folder hierarchy.
