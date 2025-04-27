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
if (!$data || empty($data->name) || empty($data->passwd)) {
    http_response_code(400);
    echo json_encode(["message" => "Nombre de usuario y contraseña son obligatorios."]);
    exit();
}

// Asignar los datos del cliente al objeto User
$user->name = $data->name;
$user->passwd = $data->passwd;

// Intentar autenticar al usuario
$result = $user->authenticate();
if ($result['success']) {
    http_response_code(200);
    echo json_encode([
        "message" => "Inicio de sesión exitoso.",
        "user_id" => $result['user_id'],
	"name" => $user->name
    ]);
} else {
    http_response_code(401); // No autorizado
    echo json_encode(["message" => $result['message']]);
}
?>