<?php
// Habilitar CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Manejo de solicitudes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir modelos
include_once '../model/database.php';
include_once '../model/user.php';

// Crear conexión a la base de datos
$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Leer datos
$data = json_decode(file_get_contents("php://input"));

// Validar ID
if (!$data || empty($data->id)) {
    http_response_code(400);
    echo json_encode(["message" => "ID de usuario no proporcionado."]);
    exit();
}

// Asignar ID
$user->id = intval($data->id);

// Intentar eliminar
if ($user->delete()) {
    http_response_code(200);
    echo json_encode(["message" => "Usuario eliminado correctamente."]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Error al eliminar el usuario."]);
}
?>
