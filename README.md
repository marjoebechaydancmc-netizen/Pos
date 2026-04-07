# 🛒 NavBech Restobar POS (Static Version)

A fast, modern, and fully offline Point of Sale (POS) system built with HTML, CSS, and JavaScript. This version is designed to be hosted on **GitHub Pages** (or any other static host) as it requires no backend or database.

## ✨ Features

- **Product Management**: Categorized menu for easy selection.
- **Cart System**: Real-time total calculation and quantity adjustments.
- **Payment Processing**: Simple checkout workflow with change calculation.
- **Transaction History**: View, delete, and clear all previous sales.
- **Stats Dashboard**: Automatic calculation of Total Income and Average Order value.
- **Data Persistence**: Uses `localStorage` to keep your history persistent in the browser.
- **Receipt Printing**: Built-in support for browser-based printing.

## 🚀 Deployment to GitHub Pages

To host this on GitHub for free:

1.  **Create a New Repository**: Go to [GitHub](https://github.com/new) and name it something like `navbech-pos`.
2.  **Upload the Files**: Upload `index.html`, `history.html`, and any asset folders (if any).
3.  **Enable GitHub Pages**:
    *   Navigate to **Settings** > **Pages**.
    *   Under **Build and deployment**, select **Deploy from a branch**.
    *   Choose use the `main` branch and the `/ (root)` folder.
    *   Click **Save**.
4.  **Visit Your Site**: Your POS will be live at `https://your-username.github.io/navbech-pos/`.

## 📂 Project Structure

- `index.html`: The main POS interface and terminal logic.
- `history.html`: The detailed transaction history and stats dashboard.
- `php_backup/`: Contains the original PHP/MySQL version for archival purposes.

---
*Created by [your-username]*
