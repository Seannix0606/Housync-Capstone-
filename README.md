# Housync

A comprehensive, modern Laravel 12 property management system tailored specifically for the Philippine rental market. Housync streamlines the relationship between landlords, tenants, and staff while offering robust management tools and secure access control.

## Project Description & Features

Housync offers a robust suite of tools that replace traditional paper-and-pen property management with a modern digital solution. It solves common problems such as tracking payments, communicating securely, managing maintenance requests, and verifying identities securely.

**Core Feature List:**
* **Multi-Role Authentication:** Dedicated dashboards and functionality for Super Admins, Landlords, Tenants, and Staff.
* **Property & Unit Management:** Easily add buildings, properties, and individual rentable units. Set rent amounts, amenities, leasing types, and occupancy limits.
* **Billing & Payments:** Generate bills, track payments, and allow tenants to upload payment proof. Support for status tracking (Pending, Paid, Overdue).
* **Maintenance Request Tracking:** Tenants can report issues, and landlords can assign staff to fulfill these maintenance requests, complete with status updates.
* **Real-time Chat:** Communication is made easy with real-time chat powered by Laravel broadcasting, keeping tenants and landlords in touch instantly.
* **Hardware Integration (ESP32 RFID):** Integrated physical access control! Secure properties using an ESP32 device configured to scan RFID cards, managed and validated by a robust local CLI script (`tools/esp32/ESP32Reader.php`).

---

## System Requirements

Before you begin, ensure your local development environment meets the following requirements:
* **PHP:** 8.2 or higher
* **Node.js:** 18.x or higher
* **Composer:** 2.x
* **Database:** MySQL (recommended) or SQLite

---

## Step-by-Step Local Setup

Follow these steps to get your Housync development environment up and running:

1. **Clone the Repository**
   ```bash
   git clone https://github.com/your-username/Housync-Capstone-.git
   cd Housync-Capstone-
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript Dependencies**
   ```bash
   npm install
   ```

4. **Setup Environment Configuration**
   ```bash
   cp .env.example .env
   ```

5. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

6. **Configure Database**
   By default, the application is set to use SQLite for easy local development. Ensure `DB_CONNECTION=sqlite` in your `.env`. Create an empty SQLite file if it doesn't exist:
   ```bash
   touch database/database.sqlite
   ```
   *(If you prefer MySQL, update the `DB_*` keys in `.env` to match your local MySQL configuration).*

7. **Run Database Migrations and Seeders**
   ```bash
   php artisan migrate --seed
   ```

8. **Build Frontend Assets**
   ```bash
   npm run dev
   ```

9. **Start the Development Server**
   ```bash
   php artisan serve
   ```
   *Your application will now be accessible at [http://localhost:8000](http://localhost:8000).*

---

## Environment Variables (`.env`)

Housync uses several environment variables to configure its core features and external integrations. Below is an explanation of the primary `.env` variables required:

### Application Settings
* `APP_NAME`: The name of your application (e.g., Housync).
* `APP_ENV`: Your current environment (e.g., `local` for development, `production` for live).
* `APP_KEY`: Application encryption key generated via `php artisan key:generate`.
* `APP_DEBUG`: Set to `true` to show detailed error pages (should be `false` in production).
* `APP_URL`: The base URL of your application (e.g., `http://localhost:8000`).

### Database
* `DB_CONNECTION`: Connection driver (`sqlite` or `mysql`).
* *(MySQL only)* `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: Standard relational database credentials.

### Broadcasting & Queues
* `BROADCAST_CONNECTION`: Set to your broadcasting driver (e.g., `reverb` or `log`).
* `QUEUE_CONNECTION`: Defines how queued jobs are handled (e.g., `database` or `sync`).

### Supabase Storage & Services
* `SUPABASE_URL`: Your Supabase project URL (e.g., `https://xxxx.supabase.co`).
* `SUPABASE_KEY`: The public anonymous key for Supabase API requests.
* `SUPABASE_SERVICE_KEY`: The secret service role key used for privileged backend operations like file storage or bypass-RLS operations.

### ESP32 RFID Hardware Config
* `ESP32_API_KEY`: The secret authentication key used to secure communication between the physical ESP32 card reader and the Housync backend. Ensure this matches the hardcoded key on the physical device firmware.

---

## Database Seeding & User Roles

During the `php artisan migrate --seed` step, the database is populated with sample data and default user accounts. 

### Seeded Credentials:

**1. Super Admin**
Has complete oversight over the platform, approves new landlords, and manages global settings.
* **Email:** `admin@housesync.com`
* **Password:** `admin123`

**2. Landlord**
Can manage properties, units, tenants, billing, and view maintenance requests.
* **Email:** `landlord@example.com`
* **Password:** `password`

**3. Tenant**
Can browse properties, pay rent, submit proof of payments, open maintenance tickets, and chat with the landlord.
* **Email:** `tenant1@example.com` (or `tenant2@example.com`)
* **Password:** `password`

**4. Staff**
Created and assigned by Landlords to handle maintenance operations and unit repairs.
* *(Staff accounts are typically created dynamically via the Landlord dashboard rather than pre-seeded).*

---

## Running the ESP32 RFID Reader Script

Housync includes a CLI script that acts as the bridge between a physical ESP32 RFID scanner and the web application. 

### How to run the script:
Navigate to the root directory of your project and run the script located at `tools/esp32/ESP32Reader.php`.

```bash
php tools/esp32/ESP32Reader.php <PORT> [BAUD_RATE]
```

### Arguments:
1. `<PORT>` **(Required):** The serial/COM port the ESP32 device is connected to. 
   * Windows example: `COM3`, `COM4`
   * Mac/Linux example: `/dev/ttyUSB0`, `/dev/cu.usbserial-1410`
2. `[BAUD_RATE]` **(Optional):** The baud rate for the serial connection. It defaults to `115200` if not provided.

**Example execution on Windows:**
```bash
php tools/esp32/ESP32Reader.php COM3 115200
```
*The script will listen for incoming RFID card scans from the physical hardware and communicate them via the `ESP32_API_KEY` to the Housync backend.*
