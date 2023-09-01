-- SQLITE Schema for Sharp-PHP Unit Tests

CREATE TABLE user (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    login VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    salt VARCHAR(100) NOT NULL
);

-- User logs are admin, admin

INSERT INTO user (login, password, salt)
VALUES ('admin', '$2y$08$pxfA4LlzVyXRPYVZH7czvu.gQQ8BNfzRdhejln2dwB7Bv6QafwAua', 'dummySalt');

CREATE TABLE user_data (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fk_user INTEGER NOT NULL REFERENCES user(id) ON DELETE CASCADE,
    data VARCHAR(200)
);