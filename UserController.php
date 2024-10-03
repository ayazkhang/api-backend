<?php

require_once 'UserService.php';

class UserController
{
    private $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    public function login()
    {  

        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $rememberMe = $data['rememberMe'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
            return;
        }

        $user = $this->userService->authenticateUser($email, $password,$rememberMe);

        if ($user) {
            echo json_encode(['status' => 'success', 'message' => 'logedin successfully ', 'token' => $user['token'], 'user' => $user['user']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
        }
    }

    public function logout()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $token = $data['token'] ?? '';
        $this->userService->logout($token);
    }

    public function register()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $email = $data['email'] ?? '';
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';


        if (empty($email) || empty($username) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
            return;
        }

        if ($this->userService->checkEmailExists($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Email already exists']);
            return;
        }

        if (strlen($username) <= 3) {
            echo json_encode(['status' => 'error', 'message' => 'Username should be more than 3 characters']);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format']);
            return;
        }

        if (strlen($password) <= 6) {
            echo json_encode(['status' => 'error', 'message' => 'Password should be more than 6 characters']);
            return;
        }

        $result = $this->userService->registerUser($email, $username, $password);

        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Registration successful']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Registration failed']);
        }
    }

    public function createDatabase()
    {

        $createTable = $this->userService->createTable();

    }



}
