<?php

namespace PixlMint\CMS\Models;

use Nacho\Contracts\ArrayableInterface;
use Nacho\ORM\ModelInterface;
use Nacho\ORM\TemporaryModel;
use Nacho\Security\AbstractUser;
use Nacho\Security\UserInterface;

class TokenUser extends AbstractUser implements UserInterface, ModelInterface, ArrayableInterface
{
    private ?string $tokenStamp = '';
    private ?string $resetLink = null;
    private ?string $email = null;
    private ?string $firstname = null;
    private ?string $lastname = null;

    public static function init(TemporaryModel $data, int $id): ModelInterface
    {
        return new TokenUser($id, $data->get('username'), $data->get('role'), $data->get('tokenStamp'), $data->get('password'), $data->get('resetLink'), $data->get('email'), $data->get('firstname'), $data->get('lastname'));
    }

    public function __construct(int $id, string $username, string $role, ?string $tokenStamp, ?string $password, ?string $resetLink, ?string $email, ?string $firstname, ?string $lastname)
    {
        parent::__construct($id, $username, $role, $password);
        $this->tokenStamp = $tokenStamp;
        $this->resetLink = $resetLink;
        $this->email = $email;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
    }

    public function getResetLink(): ?string
    {
        return $this->resetLink;
    }

    public function setResetLink(?string $resetLink): void
    {
        $this->resetLink = $resetLink;
    }

    public function getTokenStamp(): string
    {
        return $this->tokenStamp;
    }

    public function setTokenStamp(string $tokenStamp): void
    {
        $this->tokenStamp = $tokenStamp;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): void
    {
        $this->firstname = $firstname;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): void
    {
        $this->lastname = $lastname;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'resetLink' => $this->resetLink,
            'tokenStamp' => $this->tokenStamp,
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
        ]);
    }

}