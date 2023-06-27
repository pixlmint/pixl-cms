<?php

namespace PixlMint\CMS\Controllers;

use Nacho\Exceptions\PasswordInvalidException;
use Nacho\Models\HttpMethod;
use PixlMint\CMS\Helpers\AdminHelper;
use PixlMint\CMS\Models\TokenUser;
use PixlMint\CMS\Helpers\TokenHelper;
use Nacho\Controllers\AbstractController;
use Nacho\Models\Request;
use Nacho\ORM\RepositoryManager;
use Nacho\Security\UserRepository;

class AuthenticationController extends AbstractController
{
    public function login(Request $request): string
    {
        $tokenHelper = new TokenHelper();
        $username = $request->getBody()['username'];
        $password = $request->getBody()['password'];
        if (strtolower($request->requestMethod) === 'post') {
            $user = $this->nacho->userHandler->findUser($username);
            if ($this->nacho->userHandler->passwordVerify($user, $password)) {
                $token = $tokenHelper->getToken($user);
                return $this->json(['token' => $token]);
            } else {
                return $this->json(['message' => 'This password/ username is not valid'], 400);
            }
        }

        return $this->json([], 405);
    }

    public function requestNewPassword(Request $request): string
    {
        if (strtolower($request->requestMethod) !== 'post') {
            return $this->json([], 405);
        }
        $username = $_REQUEST['username'];
        $resetLink = md5(random_bytes(100));

        /** @var TokenUser $user */
        $user = $this->nacho->userHandler->findUser($username);

        if (!$user) {
            return $this->json([], 400);
        }

        $user->setResetLink($resetLink);
        RepositoryManager::getInstance()->getRepository(UserRepository::class)->set($user);

        if (!$user->getEmail()) {
            return $this->json(['message' => 'This user has no E-Mail Address'], 400);
        }

        $message = "Click <a href='" . $_SERVER['SERVER_NAME'] . '/auth/restore-password?token=' . $resetLink . "'>here</a> to set a new Password";

        $success = mail($user->getEmail(), 'Reset Password', $message);

        return $this->json(['success' => $success]);
    }

    public function restorePassword(Request $request): string
    {
        if (strtolower($request->requestMethod) !== 'post') {
            return $this->json([], 405);
        }

        $username = $_REQUEST['username'];
        $resetToken = $_REQUEST['resetToken'];
        $password1 = $_REQUEST['password1'];
        $password2 = $_REQUEST['password2'];

        /** @var TokenUser $user */
        $user = $this->nacho->userHandler->findUser($username);

        if (!$user) {
            return $this->json(['message' => "Unable to find user with username $username"], 404);
        }

        if ($password1 !== $password2) {
            return $this->json(['message' => 'The provided passwords don\'t match'], 400);
        }

        if ($resetToken !== $user->getResetLink() || $user->getResetLink() === '') {
            return $this->json(['message' => 'Invalid Reset token'], 400);
        }

        /** @var TokenUser $user */
        $user = $this->nacho->userHandler->setPasswordForUser($user, $password1);
        $tokenHelper = new TokenHelper();

        $tokenHelper->generateNewTokenStamp($user);
        $user->setResetLink('');
        $token = $tokenHelper->getToken($user);
        RepositoryManager::getInstance()->getRepository(UserRepository::class)->set($user);

        return $this->json(['token' => $token]);
    }

    public function generateNewToken(Request $request): string
    {
        if (strtolower($request->requestMethod) !== 'post') {
            return $this->json([], 405);
        }
        $username = $_REQUEST['username'];
        $token = TokenHelper::getTokenFromRequest();
        if (!$username || !$token) {
            return $this->json(['message' => 'Define Token and Username'], 400);
        }
        $tokenHelper = new TokenHelper();
        if (!$tokenHelper->isTokenValid($token, $this->nacho->userHandler->getUsers())) {
            return $this->json([], 400);
        }

        /** @var TokenUser $user */
        $user = $this->nacho->userHandler->findUser($username);

        $tokenHelper->generateNewTokenStamp($user);
        $newToken = $tokenHelper->getToken($user);
        RepositoryManager::getInstance()->getRepository(UserRepository::class)->set($user);

        return $this->json(['token' => $newToken]);
    }

    public function changePassword(): string
    {
        print_r($_REQUEST);
        /** @var TokenUser $user */
        $user = $this->nacho->userHandler->findUser($_REQUEST['username']);

        if ($_REQUEST['newPassword1'] !== $_REQUEST['newPassword2']) {
            return $this->json(['message' => 'The Passwords have to match'], 400);
        }

        try {
            $this->nacho->userHandler->changePassword($user->getUsername(), $_REQUEST['currentPassword'], $_REQUEST['newPassword1']);
        } catch (PasswordInvalidException $e) {
            return $this->json(['message' => 'Invalid Password'], 400);
        }

        $tokenHelper = new TokenHelper();
        $tokenHelper->generateNewTokenStamp($user);
        $newToken = $tokenHelper->getToken($user);

        RepositoryManager::getInstance()->getRepository(UserRepository::class)->set($user);

        print($newToken);

        return $this->json(['token' => $newToken]);
    }

    public function createAdmin(Request $request): string
    {
        if (AdminHelper::isAdminCreated()) {
            return $this->json(['message' => 'An Admin already exists'], 400);
        }

        if (strtolower($request->requestMethod) === 'get') {
            return $this->json(['message' => 'Create your admin']);
        }

        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];

        $user = new TokenUser(0, $username, 'Editor', null, null, null, null);
        $guest = new TokenUser(0, 'Guest', 'Guest', null, null, null, null);

        $tokenHelper = new TokenHelper();

        $tokenHelper->generateNewTokenStamp($user);

        RepositoryManager::getInstance()->getRepository(UserRepository::class)->set($user);
        RepositoryManager::getInstance()->getRepository(UserRepository::class)->set($guest);

        $this->nacho->userHandler->setPassword($username, $password);

        return $this->json(['adminCreated' => true]);
    }
}
