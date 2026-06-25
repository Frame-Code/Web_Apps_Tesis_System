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
require_once __DIR__ . '/controlador/UsuarioControlador.php';
require_once __DIR__ . '/controlador/ProyectoControlador.php';
require_once __DIR__ . '/controlador/TutoriaControlador.php';

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

    // ── USUARIOS ─────────────────────────────────
    case 'listar_usuarios':
        UsuarioControlador::listar();
        break;
    case 'listar_tutores':
        UsuarioControlador::listarTutores();
        break;
    case 'listar_estudiantes':
        UsuarioControlador::listarEstudiantes();
        break;
    case 'ver_usuario':
        UsuarioControlador::ver();
        break;
    case 'editar_usuario':
        UsuarioControlador::editar();
        break;
    case 'desactivar_usuario':
        UsuarioControlador::desactivar();
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

    // ── TUTORÍAS ─────────────────────────────────
    case 'listar_tutorias':
        TutoriaControlador::listar();
        break;
    case 'tutorias_por_tutor':
        TutoriaControlador::porTutor();
        break;
    case 'ver_tutoria':
        TutoriaControlador::ver();
        break;
    case 'crear_tutoria':
        TutoriaControlador::crear();
        break;
    case 'editar_tutoria':
        TutoriaControlador::editar();
        break;
    case 'cambiar_estado_tutoria':
        TutoriaControlador::cambiarEstado();
        break;
    case 'eliminar_tutoria':
        TutoriaControlador::eliminar();
        break;

    // Default
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Acción no encontrada']);
        break;
}
?>
