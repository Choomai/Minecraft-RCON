# Minecraft-RCON
Simple Minecraft RCON Web Console with account system (using PHP, MySQL and Javascript) 

Inspired by [Minecraft-RCON-Console](https://github.com/ekaomk/Minecraft-RCON-Console) and using [PHP-Minecraft-Rcon](https://github.com/thedudeguy/PHP-Minecraft-Rcon).

## Configuration

1. Edit your Minecraft server `server.properties` configuration file in order to enable RCON:
```
enable-rcon=true
rcon.port=25575
rcon.password=xtMJsVtmx0XypuId7jIb
```
2. Restart your Minecraft server.
3. Download/Clone the Minecraft RCON Web Console files and create the `auth/credentials.php` file. Provide your MySQL Database credentials there:
```php
<?php
define("DB_HOST", "localhost");
define("DB_USERNAME", "mc_rcon");
define("DB_PASSWORD", "69");
define("DB_NAME", "minecraft_rcon");
define("DB_PORT", 3306);
define("DB_SOCKET", "/run/mysqld/mysqld.sock");
?>
```