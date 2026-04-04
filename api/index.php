<?php
header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: must-understand, no-store");

require_once "rcon.php";
session_set_cookie_params(3600 * 24 * 7);
session_start();

$host = $_SESSION["target_host"];
$port = $_SESSION["target_port"] ?? 25575;
$password = $_SESSION["target_pass"];
$timeout = 5;

$response = array();
$rcon = new Rcon($host, $port, $password, $timeout);

$response["status"] = "error";
if (empty($_POST["cmd"])) {
    http_response_code(400);
    $response["error"] = "Empty command";
    exit(json_encode($response));
};
if (!isset($host, $port, $password)) {
    http_response_code(401);
    $response["error"] = "Target server not found on session";
    exit(json_encode($response));
};
if (!$rcon->connect()) {
    http_response_code(400);
    $response["error"] = "RCON connection error";
    exit(json_encode($response));
};

$rcon->send_command($_POST["cmd"]);
$response["status"] = "success";
$response["command"] = $_POST["cmd"];
$response["response"] = parseMinecraftColors($rcon->get_response());

function parseMinecraftColors($string) {
    if (!is_string($string)) return null;

    // Set proper encoding
    if (mb_check_encoding($string, "ISO-8859-1")) {
        $string = mb_convert_encoding(htmlspecialchars($string, ENT_QUOTES, "UTF-8"), "ISO-8859-1", "UTF-8");
    }
    
    $string = str_replace("\n", "<br>", $string);
    // Parse color and formatting codes (§ symbol = \xA7)
    $string = preg_replace('/\xA7([0-9a-f])/i', '<span class="mc-color mc-$1">', $string, -1, $count) . str_repeat("</span>", $count);
    $string = preg_replace('/\xA7([k-or])/i', '<span class="mc-$1">', $string, -1, $count) . str_repeat("</span>", $count);
    
    return mb_convert_encoding($string, "UTF-8", "ISO-8859-1");
}

exit(json_encode($response));
?>