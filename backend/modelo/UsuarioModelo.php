<?php
require_once __DIR__ . '/../config/conexion.php';

class UsuarioModelo {

    public static function obtenerPorId($id) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare('SELECT id, nombre, apellido, email, rol, activo, created_at FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function obtenerPorEmail($email) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare('SELECT * FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public static function crear($datos) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            'INSERT INTO usuarios (nombre, apellido, email, password, rol)
             VALUES (:nombre, :apellido, :email, :password, :rol)'
        );
        $stmt->execute([
            ':nombre'   => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':email'    => $datos['email'],
            ':password' => password_hash($datos['password'], PASSWORD_BCRYPT),
            ':rol'      => $datos['rol'],
        ]);
        return $conn->lastInsertId();
    }

    public static function obtenerTodos($filtros = []) {
        $conn   = Conexion::conectar();
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['rol'])) {
            $where[]        = 'rol = :rol';
            $params[':rol'] = $filtros['rol'];
        }
        if (isset($filtros['activo'])) {
            $where[]           = 'activo = :activo';
            $params[':activo'] = $filtros['activo'];
        }

        $sql  = 'SELECT id, nombre, apellido, email, rol, activo, created_at FROM usuarios WHERE '
              . implode(' AND ', $where) . ' ORDER BY nombre, apellido';
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function obtenerTutores() {
        return self::obtenerTodos(['rol' => 'tutor', 'activo' => 1]);
    }

    public static function obtenerEstudiantes() {
        return self::obtenerTodos(['rol' => 'estudiante', 'activo' => 1]);
    }

    public static function editar($id, $datos) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            'UPDATE usuarios SET nombre=:nombre, apellido=:apellido, email=:email, rol=:rol WHERE id=:id'
        );
        return $stmt->execute([
            ':nombre'   => $datos['nombre'],
            ':apellido' => $datos['apellido'],
            ':email'    => $datos['email'],
            ':rol'      => $datos['rol'],
            ':id'       => $id,
        ]);
    }

    public static function desactivar($id) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare('UPDATE usuarios SET activo = 0 WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
?>
