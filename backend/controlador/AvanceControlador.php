<?php
require_once __DIR__ . '/../modelo/AvanceModelo.php';

class AvanceControlador {

    private static function verificarSesion() {
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return false;
        }
        return true;
    }

    // Listar avances con filtros opcionales (proyecto_id, estado)
    public static function listar() {
        if (!self::verificarSesion()) return;

        $filtros = array_filter([
            'proyecto_id' => $_GET['proyecto_id'] ?? null,
            'estado'      => $_GET['estado']       ?? null,
        ], fn($v) => !is_null($v));

        echo json_encode(['success' => true, 'data' => AvanceModelo::obtenerTodos($filtros)]);
    }

    // Ver un avance por id
    public static function ver() {
        if (!self::verificarSesion()) return;

        $id     = (int) ($_GET['id'] ?? 0);
        $avance = AvanceModelo::obtenerPorId($id);

        if (!$avance) {
            http_response_code(404);
            echo json_encode(['error' => 'Avance no encontrado']);
            return;
        }

        echo json_encode(['success' => true, 'data' => $avance]);
    }

    // Crear avance (cualquier usuario autenticado)
    public static function crear() {
        if (!self::verificarSesion()) return;

        $datos = json_decode(file_get_contents('php://input'), true);

        if (empty($datos['proyecto_id']) || empty($datos['descripcion'])) {
            http_response_code(400);
            echo json_encode(['error' => 'El proyecto y la descripción son requeridos']);
            return;
        }

        $porcentaje = (int) ($datos['porcentaje_avance'] ?? 0);
        if ($porcentaje < 0 || $porcentaje > 100) {
            http_response_code(400);
            echo json_encode(['error' => 'El porcentaje debe estar entre 0 y 100']);
            return;
        }

        $id = AvanceModelo::crear($datos);
        http_response_code(201);
        echo json_encode(['success' => true, 'data' => AvanceModelo::obtenerPorId($id)]);
    }

    // Editar avance (coordinador o tutor)
    public static function editar() {
        if (!self::verificarSesion()) return;

        $sesion = $_SESSION['usuario'];

        if (!in_array($sesion['rol'], ['coordinador', 'tutor'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Solo coordinadores y tutores pueden editar avances']);
            return;
        }

        $id     = (int) ($_GET['id'] ?? 0);
        $avance = AvanceModelo::obtenerPorId($id);

        if (!$avance) {
            http_response_code(404);
            echo json_encode(['error' => 'Avance no encontrado']);
            return;
        }

        $datos      = json_decode(file_get_contents('php://input'), true);
        $porcentaje = (int) ($datos['porcentaje_avance'] ?? 0);

        if ($porcentaje < 0 || $porcentaje > 100) {
            http_response_code(400);
            echo json_encode(['error' => 'El porcentaje debe estar entre 0 y 100']);
            return;
        }

        AvanceModelo::editar($id, $datos);
        echo json_encode(['success' => true, 'data' => AvanceModelo::obtenerPorId($id)]);
    }

    // Eliminar avance (solo coordinador)
    public static function eliminar() {
        if (!self::verificarSesion()) return;

        $sesion = $_SESSION['usuario'];

        if ($sesion['rol'] !== 'coordinador') {
            http_response_code(403);
            echo json_encode(['error' => 'Solo el coordinador puede eliminar avances']);
            return;
        }

        $id     = (int) ($_GET['id'] ?? 0);
        $avance = AvanceModelo::obtenerPorId($id);

        if (!$avance) {
            http_response_code(404);
            echo json_encode(['error' => 'Avance no encontrado']);
            return;
        }

        AvanceModelo::eliminar($id);
        echo json_encode(['success' => true, 'mensaje' => 'Avance eliminado']);
    }
}
?>
