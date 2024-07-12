<?php

use Sharp\Classes\Security\Authentication;

function authIsLogged(): bool
{
    return Authentication::getInstance()->isLogged();
}

function authGetUser(): array
{
    return Authentication::getInstance()->getUser();
}

function authAttempt(string $login, string $password): bool
{
    return Authentication::getInstance()->attempt($login, $password);
}

function authLogout(): void
{
    Authentication::getInstance()->logout();
}