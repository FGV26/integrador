<?php
class Conexion {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "estudiojuridico";
    private $port = 3307;
    public $conn;

    public function __construct() {
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname, $this->port);

        if ($this->conn->connect_error) {
            die("Conexión fallida: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>
