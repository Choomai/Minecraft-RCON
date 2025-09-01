<?php
    require_once "auth.php";
    $message = null;
    if (!empty($username) && !empty($pass)) {
        if ($auth->check_login($username, $pass)) {$isSuccess = true;}
        else {
            $message = "Invalid cookie data. Please login again.";
            session_destroy();
        };
    } else if (!empty($_POST["username"]) && !empty($_POST["password"])) {
        $username = $_POST["username"];
        if ($row = $auth->check($username)) {
            if (password_verify($_POST["password"], $row["password"])) {
                $_SESSION["username"] = $username;
                $_SESSION["password"] = $pass;
                $isSuccess = true;
                $auth->log("$username has logged in.");
            } else {
                $message = "Invalid password. Try again.";
                $auth->log("$username failed to get the password.");
            }
        } else {$message = "User didn't exist in the database.";}
    };
    $auth->quit();
    if ($isSuccess == true) {header("Location: /");exit;}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "../head.php" ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto&display=swap">
    <link rel="stylesheet" href="auth.css">
    <title>RCON Auth | Login</title>
</head>
<body>
    <div class="container">
        <h1 class="auth-type">Minecraft RCON</h1>
        <span>New user ? <a class="auth-hyp" href="/auth/register.php">Create an account</a></span>
        <form id="login" action="" method="post">
            <div class="input">
                <input id="user" type="text" name="username" pattern="^(?!.*([_.])\1)[a-zA-Z0-9._]{3,32}$" placeholder="Username" required>
                <input id="pass" type="password" name="password" placeholder="Password" required>
                <span class="message"><?php echo $message;?></span>
            </div>
            <div class="action">
                <del style="color:#6fbcff" href="/auth/forgot-password.php">Forgot password?</del>
                <input type="submit" name="submit" value="Sign in" class="login">
            </div>
        </form>
    </div>

    <div class="reduced-fullpage"></div>
</body>
</html>