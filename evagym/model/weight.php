<?php
class Weight {
    private $conn;
    private $table_name = "weights";

    public $id;
    public $weight;
    public $date;
    public $id_user;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " (weight, date, id_user) 
                  VALUES (:weight, :date, :id_user)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->weight = filter_var($this->weight, FILTER_VALIDATE_FLOAT);
        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->id_user = filter_var($this->id_user, FILTER_VALIDATE_INT);

        $stmt->bindParam(":weight", $this->weight);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":id_user", $this->id_user, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Database Insert Error (Weight): " . $e->getMessage());
        }

        return false;
    }

    public function getByUserId() {
        $query = "SELECT id, weight, date FROM " . $this->table_name . " 
                  WHERE id_user = :id_user 
                  ORDER BY date DESC";

        $stmt = $this->conn->prepare($query);

        $this->id_user = filter_var($this->id_user, FILTER_VALIDATE_INT);
        $stmt->bindParam(":id_user", $this->id_user, PDO::PARAM_INT);

        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Fetch Weights Error: " . $e->getMessage());
        }

        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $this->id = filter_var($this->id, FILTER_VALIDATE_INT);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Delete Weight Error: " . $e->getMessage());
        }

        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET weight = :weight, date = :date 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar
        $this->weight = filter_var($this->weight, FILTER_VALIDATE_FLOAT);
        $this->date = htmlspecialchars(strip_tags($this->date));
        $this->id = filter_var($this->id, FILTER_VALIDATE_INT);

        $stmt->bindParam(":weight", $this->weight);
        $stmt->bindParam(":date", $this->date);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Update Weight Error: " . $e->getMessage());
        }

        return false;
    }
}
?>
