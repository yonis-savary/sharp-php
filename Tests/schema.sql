-- SQLITE Schema for Sharp-PHP Unit Tests

CREATE TABLE user (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    login VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    salt VARCHAR(100) NOT NULL
);

INSERT INTO user (login, password, salt)
VALUES ('admin', '$2y$08$t.zEvNyj78yxcX7ZycPjdO4hAVGiaOs92liqtzIoh8dPEFk5iX9hq', 'dummySalt');

CREATE TABLE user_data (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fk_user INTEGER NOT NULL REFERENCES user(id) ON DELETE CASCADE,
    data VARCHAR(200)
);