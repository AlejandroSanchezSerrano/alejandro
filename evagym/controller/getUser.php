<?php
// Habilitar CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Habilitar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Manejo de solicitudes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir archivos necesarios
include_once '../model/database.php';
include_once '../model/user.php';

// Conexión a la base de datos
$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Leer los datos recibidos (JSON)
$data = json_decode(file_get_contents("php://input"));

// Validar que se envió el ID
if (!$data || empty($data->id)) {
    http_response_code(400);
    echo json_encode(["message" => "ID de usuario no proporcionado."]);
    exit();
}

// Asignar el ID al objeto
$user->id = intval($data->id);

// Obtener información del usuario
$result = $user->getById();

if ($result) {
    http_response_code(200);
    echo json_encode($result);
} else {
    http_response_code(404);
    echo json_encode(["message" => "Usuario no encontrado."]);
}
?>
