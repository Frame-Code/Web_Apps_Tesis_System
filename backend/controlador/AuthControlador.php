<?php
require_once __DIR__ . '/../modelo/UsuarioModelo.php';

class AuthControlador {

    public static function registro() {
        $datos = json_decode(file_get_contents('php://input'), true);

        //Valida que cada campo sea correcto
        foreach (['nombre', 'apellido', 'email', 'password', 'rol'] as $campo) {
            if (empty($datos[$campo])) {
                echo json_encode(['error' => "El campo '$campo' es requerido"]);
                return;
            }
        }

        //Valida que el rol sea correcto
        if (!in_array($datos['rol'], ['estudiante', 'tutor', 'coordinador'])) {
            echo json_encode(['error' => 'Rol inválido']);
            return;
        }

        // Solo el coordinador puede crear tutores
        if ($datos['rol'] === 'tutor') {
            if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'coordinador') {
                http_response_code(403);
                echo json_encode(['error' => 'Solo el coordinador puede registrar tutores']);
                return;
            }
        }

        if (UsuarioModelo::obtenerPorEmail($datos['email'])) {
            http_response_code(409);
            echo json_encode(['error' => 'El correo ya está registrado']);
            return;
        }

        $id = UsuarioModelo::crear($datos);
        $usuario = UsuarioModelo::obtenerPorId($id);

        http_response_code(201);
        echo json_encode(['success' => true, 'data' => $usuario]);
    }

    public static function login() {
        $datos = json_decode(file_get_contents('php://input'), true);

        if (empty($datos['email']) || empty($datos['password'])) {
            echo json_encode(['error' => 'Email y contraseña son requeridos']);
            return;
        }

        $usuario = UsuarioModelo::obtenerPorEmail($datos['email']);

        //Valida que el usuario exista y su password coincida con el hash guardado
        if (!$usuario || !password_verify($datos['password'], $usuario['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Credenciales incorrectas']);
            return;
        }

        if (!$usuario['activo']) {
            http_response_code(403);
            echo json_encode(['error' => 'Cuenta desactivada. Contacte al coordinador']);
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

        echo json_encode(['success' => true, 'data' => $_SESSION['usuario']]);
    }

    public static function logout() {
        session_unset();
        session_destroy();
        echo json_encode(['success' => true, 'mensaje' => 'Sesión cerrada']);
    }

    // Devuelve el usuario de la sesión activa 
    public static function sesionActual() {
        if (isset($_SESSION['usuario'])) {
            echo json_encode(['success' => true, 'data' => $_SESSION['usuario']]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Sin sesión activa']);
        }
    }
}
?>
