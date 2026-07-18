<?php
require_once __DIR__ . '/../config/conexion.php';

class AvanceModelo {

    public static function obtenerTodos($filtros = []) {
        $conn   = Conexion::conectar();
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['proyecto_id'])) {
            $where[]               = 'a.proyecto_id = :proyecto_id';
            $params[':proyecto_id'] = $filtros['proyecto_id'];
        }
        if (!empty($filtros['estado'])) {
            $where[]          = 'a.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        $sql = "SELECT a.*,
                       p.titulo AS proyecto_titulo
                FROM avances a
                JOIN proyectos p ON a.proyecto_id = p.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY a.fecha_registro DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function obtenerPorId($id) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            "SELECT a.*,
                    p.titulo AS proyecto_titulo
             FROM avances a
             JOIN proyectos p ON a.proyecto_id = p.id
             WHERE a.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function crear($datos) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            'INSERT INTO avances (proyecto_id, descripcion, porcentaje_avance, estado, comentario_tutor, archivo_url)
             VALUES (:proyecto_id, :descripcion, :porcentaje_avance, :estado, :comentario_tutor, :archivo_url)'
        );
        $stmt->execute([
            ':proyecto_id'       => $datos['proyecto_id'],
            ':descripcion'       => $datos['descripcion'],
            ':porcentaje_avance' => $datos['porcentaje_avance'] ?? 0,
            ':estado'            => $datos['estado']            ?? 'pendiente',
            ':comentario_tutor'  => $datos['comentario_tutor']  ?? null,
            ':archivo_url'       => $datos['archivo_url']       ?? null,
        ]);
        return $conn->lastInsertId();
    }

    public static function editar($id, $datos) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            'UPDATE avances
             SET proyecto_id=:proyecto_id, descripcion=:descripcion,
                 porcentaje_avance=:porcentaje_avance, estado=:estado,
                 comentario_tutor=:comentario_tutor, archivo_url=:archivo_url
             WHERE id=:id'
        );
        return $stmt->execute([
            ':proyecto_id'       => $datos['proyecto_id'],
            ':descripcion'       => $datos['descripcion'],
            ':porcentaje_avance' => $datos['porcentaje_avance'],
            ':estado'            => $datos['estado'],
            ':comentario_tutor'  => $datos['comentario_tutor'] ?? null,
            ':archivo_url'       => $datos['archivo_url']      ?? null,
            ':id'                => $id,
        ]);
    }

    public static function eliminar($id) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare('DELETE FROM avances WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
?>
