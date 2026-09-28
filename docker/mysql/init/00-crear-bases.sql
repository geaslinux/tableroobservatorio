-- Se ejecuta solo la primera vez que se crea el volumen de MySQL.
-- Crea las dos bases que usa la aplicación (app/Config/Database.php).
CREATE DATABASE IF NOT EXISTS `rep`   CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE DATABASE IF NOT EXISTS `base2` CHARACTER SET utf8 COLLATE utf8_general_ci;

GRANT ALL PRIVILEGES ON `rep`.*   TO 'sistemabase'@'%';
GRANT ALL PRIVILEGES ON `base2`.* TO 'sistemabase'@'%';
FLUSH PRIVILEGES;
