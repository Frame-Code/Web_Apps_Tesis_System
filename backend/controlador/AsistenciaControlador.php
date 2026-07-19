<?php

require_once __DIR__ . '/../modelo/AsistenciaModelo.php';

class AsistenciaControlador
{

    private static function validateSession()
    {
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return false;
        }

        return true;
    }

    public static function listar()
    {
        if (!self::validateSession())
            return;

        echo json_encode([
            'success' => true,
            'data' => AsistenciaModelo::obtenerTodos()
        ]);
    }

    public static function ver()
    {
        if (!self::validateSession())
            return;

        $id = (int)($_GET['id'] ?? 0);

        $asistencia = AsistenciaModelo::obtenerPorId($id);

        if (!$asistencia) {
            http_response_code(404);
            echo json_encode([
                'error' => 'Asistencia no encontrada'
            ]);
            return;
        }

        echo json_encode([
            'success' => true,
            'data' => $asistencia
        ]);
    }

    public static function crear()
    {
        if (!self::validateSession())
            return;

        $datos = json_decode(file_get_contents("php://input"), true);

        if (
            empty($datos['proyecto_id']) ||
            empty($datos['fecha']) ||
            empty($datos['estado']) ||
            empty($datos['modalidad'])
        ) {

            echo json_encode([
                'error' => 'Complete todos los campos obligatorios.'
            ]);

            return;
        }

        $proyecto = AsistenciaModelo::obtenerProyecto($datos['proyecto_id']);

        if (!$proyecto) {

            http_response_code(404);

            echo json_encode([
                'error' => 'Proyecto no encontrado.'
            ]);

            return;
        }

        if (empty($proyecto['tutor_id'])) {

            http_response_code(400);

            echo json_encode([
                'error' => 'Este proyecto todavía no tiene un tutor asignado.'
            ]);

            return;
        }

        $datos['estudiante_id'] = $proyecto['estudiante_id'];
        $datos['tutor_id'] = $proyecto['tutor_id'];

        $id = AsistenciaModelo::crear($datos);

        echo json_encode([
            'success' => true,
            'data' => AsistenciaModelo::obtenerPorId($id)
        ]);
    }

    public static function editar()
    {
        if (!self::validateSession())
            return;

        $id = (int)($_GET['id'] ?? 0);

        $datos = json_decode(file_get_contents("php://input"), true);

        $proyecto = AsistenciaModelo::obtenerProyecto($datos['proyecto_id']);

        if (!$proyecto) {

            echo json_encode([
                'error' => 'Proyecto no encontrado.'
            ]);

            return;
        }

        $datos['estudiante_id'] = $proyecto['estudiante_id'];
        $datos['tutor_id'] = $proyecto['tutor_id'];

        AsistenciaModelo::editar($id, $datos);

        echo json_encode([
            'success' => true
        ]);
    }

    public static function eliminar()
    {
        if (!self::validateSession())
            return;

        $id = (int)($_GET['id'] ?? 0);

        AsistenciaModelo::eliminar($id);

        echo json_encode([
            'success' => true
        ]);
    }

}
?>