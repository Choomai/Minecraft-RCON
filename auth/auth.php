<?php
require_once "credentials.php";

function getValueFromSession(string $key, mixed $defaultValue = null) {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
};

$isSuccess = false;
session_set_cookie_params(3600 * 24 * 7);
session_start();
$username = getValueFromSession("username");
$pass = getValueFromSession("password");
$usr_pattern = '/^(?!.*([_.])\\1)[a-zA-Z0-9._]{3,32}$/';
class Auth {
    public $db;
    public $sql_cmd;
    function __construct($inp_db) {
        $this->db = $inp_db;
        $this->db->query("CREATE TABLE IF NOT EXISTS `account_list`(
            `id` int NOT NULL AUTO_INCREMENT,
            `username` varchar(32) NOT NULL,
            `password` char(60) NOT NULL,
            `target_host` varchar(255) NOT NULL DEFAULT 'localhost',
            `target_port` int NOT NULL DEFAULT '25575',
            `target_password` varchar(255) NOT NULL DEFAULT 'password',
            `priority` int DEFAULT '0',
            PRIMARY KEY (`id`),
            UNIQUE KEY `sqlite_autoindex_account_list_1` (`username`)
        )");
        $this->db->query("CREATE TABLE IF NOT EXISTS `auth_logs` (
            `id` int NOT NULL AUTO_INCREMENT,
            `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `info` text,
            PRIMARY KEY (`id`)
        )");

        $this->db->query("CREATE TABLE IF NOT EXISTS `user_logs` (
            `id` int NOT NULL AUTO_INCREMENT,
            `time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            `info` text,
            PRIMARY KEY (`id`)
            );
        ");
    }
    function check($username) {
        $statement = null;
        $this->sql_cmd = "SELECT * FROM account_list WHERE username=?";
        $statement = $this->db->prepare($this->sql_cmd);
        $statement->bind_param("s", $username);
        $statement->execute();
        
        $result = $statement->get_result();
        if ($result->fetch_assoc()) {
            $result->close();   
            return true;
        };
        $result->close();
    }

    function check_login($username, $password) {
        $this->sql_cmd = "SELECT * FROM account_list WHERE username=? AND password=?";
        $statement = $this->db->prepare($this->sql_cmd);
        $statement->bind_param("ss", $username, $password);
        $statement->execute();
        
        $result = $statement->get_result();
        if ($result->fetch_assoc()) {
            $result->close();   
            return true;
        };
        $result->close();
    }

    function register($username, $pass) {
        $this->sql_cmd = "INSERT INTO account_list(username, password) VALUES(?, ?)";
        $statement = $this->db->prepare($this->sql_cmd);
        $statement->bind_param("ss", $username, $pass);
        $statement->execute();
    }

    function log($log_content) {
        $log_content = $this->db->real_escape_string($log_content);
        return $this->db->query("INSERT INTO auth_logs(info) VALUES('$log_content')");
    }
    function quit() {return $this->db->close();}
};
$auth = new Auth(new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT, DB_SOCKET));
?>