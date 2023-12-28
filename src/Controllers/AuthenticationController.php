<?php

namespace PixlMint\CMS\Controllers;

use Nacho\Contracts\RequestInterface;
use Nacho\Contracts\UserHandlerInterface;
use Nacho\Exceptions\PasswordInvalidException;
use Nacho\Models\HttpResponse;
use PixlMint\CMS\Helpers\CustomUserHelper;
use PixlMint\CMS\Helpers\AdminHelper;
use PixlMint\CMS\Models\TokenUser;
use PixlMint\CMS\Helpers\TokenHelper;
use Nacho\Controllers\AbstractController;
use Nacho\Models\Request;
use Nacho\ORM\RepositoryManager;
use Nacho\Security\UserRepository;

class AuthenticationController extends AbstractController
{
    private UserHandlerInterface $userHandler;
    private UserRepository $userRepository;
    private TokenHelper $tokenHelper;

    public function __construct(UserHandlerInterface $userHandler, UserRepository $userRepository, TokenHelper $tokenHelper)
    {
        parent::__construct();
        $this->userHandler = $userHandler;
        $this->userRepository = $userRepository;
        $this->tokenHelper = $tokenHelper;
    }

    public function login(RequestInterface $request): HttpResponse
    {
        $username = $request->getBody()['username'];
        $password = $request->getBody()['password'];
        if (strtolower($request->requestMethod) === 'post') {
            $user = $this->userHandler->findUser($username);
            if ($this->userHandler->passwordVerify($user, $password)) {
                $token = $this->tokenHelper->getToken($user);
                return $this->json(['token' => $token]);
            } else {
                return $this->json(['message' => 'This password/ username is not valid'], 400);
            }
        }

        return $this->json([], 405);
    }

    public function requestNewPassword(RequestInterface $request): HttpResponse
    {
        if (strtolower($request->requestMethod) !== 'post') {
            return $this->json([], 405);
        }
        $username = $_REQUEST['username'];
        $resetLink = md5(random_bytes(100));

        /** @var TokenUser $user */
        $user = $this->userHandler->findUser($username);

        if (!$user) {
            return $this->json([], 400);
        }

        $user->setResetLink($resetLink);
        $this->userRepository->set($user);

        if (!$user->getEmail()) {
            return $this->json(['message' => 'This user has no E-Mail Address'], 400);
        }

        $message = "Click <a href='" . $_SERVER['SERVER_NAME'] . '/auth/restore-password?token=' . $resetLink . "'>here</a> to set a new Password";

        $success = mail($user->getEmail(), 'Reset Password', $message);

        return $this->json(['success' => $success]);
    }

    public function restorePassword(RequestInterface $request): HttpResponse
    {
        if (strtolower($request->requestMethod) !== 'post') {
            return $this->json([], 405);
        }

        $username = $_REQUEST['username'];
        $resetToken = $_REQUEST['resetToken'];
        $password1 = $_REQUEST['password1'];
        $password2 = $_REQUEST['password2'];

        /** @var TokenUser $user */
        $user = $this->userHandler->findUser($username);

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
        $user = $this->userHandler->setPasswordForUser($user, $password1);

        $this->tokenHelper->generateNewTokenStamp($user);
        $user->setResetLink('');
        $token = $this->tokenHelper->getToken($user);
        $this->userRepository->set($user);

        return $this->json(['token' => $token]);
    }

    public function generateNewToken(RequestInterface $request): HttpResponse
    {
        if (strtolower($request->requestMethod) !== 'post') {
            return $this->json([], 405);
        }
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }

        /** @var TokenUser $user */
        $user = $this->nacho->userHandler->getCurrentUser();

        $this->tokenHelper->generateNewTokenStamp($user);
        $newToken = $this->tokenHelper->getToken($user);
        $this->userRepository->set($user);

        return $this->json(['token' => $newToken]);
    }

    public function changePassword(): HttpResponse
    {
        /** @var TokenUser $user */
        $user = $this->userHandler->findUser($_REQUEST['username']);

        if ($_REQUEST['newPassword1'] !== $_REQUEST['newPassword2']) {
            return $this->json(['message' => 'The Passwords have to match'], 400);
        }

        try {
            $this->userHandler->changePassword($user->getUsername(), $_REQUEST['currentPassword'], $_REQUEST['newPassword1']);
        } catch (PasswordInvalidException $e) {
            return $this->json(['message' => 'Invalid Password'], 400);
        }

        $this->tokenHelper->generateNewTokenStamp($user);
        $newToken = $this->tokenHelper->getToken($user);

        $this->userRepository->set($user);

        return $this->json(['token' => $newToken]);
    }

    public function createAdmin(RequestInterface $request): HttpResponse
    {
        if (AdminHelper::isAdminCreated()) {
            return $this->json(['message' => 'An Admin already exists'], 400);
        }

        if (strtolower($request->requestMethod) === 'get') {
            return $this->json(['message' => 'Create your admin']);
        }

        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];

        $user = new TokenUser(0, $username, 'Editor', null, null, null, null, null, null);
        $guest = new TokenUser(0, 'Guest', 'Guest', null, null, null, null, null, null);

        $this->tokenHelper->generateNewTokenStamp($user);

        $this->userRepository->set($user);
        $this->userRepository->set($guest);

        $this->userHandler->setPassword($username, $password);

        return $this->json(['adminCreated' => true]);
    }
}
