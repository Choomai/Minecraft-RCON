<?php
    require_once "auth/auth.php";
    if (isset($username) && isset($pass)) {
        if ($row = $auth->check_login($username, $pass)) {
            $_SESSION["target_host"] = $row["target_host"];
            $_SESSION["target_port"] = $row["target_port"];
            $_SESSION["target_pass"] = $row["target_password"];
            $isSuccess = true;
        };
        $auth->quit();
    };
    if (!$isSuccess) {header("Location: /auth/login.php");exit;}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <?php include "head.php" ?>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="colors.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery-ui/dist/jquery-ui.min.js"></script>
    <script src="rcon.js" defer></script>
    <script>const username = "<?= $username?>";</script>
    <title>Minecraft RCON</title>
</head>
<body>
    <main class="container">
        <div class="alert alert-info" id="alertMessage">Minecraft RCON</div>
        <div id="consoleRow">
            <div class="panel" id="consoleContent">
                <div id="hidden-xs" class="panel-heading">
                    <h3 id="panel-title" class="panel-title"><span class="material-symbols-outlined">terminal</span>&nbsp;Console<span style="color:red;font-weight:bold">&nbsp;v0.4.8</span></h3>
                    <div class="btn-group">
                        <a class="btn-def cmd-help" href="https://minecraft.gamepedia.com/Commands" target="_blank"><span class="material-symbols-outlined">help</span><span class="btn-xs">&nbsp;Commands</span></a>
                        <a class="btn-def" href="https://github.com/Rauks/Minecraft-RCON"><img src="https://github.githubassets.com/favicons/favicon-dark.svg" style="width:24px"><span class="btn-xs">&nbsp;Source</span></a>
                        <a class="btn-def" href="/auth/logout.php"><span class="material-symbols-outlined">logout</span></a>
                    </div>
                </div>
                <div class="panel-body"><ul class="list-group" id="groupConsole"></ul></div>
            </div>
            <div class="input-group">
                <span class="settings" onclick="settings_popup('show')"><span class="material-symbols-outlined">settings</span></span>
                <div id="txtCommandResults"></div>
                <input type="text" class="form-control" id="txtCommand">
                <div class="input-group-btn">
                    <button class="btn-gradient" type="button" id="send"><span class="material-symbols-outlined">send</span><span class="btn-xs">&nbsp;Send</span></button>
                    <button type="button" id="clearLog"><span class="material-symbols-outlined">delete_sweep</span><span class="btn-xs">&nbsp;Clear</span></button>
                </div>
            </div>
        </div>
    </main>
    <div id="settings" class="settings-popup" style="display:none">
        <span class="close-popup" onclick="settings_popup('hide')">&times;</span>
        <span class="logged-as">Currently signed in as&nbsp;<b id="current-username"><?= $username?></b></span>
        <div class="settings-container center">
            <h3>Settings</h3>
            <span class="settings-elem"><input id="auto-scroll" class="toggle" type="checkbox" onchange="settings('auto-scroll')"><label for="auto-scroll">&nbsp;Auto-scoll console log to bottom</label></span>
            <span class="settings-elem"><input id="hide-desc" class="toggle" type="checkbox" onchange="settings('hide-desc')"><label for="hide-desc">&nbsp;Hide button description</label></span>
        </div>
        <div class="target-server-container center">
            <h3>Target Server</h3>
            <input id="target_host" type="text" value="<?= $_SESSION["target_host"];?>">
            <input id="target_port" type="number" min="1" max="65535" value="<?= $_SESSION["target_port"];?>">
            <input id="target_passwd" type="password" value="<?= $_SESSION["target_pass"];?>"><br>
            <button class="save-db" onclick="saveTargetServer($('input#target_host').val(), $('input#target_port').val(), $('input#target_passwd').val())">Save to database</button>
            <span id="save-db_notify" style="display:none"></span>
        </div>
    </div>
</body>
</html>