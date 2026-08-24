<?php
class Conexion {
    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $port;
    public $conn;

    public function __construct() {
        $this->servername = getenv('DB_HOST') ?: 'localhost';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';
        $this->dbname = getenv('DB_NAME') ?: 'estudiojuridico';
        $this->port = (int) (getenv('DB_PORT') ?: 3307);

        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname, $this->port);
        $this->conn->set_charset('utf8mb4');

        if ($this->conn->connect_error) {
            die("Conexion fallida: " . $this->conn->connect_error);
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>
