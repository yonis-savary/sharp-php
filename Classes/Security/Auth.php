<?php

namespace Sharp\Classes\Security;

use InvalidArgumentException;
use Sharp\Classes\Core\Component;
use Sharp\Classes\Core\Configurable;
use Sharp\Classes\Env\Config;
use Sharp\Classes\Env\Session;
use Sharp\Core\Utils;

class Auth
{
    use Component, Configurable;

    const ATTEMPTS_NUMBER = "sharp.auth.failed-attempt-number";
    const SESSION_EXPIRE_TIME = "sharp.auth.session-expire-time";
    const USER_DATA = "sharp.auth.user_data";
    const IS_LOGGED = "sharp.auth.is_logged";

    /** @var \Sharp\CLasses\Data\Model $model */
    protected $model;
    protected string $loginField;
    protected string $passwordField;
    protected string $saltField;

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

    public function __construct(Session $session=null, Config $config=null)
    {
        $this->session = $session ?? Session::getInstance();

        $config = $this->getConfiguration();

        $model         = $this->model         = $config["model"];
        $loginField    = $this->loginField    = $config["login-field"];
        $passwordField = $this->passwordField = $config["password-field"];
        $saltField     = $this->saltField     = $config["salt-field"];

        if (!class_exists($model))
            throw new InvalidArgumentException("[$model] class does not exists");

        if (!Utils::uses($model, 'Sharp\Classes\Data\Model'))
            throw new InvalidArgumentException("[$model] class must use Model trait");

        $fields = $model::getFieldNames();

        foreach (array_filter([$loginField, $passwordField, $saltField]) as $field)
        {
            if (!in_array($field, $fields))
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
        if ($this->passwordField)
            $password .= $user["data"][$this->saltField];

        if (!password_verify($password, $hash))
            return $this->failAttempt();

        $session  = $this->session;
        $session->set(self::IS_LOGGED, true);
        $session->set(self::USER_DATA, $user);
        $session->set(self::ATTEMPTS_NUMBER, 0);
        $this->refreshExpireTime();

        return true;
    }

    protected function failAttempt(): bool
    {
        $this->logout();

        $session = $this->session;
        $session->set(self::ATTEMPTS_NUMBER, $session->get(self::ATTEMPTS_NUMBER, 0) + 1);

        return false;
    }

    protected function refreshExpireTime(): void
    {
        $sessionDuration = intval($this->configuration["session-duration"]);
        $this->session->set(self::SESSION_EXPIRE_TIME, time() + $sessionDuration);
    }

    public function logout(): void
    {
        $session = $this->session;
        $session->set(self::IS_LOGGED, false);
        $session->set(self::USER_DATA, null);
    }

    public function isLogged(): bool
    {
        return boolval($this->session->get(self::IS_LOGGED));
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
     * @return \Sharp\Classes\Data\Model
     */
    public function createUser(string $login, string $password, string $salt=null, string $algo=null, array $options=null)
    {
        $algo ??= PASSWORD_BCRYPT;
        $options ??= ["cost" => 8];

        $model = $this->model;

        $password = "$password$salt";
        $password = password_hash($password, $algo, $options);

        $instance = new $model([
            [$this->loginField] => $login,
            [$this->passwordField] => $password
        ]);

        if ($saltField = $this->saltField)
            $instance->$saltField = $salt;

        return $instance;
    }
}