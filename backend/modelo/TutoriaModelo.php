<?php
require_once __DIR__ . '/../config/conexion.php';

class TutoriaModelo {

    public static function obtenerTodos($filtros = []) {
        $conn   = Conexion::conectar();
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['proyecto_id'])) {
            $where[]               = 't.proyecto_id = :proyecto_id';
            $params[':proyecto_id'] = $filtros['proyecto_id'];
        }
        if (!empty($filtros['tutor_id'])) {
            $where[]            = 't.tutor_id = :tutor_id';
            $params[':tutor_id'] = $filtros['tutor_id'];
        }
        if (!empty($filtros['estado'])) {
            $where[]          = 't.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }

        $sql = "SELECT t.*, p.titulo AS proyecto_titulo, CONCAT(u.nombre,' ',u.apellido) AS tutor_nombre
                FROM tutorias t
                JOIN proyectos p ON t.proyecto_id = p.id
                JOIN usuarios  u ON t.tutor_id    = u.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY t.fecha_hora DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function obtenerPorId($id) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            "SELECT t.*, p.titulo AS proyecto_titulo, CONCAT(u.nombre,' ',u.apellido) AS tutor_nombre
             FROM tutorias t
             JOIN proyectos p ON t.proyecto_id = p.id
             JOIN usuarios  u ON t.tutor_id    = u.id
             WHERE t.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function crear($datos) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            'INSERT INTO tutorias (proyecto_id, tutor_id, fecha_hora, duracion_min, modalidad, estado, observaciones)
             VALUES (:proyecto_id, :tutor_id, :fecha_hora, :duracion_min, :modalidad, :estado, :observaciones)'
        );
        $stmt->execute([
            ':proyecto_id'  => $datos['proyecto_id'],
            ':tutor_id'     => $datos['tutor_id'],
            ':fecha_hora'   => $datos['fecha_hora'],
            ':duracion_min' => $datos['duracion_min'],
            ':modalidad'    => $datos['modalidad']    ?? 'virtual',
            ':estado'       => $datos['estado']       ?? 'programada',
            ':observaciones'=> $datos['observaciones'] ?? null,
        ]);
        return $conn->lastInsertId();
    }

    public static function editar($id, $datos) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            'UPDATE tutorias
             SET proyecto_id=:proyecto_id, tutor_id=:tutor_id, fecha_hora=:fecha_hora,
                 duracion_min=:duracion_min, modalidad=:modalidad, observaciones=:observaciones
             WHERE id=:id'
        );
        return $stmt->execute([
            ':proyecto_id'   => $datos['proyecto_id'],
            ':tutor_id'      => $datos['tutor_id'],
            ':fecha_hora'    => $datos['fecha_hora'],
            ':duracion_min'  => $datos['duracion_min'],
            ':modalidad'     => $datos['modalidad'],
            ':observaciones' => $datos['observaciones'] ?? null,
            ':id'            => $id,
        ]);
    }

    public static function cambiarEstado($id, $estado, $observaciones = null) {
        $conn = Conexion::conectar();
        if ($observaciones !== null) {
            $stmt = $conn->prepare('UPDATE tutorias SET estado=?, observaciones=? WHERE id=?');
            return $stmt->execute([$estado, $observaciones, $id]);
        }
        $stmt = $conn->prepare('UPDATE tutorias SET estado=? WHERE id=?');
        return $stmt->execute([$estado, $id]);
    }

    public static function eliminar($id) {
        $conn = Conexion::conectar();
        // Solo se puede eliminar si está programada
        $stmt = $conn->prepare("DELETE FROM tutorias WHERE id=? AND estado='programada'");
        return $stmt->execute([$id]);
    }

    // Cuenta tutorías presenciales no canceladas de un proyecto (límite: 4)
    public static function contarPresenciales($proyectoId) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            "SELECT COUNT(*) FROM tutorias
             WHERE proyecto_id=? AND modalidad='presencial' AND estado != 'cancelada'"
        );
        $stmt->execute([$proyectoId]);
        return (int) $stmt->fetchColumn();
    }

    // Total de horas de tutorías realizadas de un proyecto
    public static function totalHoras($proyectoId) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            "SELECT COALESCE(SUM(duracion_min), 0) / 60.0 FROM tutorias
             WHERE proyecto_id=? AND estado='realizada'"
        );
        $stmt->execute([$proyectoId]);
        return round((float) $stmt->fetchColumn(), 2);
    }
}
?>
