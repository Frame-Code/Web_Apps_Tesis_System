<?php
require_once __DIR__ . '/../modelo/UsuarioModelo.php';

class UsuarioControlador {

    private static function verificarSesion() {
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return false;
        }
        return true;
    }

    // Lista todos los usuarios (solo coordinador)
    public static function listar() {
        if (!self::verificarSesion()) return;

        if ($_SESSION['usuario']['rol'] !== 'coordinador') {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            return;
        }

        $filtros = array_filter([
            'rol'    => $_GET['rol']    ?? null,
            'activo' => isset($_GET['activo']) ? (int)$_GET['activo'] : null,
        ], fn($v) => !is_null($v));

        echo json_encode(['success' => true, 'data' => UsuarioModelo::obtenerTodos($filtros)]);
    }

    // Lista solo tutores activos (para selects en formularios)
    public static function listarTutores() {
        if (!self::verificarSesion()) return;
        echo json_encode(['success' => true, 'data' => UsuarioModelo::obtenerTutores()]);
    }

    // Lista solo estudiantes activos (para selects en formularios)
    public static function listarEstudiantes() {
        if (!self::verificarSesion()) return;
        echo json_encode(['success' => true, 'data' => UsuarioModelo::obtenerEstudiantes()]);
    }

    // Ver un usuario por id
    public static function ver() {
        if (!self::verificarSesion()) return;

        $id      = (int) ($_GET['id'] ?? 0);
        $usuario = UsuarioModelo::obtenerPorId($id);

        if (!$usuario) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            return;
        }

        echo json_encode(['success' => true, 'data' => $usuario]);
    }

    // Editar usuario (coordinador o el propio usuario)
    public static function editar() {
        if (!self::verificarSesion()) return;

        $id      = (int) ($_GET['id'] ?? 0);
        $sesion  = $_SESSION['usuario'];
        $datos   = json_decode(file_get_contents('php://input'), true);

        if ($sesion['rol'] !== 'coordinador' && $sesion['id'] !== $id) {
            http_response_code(403);
            echo json_encode(['error' => 'No puede editar este usuario']);
            return;
        }

        if (!UsuarioModelo::obtenerPorId($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            return;
        }

        UsuarioModelo::editar($id, $datos);
        echo json_encode(['success' => true, 'data' => UsuarioModelo::obtenerPorId($id)]);
    }

    // Desactivar usuario (solo coordinador)
    public static function desactivar() {
        if (!self::verificarSesion()) return;

        if ($_SESSION['usuario']['rol'] !== 'coordinador') {
            http_response_code(403);
            echo json_encode(['error' => 'Solo el coordinador puede desactivar usuarios']);
            return;
        }

        $id = (int) ($_GET['id'] ?? 0);
        UsuarioModelo::desactivar($id);
        echo json_encode(['success' => true, 'mensaje' => 'Usuario desactivado']);
    }
}
?>
