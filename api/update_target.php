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

    $statement = $auth->db->prepare("UPDATE account_list SET target_host=?, target_port=?, target_password=? WHERE username=? AND password=?");

    $statement->bind_param("sisss", $target_host, $target_port, $target_pass, $username, $pass);
    $statement->execute();
    $_SESSION["target_host"] = $target_host;
    $_SESSION["target_port"] = $target_port;
    $_SESSION["target_pass"] = $target_pass;

    $log_content = $auth->db->real_escape_string("$username has changed their target server to [$target_host, $target_port, $target_pass].");
    $auth->db->query("INSERT INTO user_logs(info) VALUES('$log_content')");
?>