<?php

namespace Sharp\Classes\Security;

use InvalidArgumentException;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Core\EventListener;
use Sharp\Classes\Core\Logger;
use Sharp\Classes\Env\Configuration;
use Sharp\Classes\Env\Session;
use Sharp\Core\Utils;
use Sharp\Classes\Data\Model;
use Sharp\Classes\Events\AuthenticatedUser;

class Authentication
{
    use Component, Configurable;

    const ATTEMPTS_NUMBER = "sharp.authentication.failed-attempt-number";
    const SESSION_EXPIRE_TIME = "sharp.authentication.session-expire-time";
    const USER_DATA = "sharp.authentication.user_data";
    const IS_LOGGED = "sharp.authentication.is_logged";

    public readonly string $model;
    public readonly string $loginField;
    public readonly string $passwordField;
    public readonly ?string $saltField;

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

    public function __construct(Session $session=null, Configuration $config=null)
    {
        $this->session = $session ?? Session::getInstance();

        $this->loadConfiguration($config);

        $model         = $this->model         = $this->configuration["model"];
        $loginField    = $this->loginField    = $this->configuration["login-field"];
        $passwordField = $this->passwordField = $this->configuration["password-field"];
        $saltField     = $this->saltField     = $this->configuration["salt-field"];

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

        $expireTime = $this->session->get(self::SESSION_EXPIRE_TIME);

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
            self::IS_LOGGED => true,
            self::USER_DATA => $userData,
            self::ATTEMPTS_NUMBER => 0,
        ]);
        $this->refreshExpireTime();

        EventListener::getInstance()->dispatch(new AuthenticatedUser($userData, $this->model));
    }

    protected function failAttempt(): bool
    {
        $this->logout();
        $this->session->edit(self::ATTEMPTS_NUMBER, fn($x=0) => $x+1);

        return false;
    }

    protected function refreshExpireTime(): void
    {
        $sessionDuration = intval($this->configuration["session-duration"]);
        $this->session->set(self::SESSION_EXPIRE_TIME, time() + $sessionDuration);
    }

    public function logout(): void
    {
        $this->session->unset(
            self::IS_LOGGED,
            self::USER_DATA
        );
    }

    public function isLogged(): bool
    {
        return boolval($this->session->get(self::IS_LOGGED, false));
    }

    public function attemptNumber(): int
    {
        return $this->session->get(self::ATTEMPTS_NUMBER, 0);
    }

    public function getUser(): ?array
    {
        return $this->session->get(self::USER_DATA);
    }

    /**
     * Return a new instance of the Authentication model with pre-filled values
     *
     * @param string $login Value for the login field
     * @param string $password Value for the password field
     * @param string $salt Value for the salt field (If the salt field is configured and value not provided, a random 32 characters HEX string is generated)
     * @param string $algo `password_hash` algorithm to use
     * @param array $options array option for `password_hash`
     * @return \Sharp\Classes\Data\Model
     */
    public function createUser(
        string $login,
        string $password,
        string $salt=null,
        string $algo=PASSWORD_BCRYPT,
        array $options=["cost" => 8]
    )
    {
        if ((!$salt) && $this->saltField)
            $salt = bin2hex(random_bytes(16));

        if ($salt && (!$this->saltField))
            Logger::getInstance()->warning("[salt] parameter used, no salt field configured");

        $model = $this->model;

        $password = $password . $salt;
        $password = password_hash($password, $algo, $options);

        $instance = new $model([
            $this->loginField => $login,
            $this->passwordField => $password
        ]);

        if ($saltField = $this->saltField)
            $instance->$saltField = $salt;

        return $instance;
    }
}