<?php
// Habilitar CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Reporte de errores para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Manejo de solicitudes OPTIONS (preflight request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../model/database.php';
include_once '../model/weight.php';

$database = new Database();
$db = $database->getConnection();
$weight = new Weight($db);

// Leer los datos de la solicitud
$data = json_decode(file_get_contents("php://input"));

if (!$data) {
    http_response_code(400);
    echo json_encode(["message" => "No se recibieron datos."]);
    exit();
}

// Verificar si viene un campo 'action'
if (!isset($data->action)) {
    http_response_code(400);
    echo json_encode(["message" => "No se especificó ninguna acción."]);
    exit();
}

switch ($data->action) {
    case 'create':
        if (empty($data->weight) || empty($data->id_user)) {
            http_response_code(400);
            echo json_encode(["message" => "Faltan los campos obligatorios: peso o id de usuario."]);
            exit();
        }
    
        $weight->weight = floatval($data->weight);
        $weight->id_user = intval($data->id_user);
        $weight->date = isset($data->date) ? htmlspecialchars(strip_tags($data->date)) : date('Y-m-d H:i:s');
    
        if ($weight->create()) {
            // Actualizar el peso en la tabla users
            include_once '../model/user.php';
            $user = new User($db);
            $user->id = $weight->id_user;
            $user->weight = $weight->weight;
    
            if ($user->updateWeightOnly()) {
                http_response_code(201);
                echo json_encode(["message" => "Peso registrado y usuario actualizado correctamente."]);
            } else {
                http_response_code(200); // Se creó el peso, pero falló la actualización del usuario
                echo json_encode(["message" => "Peso registrado, pero error al actualizar el usuario."]);
            }
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al registrar el peso."]);
        }
        break;    

    case 'update':
        if (empty($data->id) || empty($data->weight) || empty($data->date)) {
            http_response_code(400);
            echo json_encode(["message" => "Faltan los campos necesarios para actualizar (id, peso, fecha)."]);
            exit();
        }

        $weight->id = intval($data->id);
        $weight->weight = floatval($data->weight);
        $weight->date = htmlspecialchars(strip_tags($data->date));

        if ($weight->update()) {
            http_response_code(200);
            echo json_encode(["message" => "Registro de peso actualizado correctamente."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al actualizar el registro de peso."]);
        }
        break;

    case 'delete':
        if (empty($data->id)) {
            http_response_code(400);
            echo json_encode(["message" => "Falta el id para eliminar el registro."]);
            exit();
        }

        $weight->id = intval($data->id);

        if ($weight->delete()) {
            http_response_code(200);
            echo json_encode(["message" => "Registro de peso eliminado correctamente."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al eliminar el registro de peso."]);
        }
        break;

        case 'getByUserId':
            if (empty($data->id_user)) {
                http_response_code(400);
                echo json_encode(["message" => "Falta el id del usuario para obtener los pesos."]);
                exit();
            }
        
            $weight->id_user = intval($data->id_user);
            $weights = $weight->getByUserId();
        
            if ($weights !== false) {
                http_response_code(200);
                echo json_encode($weights);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error al obtener los registros de peso."]);
            }
            break;
            
    default:
        http_response_code(400);
        echo json_encode(["message" => "Acción no válida."]);
        break;
}
?>
