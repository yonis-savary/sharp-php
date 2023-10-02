<?php

namespace Sharp\Classes\Extras;

use Sharp\Classes\Env\Session;

trait SessionStraw
{
    public static function getDefaultValue(): mixed
    {
        return false;
    }

    final protected static function getKey(): string
    {
        return "sharp.session-straw." . self::class;
    }

    final public static function set(mixed $value): void
    {
        Session::getInstance()->set(self::getKey(), $value);
    }

    final public static function get(): mixed
    {
        return Session::getInstance()->get(self::getKey(), self::getDefaultValue());
    }

    final public static function unset(): void
    {
        Session::getInstance()->unset(self::getKey());
    }

}