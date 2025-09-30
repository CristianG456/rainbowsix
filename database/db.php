<?php
    class database{
        private $hostname = "localhost" ;
        private $database = "raimbowsix"; 
        private $username = "root";
        private $password = "";
        private $charset = "utf8";

        function conectar()
        {
            try{
                $conexion = "mysql:host=". $this->hostname ."; dbname=" . $this->database . "; charset=" .$this->charset;
                $option = [
                    PDO::ATTR_ERRMODE => PDO:: ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false 
                ];
            
            $pdo = new PDO($conexion, $this->username, $this->password, $option);
            return $pdo;
            }
            catch(PDOException $e){
                echo "Error de Conexion: " . $e->getMessage();
                exit;
            };

            
        }

    }
?>