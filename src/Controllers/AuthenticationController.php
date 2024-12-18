<?php

namespace PixlMint\CMS\Controllers;

use Nacho\Contracts\RequestInterface;
use Nacho\Contracts\UserHandlerInterface;
use Nacho\Exceptions\PasswordInvalidException;
use Nacho\Models\HttpMethod;
use Nacho\Models\HttpResponse;
use PixlMint\CMS\Helpers\CustomUserHelper;
use PixlMint\CMS\Helpers\AdminHelper;
use PixlMint\CMS\Helpers\SecretHelper;
use PixlMint\CMS\Models\TokenUser;
use PixlMint\CMS\Helpers\TokenHelper;
use Nacho\Controllers\AbstractController;
use Nacho\Security\UserRepository;
use Psr\Log\LoggerInterface;

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
        $username = $request->getBody()->get('username');
        $password = $request->getBody()->get('password');
        if ($request->isMethod(HttpMethod::POST)) {
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
        $username = $request->getBody()->get('username');
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

        $username = $request->getBody()->get('username');
        $resetToken = $request->getBody()->get('resetToken');
        $password1 = $request->getBody()->get('password1');
        $password2 = $request->getBody()->get('password2');

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

        $newToken = $this->generateNewTokenStamp();

        return $this->json(['token' => $newToken]);
    }

    public function destroyToken(): HttpResponse
    {
        if (!$this->isGranted(CustomUserHelper::ROLE_EDITOR)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }

        $this->generateNewTokenStamp();

        return $this->json(['message' => 'Successfully logged out everywhere']);
    }

    private function generateNewTokenStamp()
    {
        $user = $this->userHandler->getCurrentUser();

        $this->tokenHelper->generateNewTokenStamp($user);
        $newToken = $this->tokenHelper->getToken($user);
        $this->userRepository->set($user);
        return $newToken;
    }

    public function changePassword(RequestInterface $request): HttpResponse
    {
        $body = $request->getBody();
        if (!$body->has('username') || !$body->has('currentPassword') || !$body->has('newPassword1') || !$body->has('newPassword2')) {
            return $this->json(['message' => 'Please define username, currentPassword, newPassword1 and newPassword2'], 400);
        }

        /** @var TokenUser $user */
        $user = $this->userHandler->findUser($body->get('username'));

        if ($body->get('newPassword1') !== $body->get('newPassword2')) {
            return $this->json(['message' => 'The Passwords have to match'], 400);
        }

        try {
            $this->userHandler->changePassword($user->getUsername(), $body->get('currentPassword'), $body->get('newPassword1'));
        } catch (PasswordInvalidException $e) {
            return $this->json(['message' => 'Invalid Password'], 400);
        }

        if ($body->getOrNull('generateNewToken')) {
            $this->tokenHelper->generateNewTokenStamp($user);
            $newToken = $this->tokenHelper->getToken($user);
        } else {
            $newToken = '';
        }

        $this->userRepository->set($user);

        return $this->json(['token' => $newToken]);
    }

    public function destroySecret(RequestInterface $request, SecretHelper $secretHelper, LoggerInterface $logger): HttpResponse
    {
        if (!$request->isMethod(HttpMethod::POST)) {
            return $this->json(['message' => 'Only POST requests allowed'], 405);
        }
        if (!$this->isGranted(CustomUserHelper::ROLE_SUPER_ADMIN)) {
            return $this->json(['message' => 'You are not authenticated'], 401);
        }
        $secretHelper->generateNewSecret();
        $logger->info('Generated new secret key');

        foreach ($this->userHandler->getUsers() as $user) {
            /** @var $user TokenUser */
            $logger->info('Destroying secret for user ' . $user->getUsername());
            $user->setTokenStamp('');
            $this->userRepository->set($user);
        }

        return $this->json(['message' => 'Destroyed Secret']);
    }

    public function createAdmin(RequestInterface $request): HttpResponse
    {
        if (AdminHelper::isAdminCreated()) {
            return $this->json(['message' => 'An Admin already exists'], 400);
        }

        if (strtolower($request->requestMethod) === 'get') {
            return $this->json(['message' => 'Create your admin']);
        }

        $username = $request->getBody()->get('username');
        $password = $request->getBody()->get('password');

        $guest = new TokenUser(-1, 'Guest', 'Guest', null, null, null, null, null, null);
        $user = new TokenUser(-1, $username, 'Editor', null, null, null, null, null, null);

        $this->tokenHelper->generateNewTokenStamp($user);

        $this->userRepository->set($guest);
        $this->userRepository->set($user);

        $this->userHandler->setPassword($username, $password);

        return $this->json(['adminCreated' => true]);
    }
}
