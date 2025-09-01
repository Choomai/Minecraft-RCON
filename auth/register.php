<?php
    require_once "auth.php";
    $message = null;
    if (!empty($_POST["username"]) && !empty($_POST["password"])) {
        $username = $_POST["username"];
        $pass = password_hash($_POST["password"], PASSWORD_BCRYPT);
        if (!preg_match($usr_pattern, $username)) {
            $message = "Invalid username. Don't try to bypass this.";
        } else {
            if ($username == $auth->check("username", $username, NULL)["username"]) {
                $message = "Username has been taken. Use another one.";
            } else {
                $auth->register($username, $pass);
                $auth->log("$username has been created.");
                $isSuccess = true;
                $_SESSION["username"] = $username;
                $_SESSION["password"] = $pass;
            };
        }
    }
    $auth->quit();
    if ($isSuccess == true) {header("Location: /auth/login.php");exit;}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include "../head.php" ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto&display=swap">
    <link rel="stylesheet" href="auth.css">
    <title>RCON Auth | Register</title>
</head>
<body>
    <div class="container">
        <h1 class="auth-type">Minecraft RCON</h1>
        <span>Already have an account ? <a class="auth-hyp" href="/auth/login.php">Login</a></span>
        <form id="login" action="" method="post">
            <div class="input">
                <input id="user" type="text" name="username" pattern="^(?!.*([_.])\1)[a-zA-Z0-9._]{3,32}$" placeholder="Username" required>
                <input id="pass" type="password" name="password" placeholder="Password" required>
                <span class="message"><?php echo $message;?></span>
            </div>
            <div class="action">
                <input type="submit" name="submit" value="Sign up" class="login">
            </div>
        </form>
    </div>

    <div class="reduced-fullpage"></div>
</body>
</html>