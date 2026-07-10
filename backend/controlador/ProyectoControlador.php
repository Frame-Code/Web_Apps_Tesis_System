<?php
require_once __DIR__ . '/../modelo/ProyectoModelo.php';

class ProyectoControlador {

    private static function validateSession() {
        //Si no existe la sesion de usuario retorn 401 unauthorized
        if (!isset($_SESSION['usuario'])) { 
            http_response_code(401); 
            echo json_encode(['error' => 'No autenticado']); 
            return false; 
        } 

        return true;
    }

    public static function listar() {
        if(!self::validateSession())
            return;

        //Crea un array de filtros dependendiendo el que venta en la peticion GET
        $filtros = array_filter([
            'estado'   => $_GET['estado']   ?? null,
            'tutor_id' => $_GET['tutor_id'] ?? null,
            'area'     => $_GET['area']     ?? null,
        ]);

        //Retorna en formato json todos los que coinciden el filtro
        echo json_encode(['success' => true, 'data' => ProyectoModelo::obtenerTodos($filtros)]);
    }

    public static function ver() {
        if(!self::validateSession())
            return;

        //Obtiene el id del proyecto
        $id       = (int) ($_GET['id'] ?? 0);
        $proyecto = ProyectoModelo::obtenerPorId($id);

        //Si no existe el proyecto se devuelve 404 not found
        if (!$proyecto) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Proyecto no encontrado']); 
            return; 
        }

        $proyecto['historial'] = ProyectoModelo::obtenerHistorial($id);
        echo json_encode(['success' => true, 'data' => $proyecto]);
    }

    public static function crear() {
        if(!self::validateSession())
            return;

        //Devuelve el cuerpo de peticion
        $datos = json_decode(file_get_contents('php://input'), true);

        //Valida si cada campo que se obtuvo en la peticion es valido
        foreach (['titulo', 'area_conocimiento', 'fecha_inicio', 'estudiante_id'] as $campo) {
            if (empty($datos[$campo])) { 
                echo json_encode(['error' => "El campo '$campo' es requerido"]); 
                return; 
            }
        }

        //Valida si el estudiante ya tiene un proyecto asignado que este activo
        if (ProyectoModelo::obtenerActivoPorEstudiante($datos['estudiante_id'])) {
            http_response_code(409);
            echo json_encode(['error' => 'El estudiante ya tiene un proyecto activo']);
            return;
        }

        $id = ProyectoModelo::crear($datos);
        http_response_code(201);
        echo json_encode(['success' => true, 'data' => ProyectoModelo::obtenerPorId($id)]);
    }

    public static function editar() {
        if(!self::validateSession())
            return;

        //Valida que  el usuario que quiere editar el proyecto es coordinador ni tutor (solo ellos pueden editar un proyecto)
        if (!in_array($_SESSION['usuario']['rol'], ['coordinador', 'tutor'])) {
            http_response_code(403); 
            echo json_encode(['error' => 'Acceso denegado']); 
            return;
        }

        $id    = (int) ($_GET['id'] ?? 0);
        $datos = json_decode(file_get_contents('php://input'), true);

        if (!ProyectoModelo::obtenerPorId($id)) { 
            http_response_code(404); 
            echo json_encode(['error' => 'Proyecto no encontrado']); 
            return; 
        }

        ProyectoModelo::editar($id, $datos);
        echo json_encode(['success' => true, 'data' => ProyectoModelo::obtenerPorId($id)]);
    }

    public static function cambiarEstado() {
        if(!self::validateSession())
            return;

        $id       = (int) ($_GET['id'] ?? 0);
        $datos    = json_decode(file_get_contents('php://input'), true);
        $proyecto = ProyectoModelo::obtenerPorId($id);

        if (!$proyecto) {
            http_response_code(404);
            echo json_encode(['error' => 'Proyecto no encontrado']);
            return;
        }

        if (empty($datos['estado'])) {
            echo json_encode(['error' => 'El estado es requerido']);
            return;
        }

        if ($_SESSION['usuario']['rol'] == 'estudiante') {
            http_response_code(403);
            echo json_encode(['error' => 'Solo el coordinador o tutor puede cambiar el estado']);
            return;
        }

        ProyectoModelo::cambiarEstado($id, $datos['estado'], $_SESSION['usuario']['id'], $datos['motivo'] ?? null);
        echo json_encode(['success' => true, 'data' => ProyectoModelo::obtenerPorId($id)]);
    }

    public static function asignarTutor() {
        if(!self::validateSession())
            return;

        //Si no es coordinador no puede asignar un tutor
        if ($_SESSION['usuario']['rol'] !== 'coordinador') {
            http_response_code(403); 
            echo json_encode(['error' => 'Solo el coordinador puede asignar tutores']); 
            return;
        }

        $id    = (int) ($_GET['id'] ?? 0);
        $datos = json_decode(file_get_contents('php://input'), true);

        if (empty($datos['tutor_id'])) { 
            echo json_encode(['error' => 'tutor_id es requerido']); 
            return; 
        }

        ProyectoModelo::asignarTutor($id, $datos['tutor_id']);
        echo json_encode(['success' => true, 'data' => ProyectoModelo::obtenerPorId($id)]);
    }

    public static function archivar() {
        if(!self::validateSession())
            return;

        //Si no es coordinadoor no puede archivar el proyecto
        if ($_SESSION['usuario']['rol'] !== 'coordinador') {
            http_response_code(403); 
            echo json_encode(['error' => 'Solo el coordinador puede archivar proyectos']); 
            return;
        }

        $id = (int) ($_GET['id'] ?? 0);
        ProyectoModelo::archivar($id);
        echo json_encode(['success' => true, 'mensaje' => 'Proyecto archivado']);
    }
}
?>
