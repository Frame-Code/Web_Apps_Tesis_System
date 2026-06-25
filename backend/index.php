<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/controlador/AuthControlador.php';
require_once __DIR__ . '/controlador/ProyectoControlador.php';

$accion = $_GET['accion'] ?? 'default';

switch ($accion) {

    // ── AUTH ─────────────────────────────────────
    case 'registro':
        AuthControlador::registro();
        break;
    case 'login':
        AuthControlador::login();
        break;
    case 'logout':
        AuthControlador::logout();
        break;
    case 'sesion':
        AuthControlador::sesionActual();
        break;

    // ── PROYECTOS ────────────────────────────────
    case 'listar_proyectos':
        ProyectoControlador::listar();
        break;
    case 'ver_proyecto':
        ProyectoControlador::ver();
        break;
    case 'crear_proyecto':
        ProyectoControlador::crear();
        break;
    case 'editar_proyecto':
        ProyectoControlador::editar();
        break;
    case 'cambiar_estado_proyecto':
        ProyectoControlador::cambiarEstado();
        break;
    case 'asignar_tutor':
        ProyectoControlador::asignarTutor();
        break;
    case 'archivar_proyecto':
        ProyectoControlador::archivar();
        break;

    // Default
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Acción no encontrada']);
        break;
}
?>
