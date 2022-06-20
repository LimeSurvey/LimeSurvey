<?php

namespace App\Auth;

use Yiisoft\Auth\IdentityInterface;

class Token implements IdentityInterface
{
    public int    $userId;
    public string $userName;
    public string $userFullName;
    public string $adminLang;

    public function __construct()
    {
        $this->userId       = 0;
        $this->userName     = "";
        $this->userFullName = "";
        $this->adminLang    = "";
    }

    public static function fromSession(array $data): Token
    {
        $token = new Token();
        $token->userId       = $data["loginID"];
        $token->userName     = $data["user"];
        $token->userFullName = $data["full_name"];
        $token->adminLang    = $data["adminlang"];

        return $token;
    }

    /**
     * returns joomla user id. function needs to be called getId() because of IdentityInterface
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->userId == 0 ? null : (string)$this->userId;
    }

    /**
     * @return string|null
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @return string|null
     */
    public function getUserFullName(): string
    {
        return $this->userFullName;
    }

    /**
     * @return string|null
     */
    public function getAdminLang(): string
    {
        return $this->adminLang;
    }
}
