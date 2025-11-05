<?php
class database {
    private $hostname = "localhost";
    private $database = "raimbowsix";
    private $username = "root";
    private $password = "";
    private $charset  = "utf8";

    function conectar() {
        try {
            $dsn = "mysql:host=" . $this->hostname . ";dbname=" . $this->database . ";charset=" . $this->charset;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, $this->username, $this->password, $options);
            return $pdo;
        } catch (PDOException $e) {
            echo "Error de Conexión: " . $e->getMessage();
            exit;
        }
    }
}
?>