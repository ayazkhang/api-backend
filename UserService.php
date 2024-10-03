<?php
session_start();

require_once 'Database.php';

class UserService
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }


    private function setLongSession()
    {
        $lifetime = 30 * 86400;
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params($lifetime, '/', '', false, true);
            session_start();
        }
        $_SESSION['expire'] = time() + $lifetime;
    }

    public function authenticateUser($email, $password, $rememberMe)
    {
        // Prepare the SQL statement to fetch user details
        $stmt = $this->db->getConnection()->prepare('SELECT username, email, id FROM users WHERE email = ? AND password = ?');
        $stmt->execute([$email, $password]);
    
        // Fetch the user data as an associative array
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($user) {
            // Generate a unique token
            $token = bin2hex(random_bytes(16));
    
            // Store the token in the database
            $stmt = $this->db->getConnection()->prepare('UPDATE users SET remember_token = ? WHERE id = ?');
            $stmt->execute([$token, $user['id']]);
    
            if ($rememberMe) {
                // Set a cookie for 30 days if "remember me" is checked
                setcookie('remember_me', $token, time() + (30 * 86400), '/', '', false, true);
            }
    
            // Return user data and token without numeric keys
            return [
                'status' => 'success',
                'message' => 'Logged in successfully',
                'token' => $token,
                'user' => $user
            ];
        }
    
        return null; // User not found or password mismatch
    }
    

    public function registerUser($email, $username, $password)
    {
        $stmt = $this->db->getConnection()->prepare('INSERT INTO users (email, username, password) VALUES (?, ?, ?)');
        return $stmt->execute([$email, $username, $password]);

    }

    public function checkEmailExists($email)
    {
        $query = "SELECT COUNT(*) FROM users WHERE email = :email";
        $stmt = $this->db->getConnection()->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function logout($token)
    {
        if (isset($_COOKIE['remember_me'])) {

            $stmt = $this->db->getConnection()->prepare('UPDATE users SET remember_token = NULL WHERE remember_token = ?');
            $stmt->execute([$token]);

            setcookie('remember_me', '', time() - 3600, '/', '', false, true);
        }

        echo json_encode(['status' => 'success', 'message' => 'Successfully logged out']);
    }

    public function createTable()
    {
        $databaseName = 'api';

        $this->db->getConnection()->exec("CREATE DATABASE IF NOT EXISTS $databaseName");
        $this->db->getConnection()->exec("USE $databaseName");

        $sql = "CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `email` varchar(255) DEFAULT NULL,
            `password` varchar(255) DEFAULT NULL,
            `username` varchar(255) DEFAULT NULL,
            `remember_token` varchar(255) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

        $this->db->getConnection()->exec($sql);
        
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Database and users table created successfully']);
    }
}
