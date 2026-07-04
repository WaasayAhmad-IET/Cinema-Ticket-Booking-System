-- =============================
-- CINEMA TICKET BOOKING SYSTEM 
-- =============================

DROP DATABASE IF EXISTS CTBS;
CREATE DATABASE CTBS;
USE CTBS;

-- ============================================
-- 1. ADMIN TABLE
-- ============================================
CREATE TABLE Admin (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE,
    password_hash VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- 2. CUSTOMER TABLE
-- ============================================
CREATE TABLE Customer (
    customer_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(15),
    password_hash VARCHAR(255),
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- ============================================
-- 3. MOVIE TABLE
-- ============================================
CREATE TABLE Movie (
    movie_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200),
    genre VARCHAR(100),
    duration_minutes INT,
    release_date DATE,
    director VARCHAR(100),
    rating DECIMAL(3,1),
    is_active BOOLEAN DEFAULT TRUE
);

-- ============================================
-- 4. SCREEN TABLE
-- ============================================
CREATE TABLE Screen (
    screen_id INT PRIMARY KEY AUTO_INCREMENT,
    screen_name VARCHAR(50),
    capacity INT,
    screen_type ENUM('Standard','IMAX','VIP') DEFAULT 'Standard'
);

-- ============================================
-- 5. SEAT TABLE
-- ============================================
CREATE TABLE Seat (
    seat_id INT PRIMARY KEY AUTO_INCREMENT,
    screen_id INT,
    seat_row CHAR(1),
    seat_number INT,
    seat_type ENUM('Standard','Premium','VIP') DEFAULT 'Standard',
    price_multiplier DECIMAL(3,2) DEFAULT 1.00,
    FOREIGN KEY (screen_id) REFERENCES Screen(screen_id),
    UNIQUE (screen_id, seat_row, seat_number)
);

-- ============================================
-- 6. SHOWS TABLE
-- ============================================
CREATE TABLE Shows (
    show_id INT PRIMARY KEY AUTO_INCREMENT,
    movie_id INT,
    screen_id INT,
    show_date DATE,
    start_time TIME,
    base_price DECIMAL(10,2),
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (movie_id) REFERENCES Movie(movie_id),
    FOREIGN KEY (screen_id) REFERENCES Screen(screen_id)
);

-- ============================================
-- 7. BOOKING TABLE
-- ============================================
CREATE TABLE Booking (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    show_id INT,
    booking_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Confirmed','Cancelled') DEFAULT 'Confirmed',
    FOREIGN KEY (customer_id) REFERENCES Customer(customer_id),
    FOREIGN KEY (show_id) REFERENCES Shows(show_id)
);

-- ============================================
-- 8. SEAT BOOKING TABLE
-- ============================================
CREATE TABLE SeatBooking (
    seat_booking_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT,
    seat_id INT,
    price DECIMAL(10,2),
    FOREIGN KEY (booking_id) REFERENCES Booking(booking_id),
    FOREIGN KEY (seat_id) REFERENCES Seat(seat_id)
);

-- ============================================
-- 9. PAYMENT TABLE
-- ============================================
CREATE TABLE Payment (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT,
    amount DECIMAL(10,2),
    payment_method ENUM('Card','Cash','Mobile'),
    payment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Success','Failed'),
    FOREIGN KEY (booking_id) REFERENCES Booking(booking_id)
);

-- ============================================
-- 10. TRIGGER: PREVENT DOUBLE SEAT BOOKING
-- ============================================
DELIMITER $$
CREATE TRIGGER PreventDoubleSeatBooking
BEFORE INSERT ON SeatBooking
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1
        FROM SeatBooking sb
        JOIN Booking b ON sb.booking_id = b.booking_id
        WHERE sb.seat_id = NEW.seat_id
        AND b.show_id = (
            SELECT show_id FROM Booking WHERE booking_id = NEW.booking_id
        )
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Seat already booked for this show';
    END IF;
END$$
DELIMITER ;

-- ============================================
-- 11. USER STORED PROCEDURES
-- ============================================

-- Book a seat
DELIMITER $$
CREATE PROCEDURE UserBookSeat(
    IN p_customer_id INT,
    IN p_show_id INT,
    IN p_seat_id INT
)
BEGIN
    DECLARE b_id INT;
    INSERT INTO Booking (customer_id, show_id)
    VALUES (p_customer_id, p_show_id);
    SET b_id = LAST_INSERT_ID();
    INSERT INTO SeatBooking (booking_id, seat_id, price)
    SELECT b_id, p_seat_id, s.price_multiplier * sh.base_price
    FROM Seat s
    JOIN Shows sh ON sh.show_id = p_show_id
    WHERE s.seat_id = p_seat_id;
END$$
DELIMITER ;

-- Cancel own booking before show starts
DELIMITER $$
CREATE PROCEDURE UserCancelBooking(
    IN p_booking_id INT,
    IN p_customer_id INT
)
BEGIN
    DECLARE showTime DATETIME;
    SELECT CONCAT(sh.show_date,' ',sh.start_time) INTO showTime
    FROM Booking b
    JOIN Shows sh ON b.show_id = sh.show_id
    WHERE b.booking_id = p_booking_id AND b.customer_id = p_customer_id;
    
    IF showTime > NOW() THEN
        UPDATE Booking SET status='Cancelled'
        WHERE booking_id=p_booking_id AND customer_id=p_customer_id;
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Cannot cancel after show started';
    END IF;
END$$
DELIMITER ;

-- View movies by genre/date
DELIMITER $$
CREATE PROCEDURE GetAvailableMovies(
    IN p_genre VARCHAR(100),
    IN p_date DATE
)
BEGIN
    SELECT m.movie_id, m.title, m.genre, sh.show_date, sh.start_time, sh.base_price, sc.screen_name
    FROM Movie m
    JOIN Shows sh ON m.movie_id = sh.movie_id
    JOIN Screen sc ON sh.screen_id = sc.screen_id
    WHERE (p_genre IS NULL OR m.genre=p_genre)
      AND (p_date IS NULL OR sh.show_date=p_date)
      AND sh.is_active=TRUE;
END$$
DELIMITER ;

-- ============================================
-- 12. ADMIN STORED PROCEDURES
-- ============================================

-- Cancel any booking
DELIMITER $$
CREATE PROCEDURE AdminCancelBooking(IN p_booking_id INT)
BEGIN
    UPDATE Booking SET status='Cancelled' WHERE booking_id=p_booking_id;
END$$
DELIMITER ;

-- ============================================
-- 13. VIEWS
-- ============================================

-- User booking history
CREATE OR REPLACE VIEW UserBookingHistory AS
SELECT c.customer_id, c.first_name, c.last_name,
       m.title, sh.show_date, sh.start_time,
       CONCAT(s.seat_row,s.seat_number) AS seat_number,
       sb.price, b.status
FROM Booking b
JOIN Customer c ON b.customer_id=c.customer_id
JOIN Shows sh ON b.show_id=sh.show_id
JOIN Movie m ON sh.movie_id=m.movie_id
JOIN SeatBooking sb ON sb.booking_id=b.booking_id
JOIN Seat s ON s.seat_id=sb.seat_id;

-- Admin booking history
CREATE OR REPLACE VIEW AdminBookingHistory AS
SELECT b.booking_id, c.first_name, c.last_name,
       m.title, sh.show_date, sh.start_time, b.status
FROM Booking b
JOIN Customer c ON b.customer_id=c.customer_id
JOIN Shows sh ON b.show_id=sh.show_id
JOIN Movie m ON sh.movie_id=m.movie_id;

-- Available seats
CREATE OR REPLACE VIEW AvailableSeats AS
SELECT sh.show_id, s.seat_id, s.seat_row, s.seat_number, s.seat_type
FROM Seat s
JOIN Shows sh ON s.screen_id=sh.screen_id
WHERE s.seat_id NOT IN (
    SELECT sb.seat_id
    FROM SeatBooking sb
    JOIN Booking b ON sb.booking_id=b.booking_id
    WHERE b.show_id=sh.show_id
);

-- Customer stats
CREATE OR REPLACE VIEW CustomerStats AS
SELECT c.customer_id, c.first_name, c.last_name, COUNT(b.booking_id) AS total_bookings
FROM Customer c
LEFT JOIN Booking b ON c.customer_id=b.customer_id
GROUP BY c.customer_id;

-- Revenue report
CREATE OR REPLACE VIEW RevenueReport AS
SELECT m.title, sh.show_date, SUM(p.amount) AS total_revenue
FROM Payment p
JOIN Booking b ON p.booking_id=b.booking_id
JOIN Shows sh ON b.show_id=sh.show_id
JOIN Movie m ON sh.movie_id=m.movie_id
WHERE p.status='Success'
GROUP BY m.title, sh.show_date;

-- ============================================
-- 14. SAMPLE DATA
-- ============================================

-- Admin
INSERT INTO Admin(username,password_hash) VALUES ('admin1','admin123');

-- Customers
INSERT INTO Customer(first_name,last_name,email,phone,password_hash)
VALUES
('Abdullah','Sohail','abdullah@gmail.com','03001234567','123'),
('Ali','Khan','ali@gmail.com','03007654321','123'),
('Sara','Ahmed','sara@gmail.com','03009876543','123'),
('Hassan','Raza','hassan@gmail.com','03001112233','123');

-- Movies
INSERT INTO Movie(title,genre,duration_minutes,release_date,director,rating)
VALUES
('Inception','Sci-Fi',148,'2010-07-16','Christopher Nolan',8.8),
('The Matrix','Sci-Fi',136,'1999-03-31','Wachowski Brothers',8.7),
('Avengers: Endgame','Action',181,'2019-04-26','Anthony & Joe Russo',8.4),
('Parasite','Thriller',132,'2019-05-30','Bong Joon-ho',8.6);

-- Screens
INSERT INTO Screen(screen_name,capacity,screen_type)
VALUES ('Screen 1',50,'Standard'),('Screen 2',70,'IMAX'),('Screen 3',40,'VIP');

-- Seats (simplified)
INSERT INTO Seat(screen_id,seat_row,seat_number)
VALUES (1,'A',1),(1,'A',2),(1,'A',3),(2,'A',1),(2,'A',2),(2,'A',3),(3,'A',1),(3,'A',2);

-- Shows
INSERT INTO Shows(movie_id,screen_id,show_date,start_time,base_price)
VALUES (1,1,'2025-01-01','18:00:00',500),
       (2,2,'2025-01-01','20:00:00',600),
       (3,1,'2025-01-02','18:30:00',550),
       (4,3,'2025-01-03','19:00:00',650);

-- Payments for bookings
INSERT INTO Payment(booking_id, amount, payment_method, status)
VALUES 
(1, 500, 'Card', 'Success'),
(2, 600, 'Cash', 'Success'),
(3, 550, 'Mobile', 'Success');

-- ============================================
-- 15. TESTING
-- ============================================

-- Bookings
CALL UserBookSeat(1,1,1);
CALL UserBookSeat(2,2,5);
CALL UserBookSeat(3,1,2);

-- Cancel booking
CALL UserCancelBooking(3,3); -- Sara cancels her booking

-- Admin cancels
CALL AdminCancelBooking(1);

-- Views to check
SELECT * FROM AvailableSeats;
SELECT * FROM UserBookingHistory;
SELECT * FROM AdminBookingHistory;
SELECT * FROM CustomerStats;
SELECT * FROM RevenueReport;