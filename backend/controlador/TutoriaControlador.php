<?php
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../modelo/TutoriaModelo.php';

class TutoriaControlador {

    private static function verificarSesion() {
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return false;
        }
        return true;
    }

    // Lista todas las tutorías, con filtros opcionales
    public static function listar() {
        if (!self::verificarSesion()) return;

        $filtros = array_filter([
            'proyecto_id' => $_GET['proyecto_id'] ?? null,
            'tutor_id'    => $_GET['tutor_id']    ?? null,
            'estado'      => $_GET['estado']       ?? null,
        ], fn($v) => !is_null($v));

        echo json_encode(['success' => true, 'data' => TutoriaModelo::obtenerTodos($filtros)]);
    }

    // Lista las tutorías del tutor en sesión
    public static function porTutor() {
        if (!self::verificarSesion()) return;

        $filtros = ['tutor_id' => $_SESSION['usuario']['id']];
        echo json_encode(['success' => true, 'data' => TutoriaModelo::obtenerTodos($filtros)]);
    }

    // Ver una tutoría por id
    public static function ver() {
        if (!self::verificarSesion()) return;

        $id      = (int) ($_GET['id'] ?? 0);
        $tutoria = TutoriaModelo::obtenerPorId($id);

        if (!$tutoria) {
            http_response_code(404);
            echo json_encode(['error' => 'Tutoría no encontrada']);
            return;
        }

        echo json_encode(['success' => true, 'data' => $tutoria]);
    }

    // Crear tutoría (tutor o coordinador)
    public static function crear() {
        if (!self::verificarSesion()) return;

        $sesion = $_SESSION['usuario'];

        if (!in_array($sesion['rol'], ['tutor', 'coordinador'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Solo tutores y coordinadores pueden crear tutorías']);
            return;
        }

        $datos = json_decode(file_get_contents('php://input'), true);

        // Validar campos requeridos
        foreach (['proyecto_id', 'tutor_id', 'fecha_hora', 'duracion_min'] as $campo) {
            if (empty($datos[$campo])) {
                echo json_encode(['error' => "El campo '$campo' es requerido"]);
                return;
            }
        }

        // Validar que la fecha sea futura
        if (strtotime($datos['fecha_hora']) <= time()) {
            echo json_encode(['error' => 'La fecha y hora debe ser en el futuro']);
            return;
        }

        // Validar límite de 4 tutorías presenciales por proyecto
        if (($datos['modalidad'] ?? 'virtual') === 'presencial') {
            if (TutoriaModelo::contarPresenciales($datos['proyecto_id']) >= 4) {
                http_response_code(409);
                echo json_encode(['error' => 'Se alcanzó el límite de 4 tutorías presenciales para este proyecto']);
                return;
            }
        }

        $id = TutoriaModelo::crear($datos);
        http_response_code(201);
        echo json_encode(['success' => true, 'data' => TutoriaModelo::obtenerPorId($id)]);
    }

    // Editar tutoría (tutor o coordinador)
    public static function editar() {
        if (!self::verificarSesion()) return;

        $sesion = $_SESSION['usuario'];

        if (!in_array($sesion['rol'], ['tutor', 'coordinador'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Solo tutores y coordinadores pueden editar tutorías']);
            return;
        }

        $id      = (int) ($_GET['id'] ?? 0);
        $datos   = json_decode(file_get_contents('php://input'), true);
        $tutoria = TutoriaModelo::obtenerPorId($id);

        if (!$tutoria) {
            http_response_code(404);
            echo json_encode(['error' => 'Tutoría no encontrada']);
            return;
        }

        // Si cambia a presencial, revisar límite (sin contar la actual)
        $modalidadNueva  = $datos['modalidad'] ?? $tutoria['modalidad'];
        $modalidadActual = $tutoria['modalidad'];
        if ($modalidadNueva === 'presencial' && $modalidadActual !== 'presencial') {
            if (TutoriaModelo::contarPresenciales($tutoria['proyecto_id']) >= 4) {
                http_response_code(409);
                echo json_encode(['error' => 'Se alcanzó el límite de 4 tutorías presenciales para este proyecto']);
                return;
            }
        }

        TutoriaModelo::editar($id, $datos);
        echo json_encode(['success' => true, 'data' => TutoriaModelo::obtenerPorId($id)]);
    }

    // Cambiar estado (tutor o coordinador)
    public static function cambiarEstado() {
        if (!self::verificarSesion()) return;

        $sesion = $_SESSION['usuario'];

        if (!in_array($sesion['rol'], ['tutor', 'coordinador'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Solo tutores y coordinadores pueden cambiar el estado']);
            return;
        }

        $id    = (int) ($_GET['id'] ?? 0);
        $datos = json_decode(file_get_contents('php://input'), true);

        if (!TutoriaModelo::obtenerPorId($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Tutoría no encontrada']);
            return;
        }

        if (empty($datos['estado'])) {
            echo json_encode(['error' => 'El estado es requerido']);
            return;
        }

        TutoriaModelo::cambiarEstado($id, $datos['estado'], $datos['observaciones'] ?? null);
        echo json_encode(['success' => true, 'data' => TutoriaModelo::obtenerPorId($id)]);
    }

    // Eliminar tutoría (tutor o coordinador, solo si está programada)
    public static function eliminar() {
        if (!self::verificarSesion()) return;

        $sesion = $_SESSION['usuario'];

        if (!in_array($sesion['rol'], ['tutor', 'coordinador'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Solo tutores y coordinadores pueden eliminar tutorías']);
            return;
        }

        $id      = (int) ($_GET['id'] ?? 0);
        $tutoria = TutoriaModelo::obtenerPorId($id);

        if (!$tutoria) {
            http_response_code(404);
            echo json_encode(['error' => 'Tutoría no encontrada']);
            return;
        }

        if ($tutoria['estado'] !== 'programada') {
            http_response_code(409);
            echo json_encode(['error' => 'Solo se pueden eliminar tutorías en estado "programada"']);
            return;
        }

        TutoriaModelo::eliminar($id);
        echo json_encode(['success' => true, 'mensaje' => 'Tutoría eliminada']);
    }
}
?>
