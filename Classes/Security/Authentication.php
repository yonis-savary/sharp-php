<?php

namespace Sharp\Classes\Security;

use InvalidArgumentException;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Core\EventListener;
use Sharp\Classes\Env\Configuration;
use Sharp\Classes\Env\Session;
use Sharp\Core\Utils;
use Sharp\Classes\Data\Model;
use Sharp\Classes\Events\AuthenticatedUser;

class Authentication
{
    use Component, Configurable;

    const ATTEMPTS_NUMBER     = "failed-attempt-number";
    const SESSION_EXPIRE_TIME = "session-expire-time";
    const USER_DATA           = "user-data";
    const IS_LOGGED           = "is-logged";

    public readonly string $model;
    public readonly string $loginField;
    public readonly string $passwordField;
    public readonly ?string $saltField;

    public readonly string $sessionNamespace;

    protected Session $session;

    public static function getDefaultConfiguration(): array
    {
        return [
            "model" => 'App\Models\User',
            "login-field" => "login",
            "password-field" => "password",
            "salt-field" => null,
            "session-duration" => 3600
        ];
    }

    public function sessionKey(string $key)
    {
        return "sharp.authentication." . $this->sessionNamespace . "." . $key;
    }

    public function __construct(Session $session=null, Configuration $config=null)
    {
        $this->session = $session ?? Session::getInstance();

        $this->loadConfiguration($config);

        $model         = $this->model         = $this->configuration["model"];
        $loginField    = $this->loginField    = $this->configuration["login-field"];
        $passwordField = $this->passwordField = $this->configuration["password-field"];
        $saltField     = $this->saltField     = $this->configuration["salt-field"];

        $this->sessionNamespace = $this->configuration["session-namespace"] ?? md5(
            $this->model.
            $this->loginField.
            $this->passwordField
        );

        if (!class_exists($model))
            throw new InvalidArgumentException("[$model] class does not exists");

        if (!Utils::uses($model, Model::class))
            throw new InvalidArgumentException("[$model] class must use Model trait");

        $modelFields = $model::getFieldNames();
        foreach (array_filter([$loginField, $passwordField, $saltField]) as $field)
        {
            if (!in_array($field, $modelFields))
                throw new InvalidArgumentException("[$model] does not have a [$field] field");
        }

        if (!$this->isLogged())
            return;

        $expireTime = $this->session->get($this->sessionKey(self::SESSION_EXPIRE_TIME));

        if (time() >= $expireTime)
            $this->logout();
        else
            $this->refreshExpireTime();
    }

    public function attempt(string $login,string $password): bool
    {
        $model = $this->model;

        if (!($user = $model::select()->where($this->loginField, $login)->first()))
            return $this->failAttempt();

        $hash = $user["data"][$this->passwordField];
        if ($this->saltField)
            $password .= $user["data"][$this->saltField];

        if (!password_verify($password, $hash))
            return $this->failAttempt();

        $this->login($user);

        return true;
    }

    /**
     * Directly login a user and set its data
     * @param array $userData Data of the user, can be retrieved with `getUser()`
     */
    public function login(array $userData): void
    {
        $this->session->merge([
            $this->sessionKey(self::IS_LOGGED) => true,
            $this->sessionKey(self::USER_DATA) => $userData,
            $this->sessionKey(self::ATTEMPTS_NUMBER) => 0,
        ]);
        $this->refreshExpireTime();

        EventListener::getInstance()->dispatch(new AuthenticatedUser(
            $userData,
            $this->model,
            $this->loginField,
            $this->passwordField,
            $this->saltField
        ));
    }

    protected function failAttempt(): bool
    {
        $this->logout();
        $this->session->edit(
            $this->sessionKey(self::ATTEMPTS_NUMBER),
            fn($x=0) => $x+1
        );

        return false;
    }

    protected function refreshExpireTime(): void
    {
        $sessionDuration = intval($this->configuration["session-duration"]);
        $this->session->set(
            $this->sessionKey(self::SESSION_EXPIRE_TIME),
            time() + $sessionDuration
        );
    }

    public function logout(): void
    {
        $this->session->unset(
            $this->sessionKey(self::IS_LOGGED),
            $this->sessionKey(self::USER_DATA)
        );
    }

    public function isLogged(): bool
    {
        return boolval($this->session->get($this->sessionKey(self::IS_LOGGED), false));
    }

    public function attemptNumber(): int
    {
        return $this->session->get($this->sessionKey(self::ATTEMPTS_NUMBER), 0);
    }

    public function getUser(): ?array
    {
        return $this->session->get($this->sessionKey(self::USER_DATA));
    }
}