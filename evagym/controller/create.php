<?php
// Habilitar CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Habilitar reporte de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Manejo de solicitudes OPTIONS (preflight request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../model/database.php';
include_once '../model/user.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Leer los datos de la solicitud
$data = json_decode(file_get_contents("php://input"));

// Verificar si los datos fueron recibidos correctamente
if (!$data) {
    http_response_code(400);
    echo json_encode(["message" => "No data received."]);
    exit();
}

// Validar datos obligatorios
$requiredFields = ['name', 'age', 'weight', 'height', 'gender', 'activity_level', 'goal', 'daily_calories', 'passwd'];
foreach ($requiredFields as $field) {
    if (empty($data->$field)) {
        http_response_code(400);
        echo json_encode(["message" => "Missing field: $field"]);
        exit();
    }
}

// Asignar datos al objeto User
$user->name = htmlspecialchars(strip_tags($data->name));
$user->age = intval($data->age);
$user->weight = floatval($data->weight);
$user->height = floatval($data->height);
$user->gender = htmlspecialchars(strip_tags($data->gender));
$user->activity_level = htmlspecialchars(strip_tags($data->activity_level));
$user->goal = htmlspecialchars(strip_tags($data->goal));
$user->daily_calories = intval($data->daily_calories);
$user->passwd = htmlspecialchars(strip_tags($data->passwd));

// Intentar crear el usuario
if ($user->create()) {
    http_response_code(201);
    echo json_encode(["message" => "User created successfully."]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Failed to create user."]);
}
?>
