DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS support_tickets;

CREATE TABLE bookings (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  order_id    VARCHAR(20) NOT NULL UNIQUE,
  total       DECIMAL(10,2) NOT NULL,
  method      ENUM('card','upi') NOT NULL,
  identifier  VARCHAR(60) NOT NULL,
  status      ENUM('pending','paid','failed') DEFAULT 'paid'
);

CREATE TABLE support_tickets (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(80) NOT NULL,
  mobile    CHAR(10) NOT NULL,
  order_id  VARCHAR(20) NULL,
  message   TEXT NOT NULL
);
