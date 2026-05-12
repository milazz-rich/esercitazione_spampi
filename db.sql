Create DATABASE AFRA;
USE AFRA;

CREATE TABLE users (
 id INTEGER PRIMARY KEY AUTO_INCREMENT,
 username VARCHAR(50) NOT NULL UNIQUE,
 password VARCHAR(50) NOT NULL
);

CREATE TABLE movies (
 id INTEGER PRIMARY KEY AUTO_INCREMENT,
 title VARCHAR(100) NOT NULL,
 plot TEXT,
 poster VARCHAR(255),
 likes INTEGER DEFAULT 0
);

CREATE TABLE comments (
 id INTEGER PRIMARY KEY AUTO_INCREMENT,
 movie_id INTEGER,
 username VARCHAR(50),
 text TEXT,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY(movie_id) REFERENCES movies(id)
);

INSERT INTO users(username, password)
VALUES ('Riccardo', 'AFRA');

INSERT INTO users(username, password)
VALUES ('Daniele', 'AFRA');

INSERT INTO users(username, password)
VALUES ('Giuseppe', 'AFRA');

INSERT INTO users(username, password)
VALUES ('Simone', 'AFRA');

INSERT INTO movies(title, plot, poster, likes) VALUES
(
'Inception',
'Un ladro specializzato nell entrare nei sogni.',
'img/inception.jpg',
120
),
(
'Interstellar',
'Un viaggio nello spazio alla ricerca di un nuovo pianeta.',
'img/interstellar.jpg',
250
),
(
'Cars',
'Io sono velocità.',
'img/cars.jpg',
12
),
(
'Io sono leggenda',
'Io sono leggenda.',
'img/io_sono_leggenda.jpg',
250
);

