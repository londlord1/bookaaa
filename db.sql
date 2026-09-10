CREATE DATABASE IF NOT EXISTS hotel
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel;

CREATE TABLE users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    login         VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rooms (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(50)  NOT NULL,
    price    INT UNSIGNED NOT NULL,
    image    VARCHAR(100) NOT NULL,
    features TEXT         NOT NULL          -- характеристики через "|"
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE bookings (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    room_id    INT          NOT NULL,
    first_name VARCHAR(50)  NOT NULL,
    last_name  VARCHAR(50)  NOT NULL,
    phone      VARCHAR(20)  NOT NULL,
    email      VARCHAR(100) NOT NULL,
    date_in    DATE         NOT NULL,
    date_out   DATE         NOT NULL,
    status     ENUM('new','approved') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_room FOREIGN KEY (room_id)
        REFERENCES rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO rooms (category, price, image, features) VALUES
('Стандарт', 10000, 'img/standart.png', 'Включен завтрак|Душ + Ванна'),
('Студия',    8000, 'img/studio.jpg',   'Включен завтрак, обед|Душ + Ванна|Кондиционер'),
('Люкс',     19000, 'img/lux.png',      'Включен завтрак, обед, ужин|Душ + Ванна|Кондиционер|Телевизор|Мини-бар|Вид на город'),
('Стандарт', 10000, 'img/standart.png', 'Включен завтрак|Душ + Ванна'),
('Студия',    8000, 'img/studio.jpg',   'Включен завтрак, обед|Душ + Ванна|Кондиционер'),
('Люкс',     19000, 'img/lux.png',      'Включен завтрак, обед, ужин|Душ + Ванна|Кондиционер|Телевизор|Мини-бар|Вид на город'),
('Стандарт', 10000, 'img/standart.png', 'Включен завтрак|Душ + Ванна'),
('Студия',    8000, 'img/studio.jpg',   'Включен завтрак, обед|Душ + Ванна|Кондиционер'),
('Люкс',     19000, 'img/lux.png',      'Включен завтрак, обед, ужин|Душ + Ванна|Кондиционер|Телевизор|Мини-бар|Вид на город');
