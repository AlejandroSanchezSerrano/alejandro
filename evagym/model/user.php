<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $name;
    public $age;
    public $weight;
    public $height;
    public $gender;
    public $activity_level;
    public $goal;
    public $daily_calories;
    public $passwd;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (name, age, weight, height, gender, activity_level, goal, daily_calories, password) 
                  VALUES (:name, :age, :weight, :height, :gender, :activity_level, :goal, :daily_calories, :passwd)";

        $stmt = $this->conn->prepare($query);

        // Sanitizar y validar datos
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->passwd = password_hash($this->passwd, PASSWORD_DEFAULT);
        $this->age = filter_var($this->age, FILTER_VALIDATE_INT);
        $this->weight = filter_var($this->weight, FILTER_VALIDATE_FLOAT);
        $this->height = filter_var($this->height, FILTER_VALIDATE_FLOAT);
        $this->gender = htmlspecialchars(strip_tags($this->gender));
        $this->activity_level = htmlspecialchars(strip_tags($this->activity_level));
        $this->goal = htmlspecialchars(strip_tags($this->goal));
        $this->daily_calories = filter_var($this->daily_calories, FILTER_VALIDATE_INT);

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":age", $this->age, PDO::PARAM_INT);
        $stmt->bindParam(":weight", $this->weight, PDO::PARAM_STR);
        $stmt->bindParam(":height", $this->height, PDO::PARAM_STR);
        $stmt->bindParam(":gender", $this->gender);
        $stmt->bindParam(":activity_level", $this->activity_level);
        $stmt->bindParam(":goal", $this->goal);
        $stmt->bindParam(":daily_calories", $this->daily_calories, PDO::PARAM_INT);
        $stmt->bindParam(":passwd", $this->passwd);

        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Database Insert Error: " . $e->getMessage());
        }

        return false;
    }

    public function authenticate() {
        $query = "SELECT id, password FROM " . $this->table_name . " WHERE name = :name LIMIT 1";
    
        $stmt = $this->conn->prepare($query);
    
        // Sanitizar el nombre de usuario
        $this->name = htmlspecialchars(strip_tags($this->name));
    
        $stmt->bindParam(':name', $this->name);
    
        try {
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($user && password_verify($this->passwd, $user['password'])) {
                return [
                    "success" => true,
                    "user_id" => $user['id']
                ];
            }
        } catch (PDOException $e) {
            error_log("Authentication Error: " . $e->getMessage());
        }
    
        return [
            "success" => false,
            "message" => "Usuario o contrase�a incorrectos."
        ];
    }

    public function getById() {
        $query = "SELECT id, name, age, weight, height, gender, activity_level, goal, daily_calories 
                  FROM " . $this->table_name . " 
                  WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);

        try {
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Fetch User Error: " . $e->getMessage());
        }

        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
    
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
    
        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Delete User Error: " . $e->getMessage());
        }
    
        return false;
    }
    
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name = :name,
                      age = :age,
                      weight = :weight,
                      height = :height,
                      gender = :gender,
                      activity_level = :activity_level,
                      goal = :goal,
                      daily_calories = :daily_calories
                  WHERE id = :id";
    
        $stmt = $this->conn->prepare($query);
    
        // Sanitizar y validar datos
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->age = filter_var($this->age, FILTER_VALIDATE_INT);
        $this->weight = filter_var($this->weight, FILTER_VALIDATE_FLOAT);
        $this->height = filter_var($this->height, FILTER_VALIDATE_FLOAT);
        $this->gender = htmlspecialchars(strip_tags($this->gender));
        $this->activity_level = htmlspecialchars(strip_tags($this->activity_level));
        $this->goal = htmlspecialchars(strip_tags($this->goal));
        $this->daily_calories = filter_var($this->daily_calories, FILTER_VALIDATE_INT);
        $this->id = filter_var($this->id, FILTER_VALIDATE_INT);
    
        // Bind de los parámetros
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':age', $this->age, PDO::PARAM_INT);
        $stmt->bindParam(':weight', $this->weight, PDO::PARAM_STR);
        $stmt->bindParam(':height', $this->height, PDO::PARAM_STR);
        $stmt->bindParam(':gender', $this->gender);
        $stmt->bindParam(':activity_level', $this->activity_level);
        $stmt->bindParam(':goal', $this->goal);
        $stmt->bindParam(':daily_calories', $this->daily_calories, PDO::PARAM_INT);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
    
        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Update User Error: " . $e->getMessage());
        }
    
        return false;
    }
    
    public function updateWeightOnly() {
        $query = "UPDATE " . $this->table_name . " SET weight = :weight WHERE id = :id";
    
        $stmt = $this->conn->prepare($query);
    
        $this->weight = filter_var($this->weight, FILTER_VALIDATE_FLOAT);
        $this->id = filter_var($this->id, FILTER_VALIDATE_INT);
    
        $stmt->bindParam(":weight", $this->weight);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
    
        try {
            if ($stmt->execute()) {
                return true;
            }
        } catch (PDOException $e) {
            error_log("Error actualizando solo el peso: " . $e->getMessage());
        }
    
        return false;
    }
    
}
?>
