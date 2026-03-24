# Aeternitas Desktop App

This project wraps the existing Laravel web system (`backend/`) in an Electron desktop shell.

## What Was Replicated

- Original web application source was copied into `backend/` from `Aeternitas-System-V2`
- Excluded during copy: `vendor/`, `node_modules/`, `.git/`, runtime logs/cache, and `.env`
- Desktop launcher files were added at project root (`main.js`, `package.json`)

## Architecture

- `Electron` creates a native desktop window.
- On startup, Electron runs `php artisan serve` inside `backend/`.
- The desktop window loads `http://127.0.0.1:8000`.

## Prerequisites

Before you begin, ensure you have the following software installed on your new device:

1.  **Git**: For cloning the project from your version control system.
2.  **XAMPP**: This provides Apache, MySQL (MariaDB), and PHP. You can download it from the [Apache Friends website](https://www.apachefriends.org/index.html).
3.  **Composer**: The dependency manager for PHP. You can download it from [getcomposer.org](https://getcomposer.org/download/).
4.  **Node.js and npm**: Required for the Electron application. You can download it from [nodejs.org](https://nodejs.org/).

## Setup and Installation

### Step 1: Set Up the Database

1.  **Start XAMPP**: Open the XAMPP Control Panel and start the **Apache** and **MySQL** services.
2.  **Create the Database**:
    *   Open your web browser and navigate to `http://localhost/phpmyadmin/`.
    *   Click on the **Databases** tab.
    *   In the "Create database" field, enter `payrolllaravel` and click **Create**.

### Step 2: Set Up the Laravel Backend

1.  **Get Project Files**: Clone or copy the entire project folder to your new device.
2.  **Open a Terminal**: Open a terminal and navigate into the backend directory:
    ```powershell
    cd path\to\Aeternitas-Desktop app\backend
    ```
3.  **Configure Environment**: Your `backend/.env` file should already be configured. Verify the database settings:
    ```ini
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=payrolllaravel
    DB_USERNAME=root
    DB_PASSWORD=
    ```
4.  **Install PHP Dependencies**:
    ```powershell
    composer install
    ```
5.  **Generate Application Key** (if not already present in `.env`):
    ```powershell
    php artisan key:generate
    ```
6.  **Run Database Migrations**:
    ```powershell
    php artisan migrate
    ```
7.  **Start the Backend Server**:
    ```powershell
    php artisan serve
    ```
    Keep this terminal open.

### Step 3: Set Up and Run the Electron App

1.  **Open a New Terminal**: Open a second terminal window.
2.  **Navigate to Project Root**:
    ```powershell
    cd path\to\Aeternitas-Desktop app
    ```
3.  **Install Node Dependencies**:
    ```powershell
    npm install
    ```
4.  **Run the Electron App**:
    ```powershell
    npm start
    ```
The Electron application window will now launch.

## Recommended Next Steps For Full Desktop Product

1. Replace `php artisan serve` with embedded production web server (`php -S` or Laravel Octane) and add startup health checks.
2. Bundle a private PHP runtime and required extensions so users do not need system PHP.
3. Bundle local database option (SQLite by default) or packaged MySQL service if needed.
4. Add `electron-builder` for `.exe` installer generation and app icon/signing.
5. Implement auto-updates and migration scripts for offline-first installs.

## Notes

- This is the fastest safe path: keep your proven Laravel codebase unchanged while shipping as desktop.
- If you want true native UI in the long term, that is a separate rewrite project, not a conversion.