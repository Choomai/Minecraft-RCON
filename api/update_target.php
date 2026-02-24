<?php
    require_once "../auth/auth.php";

    $target_host = $_POST["host"];
    $target_port = $_POST["port"];
    $target_pass = $_POST["pass"];

    if (!isset($username, $pass)) {
        http_response_code(401);
        exit;
    }
    if (empty($target_host) || empty($target_port) || empty($target_pass)) {
        http_response_code(400);
        exit;
    }

    $db = new mysqli(hostname:"localhost", username:"www-data", database:"RCON Minecraft", socket: "/var/run/mysqld/mysqld.sock");
    $statement = $db->prepare("UPDATE account_list SET target_host=?, target_port=?, target_password=? WHERE username=? AND password=?");

    $statement->bind_param("sisss", $target_host, $target_port, $target_pass, $username, $pass);
    $statement->execute();
    $_SESSION["host"] = $target_host;
    $_SESSION["port"] = $target_port;
    $_SESSION["pass"] = $target_pass;

    $log_content = $db->real_escape_string("$username has changed their target server to [$target_host, $target_port, $target_pass].");
    $db->query("INSERT INTO user_logs(info) VALUES('$log_content')");
    $db->close();
?>