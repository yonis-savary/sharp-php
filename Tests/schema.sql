-- SQLITE Schema for Sharp-PHP Unit Tests

CREATE TABLE test_user (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    login VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    salt VARCHAR(100) NOT NULL,
    blocked BOOLEAN DEFAULT FALSE
);

-- User logs are admin, admin

INSERT INTO test_user (login, password, salt)
VALUES ('admin', '$2y$08$pxfA4LlzVyXRPYVZH7czvu.gQQ8BNfzRdhejln2dwB7Bv6QafwAua', 'dummySalt');

CREATE TABLE test_user_data (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fk_user INTEGER NOT NULL REFERENCES test_user(id) ON DELETE CASCADE,
    data VARCHAR(200)
);


CREATE TABLE test_sample_data (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    birth_year INT NOT NULL
);

INSERT INTO test_sample_data (name, birth_year)
VALUES
('Alfred', 1899),
('Francis', 1939 ),
('Martin', 1942 ),
('Quentin', 1963 ),
('Steven', 1946 );