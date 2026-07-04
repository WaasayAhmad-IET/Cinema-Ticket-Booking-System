# Cinema Ticket Booking System (CTBS)

A full-stack Cinema Ticket Booking System built with **PHP, MySQL, Bootstrap 5, HTML/CSS/JavaScript**, designed around the provided `Project_Cinema.sql` database schema (University DBMS Project — The University of Lahore).

This project fulfills **Option 1** of the original proposal: a complete, working web application that uses the existing database design (9 tables, triggers, stored procedures, views) without redesigning it.

---

## 1. Features

### Customer Side
- Home page with "Now Showing" movies
- Customer registration & login (session-based)
- Browse movies, filter by genre/date (uses `GetAvailableMovies` stored procedure)
- View movie details and showtimes
- Visual, interactive seat map for seat selection
- Book tickets (uses `UserBookSeat` stored procedure + automatically creates a `Payment` record)
- View booking history (uses `UserBookingHistory`-style query)
- Cancel bookings before the show starts (uses `UserCancelBooking` stored procedure)

### Admin Side
- Admin login (separate session/auth from customers)
- Dashboard with key stats (movies, customers, bookings, revenue)
- Movies: Add / Edit / Deactivate
- Screens: Add / Edit / Delete
- Seats: Add individually or bulk-generate rows of seats per screen
- Shows: Schedule / Edit / Deactivate, with conflict checking
- Bookings: View all bookings, cancel any booking (uses `AdminCancelBooking` stored procedure)
- Customers: View all customers and their booking counts (`CustomerStats`-style query)
- Revenue Report: Uses the `RevenueReport` view, plus a revenue-by-genre breakdown

---

## 2. Technology Stack

| Layer       | Technology                  |
|-------------|------------------------------|
| Frontend    | HTML5, CSS3, Bootstrap 5, JavaScript |
| Backend     | PHP 8 (mysqli, prepared statements) |
| Database    | MySQL / MariaDB (XAMPP)     |

No frameworks required beyond what's bundled — this runs directly on a standard **XAMPP** stack.

---

## 3. Project Structure

```
Cinema-Ticket-Booking-System/
│
├── admin/
│   ├── includes/
│   │   ├── admin_header.php
│   │   └── admin_footer.php
│   ├── login.php / logout.php
│   ├── dashboard.php
│   ├── movies.php / movie_add.php / movie_edit.php / movie_delete.php
│   ├── screens.php / screen_add.php / screen_edit.php / screen_delete.php
│   ├── seats.php / seat_delete.php
│   ├── shows.php / show_add.php / show_edit.php / show_delete.php
│   ├── bookings.php / booking_cancel.php
│   ├── customers.php
│   └── revenue.php
│
├── css/
│   └── style.css
├── js/
│   └── seats.js
├── images/                  (place any custom poster images here)
├── includes/
│   ├── db.php                (database connection)
│   ├── auth.php               (session/auth helpers)
│   ├── header.php / footer.php (customer layout)
│
├── database/
│   └── Project_Cinema.sql     (your original schema, triggers, procedures, views, sample data)
│
├── index.php
├── login.php / register.php / logout.php
├── movies.php / movie_details.php
├── seats.php / book.php / booking_confirmation.php
├── history.php / cancel_booking.php
└── README.md
```

---

## 4. Setup Instructions (XAMPP)

1. **Install XAMPP** (Apache + MySQL + PHP 8+) if you haven't already.
2. Copy the `Cinema-Ticket-Booking-System` folder into your `htdocs` directory:
   ```
   C:\xampp\htdocs\Cinema-Ticket-Booking-System   (Windows)
   /Applications/XAMPP/htdocs/Cinema-Ticket-Booking-System  (Mac)
   ```
3. Start **Apache** and **MySQL** from the XAMPP control panel.
4. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
5. Click **Import**, choose `database/Project_Cinema.sql`, and run the import.
   This will create the `CTBS` database with all 9 tables, the trigger, stored procedures, views, and sample data.
6. Check `includes/db.php` and update `DB_USER` / `DB_PASS` if your MySQL setup differs from the XAMPP default (`root` / empty password).
7. Visit the app in your browser:
   - Customer site: `http://localhost/Cinema-Ticket-Booking-System/`
   - Admin panel: `http://localhost/Cinema-Ticket-Booking-System/admin/login.php`

---

## 5. Demo Credentials

The SQL file ships with sample data using **plain-text** passwords for demo purposes. The login system supports both legacy plain-text matching (for this sample data) and proper bcrypt hashing (for any new accounts created through `register.php`, which always hashes passwords with `password_hash()`).

**Admin login:**
- Username: `admin1`
- Password: `admin123`

**Sample customer logins** (password is `123` for all):
- `abdullah@gmail.com`
- `ali@gmail.com`
- `sara@gmail.com`
- `hassan@gmail.com`

> ⚠️ For a production system, you should re-hash these sample passwords with `password_hash()` before going live. The app already does this automatically for any *newly registered* customer.

---

## 6. Database Objects Used

| Type | Name | Used In |
|------|------|---------|
| Trigger | `PreventDoubleSeatBooking` | Fires automatically on every booking (`book.php`) |
| Procedure | `UserBookSeat` | `book.php` |
| Procedure | `UserCancelBooking` | `cancel_booking.php` |
| Procedure | `GetAvailableMovies` | `movies.php` |
| Procedure | `AdminCancelBooking` | `admin/booking_cancel.php` |
| View | `RevenueReport` | `admin/revenue.php` |
| View | `AvailableSeats` (conceptually re-queried) | `seats.php` |
| View | `CustomerStats` (conceptually re-queried) | `admin/customers.php` |
| View | `UserBookingHistory` (conceptually re-queried) | `history.php` |
| View | `AdminBookingHistory` (conceptually re-queried) | `admin/bookings.php` |

> Note: A few pages re-implement the *logic* of a view as an inline query (joined to `booking_id`/`customer_id`) so that action buttons (cancel, etc.) have the IDs they need — MySQL views can't easily be filtered/joined back to a primary key that wasn't selected. The original views remain fully intact and queryable directly (e.g. via phpMyAdmin) exactly as defined in `Project_Cinema.sql`.

---

## 7. Notes on Design Decisions

- **`UserBookSeat` creates one `Booking` row per seat** (as written in the stored procedure). When a customer selects multiple seats in one checkout, the app calls the procedure once per seat, producing multiple booking IDs that are shown together on one confirmation page — this preserves the stored procedure exactly as provided rather than rewriting it.
- **Soft deletes** are used for Movies and Shows (`is_active = FALSE`) to preserve referential integrity with existing bookings, matching the `is_active` columns already present in your schema.
- **Bulk seat generation** in the admin panel is a convenience layer on top of the `Seat` table — it does not modify the schema.

---

## 8. Credits

Based on the original project report **"Cinema Ticketing Management System"** — The University of Lahore, Section I — by Abdullah Sohail, Waasay Ahmad, Muhammad Shameel, and Shayyan Mughal.
