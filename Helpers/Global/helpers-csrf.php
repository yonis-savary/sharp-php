<?php

use Sharp\Classes\Security\Csrf;


function csrfToken(): string
{
    return Csrf::getInstance()->getToken();
}

function csrfInput(): string
{
    return Csrf::getInstance()->getHTMLInput();
}