<?php
class Db {

    private static $pdo = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(){
        
        if (self::$pdo ===   null){

            $env = parse_ini_file(__DIR__ . '/../../.env');

            if(!$env) {
                throw new Exception("Failed to load .env file");
            }

            $host = $env['DB_HOST'];
            $dbname = $env['DB_NAME'];
            $user = $env['DB_USER'];
            $password = $env['DB_PASSWORD'];
            $charset = $env['DB_CHARSET'];

            try {
                self::$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $user, $password);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            } catch(PDOException $e) {
                error_log($e->getMessage());    
                throw new Exception("Failed to connect to database");
            }

        }
        return self::$pdo;
    }
}
?>