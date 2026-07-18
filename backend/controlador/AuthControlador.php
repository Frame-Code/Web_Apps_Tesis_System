<?php
require_once __DIR__ . '/../config/Conexion.php';   
require_once __DIR__ . '/../modelo/UsuarioModelo.php';

class AuthControlador {

    public static function registro() {
        $datos = json_decode(file_get_contents('php://input'), true);

        if (!$datos) {
            echo json_encode(['success' => false, 'error' => 'No se recibieron datos válidos']);
            return;
        }

        // Valida que cada campo sea correcto
        foreach (['nombre', 'apellido', 'email', 'password', 'rol'] as $campo) {
            if (empty($datos[$campo])) {
                echo json_encode(['success' => false, 'error' => "El campo '$campo' es requerido"]);
                return;
            }
        }

        // Valida que el rol sea correcto
        if (!in_array($datos['rol'], ['estudiante', 'tutor', 'coordinador'])) {
            echo json_encode(['success' => false, 'error' => 'Rol inválido']);
            return;
        }

        // Solo el coordinador puede crear tutores o coordinadores de forma manual
        if ($datos['rol'] === 'tutor' || $datos['rol'] === 'coordinador') {
            if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'coordinador') {
                echo json_encode(['success' => false, 'error' => 'Solo el coordinador puede registrar este tipo de usuarios']);
                return;
            }
        }

        if (UsuarioModelo::obtenerPorEmail($datos['email'])) {
            echo json_encode(['success' => false, 'error' => 'El correo ya está registrado']);
            return;
        }

        $id = UsuarioModelo::crear($datos);
        $usuario = UsuarioModelo::obtenerPorId($id);

        echo json_encode(['success' => true, 'usuario' => $usuario]);
    }

    public static function login() {
        $datos = json_decode(file_get_contents('php://input'), true);

        if (empty($datos['email']) || empty($datos['password'])) {
            echo json_encode(['success' => false, 'error' => 'Email y contraseña son requeridos']);
            return;
        }

        $usuario = UsuarioModelo::obtenerPorEmail($datos['email']);

        // Valida que el usuario exista y su password coincida con el hash guardado
        if (!$usuario || !password_verify($datos['password'], $usuario['password'])) {
            echo json_encode(['success' => false, 'error' => 'El correo o la contraseña son incorrectos']);
            return;
        }

        if (!$usuario['activo']) {
            echo json_encode(['success' => false, 'error' => 'Cuenta desactivada. Contacte al coordinador']);
            return;
        }

        // Guardar sesión
        $_SESSION['usuario'] = [
            'id'       => $usuario['id'],
            'nombre'   => $usuario['nombre'],
            'apellido' => $usuario['apellido'],
            'email'    => $usuario['email'],
            'rol'      => $usuario['rol'],
        ];

        echo json_encode(['success' => true, 'usuario' => $_SESSION['usuario']]);
    }

    public static function logout() {
        session_unset();
        session_destroy();
        echo json_encode(['success' => true, 'mensaje' => 'Sesión cerrada']);
    }

    // Devuelve el usuario de la sesión activa
    public static function sesionActual() {
        if (isset($_SESSION['usuario'])) {
            echo json_encode(['success' => true, 'usuario' => $_SESSION['usuario']]);
        } else {
            // Se mantiene una respuesta limpia para evitar romper el catch del login inicial
            echo json_encode(['success' => false, 'error' => 'Sin sesión activa']);
        }
    }
}
?>
