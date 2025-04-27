<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'sancheza_evagym';
    private $username = 'sancheza';
    private $password = 's$Ancheza_#8';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $exception) {
            error_log("Database Connection Error: " . $exception->getMessage());
            die(json_encode(["message" => "Database connection failed"]));
        }
        return $this->conn;
    }
}
?>
