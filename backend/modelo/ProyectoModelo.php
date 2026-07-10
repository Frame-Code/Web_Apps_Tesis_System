<?php
require_once __DIR__ . '/../config/conexion.php';

class ProyectoModelo {

    public static function obtenerTodos($filtros = []) {
        $conn   = Conexion::conectar();
        $where  = ['1=1'];
        $params = [];

        if (!empty($filtros['estado'])) {
            $where[] = 'p.estado = :estado';
            $params[':estado'] = $filtros['estado'];
        }
        if (!empty($filtros['tutor_id'])) {
            $where[] = 'p.tutor_id = :tutor_id';
            $params[':tutor_id'] = $filtros['tutor_id'];
        }
        if (!empty($filtros['area'])) {
            $where[] = 'p.area_conocimiento LIKE :area';
            $params[':area'] = '%'.$filtros['area'].'%';
        }

        //Devuelve la informacion del proyecto junto con el nombre del estudiante y del tutor
        $sql  = "SELECT p.*, CONCAT(e.nombre,' ',e.apellido) AS estudiante_nombre, CONCAT(t.nombre,' ',t.apellido)      AS tutor_nombre
                 FROM proyectos p
                 LEFT JOIN usuarios e ON p.estudiante_id = e.id
                 LEFT JOIN usuarios t ON p.tutor_id      = t.id
                 WHERE " . implode(' AND ', $where);

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function obtenerPorId($id) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            "SELECT p.*, CONCAT(e.nombre,' ',e.apellido) AS estudiante_nombre, CONCAT(t.nombre,' ',t.apellido)      AS tutor_nombre
             FROM proyectos p
             LEFT JOIN usuarios e ON p.estudiante_id = e.id
             LEFT JOIN usuarios t ON p.tutor_id      = t.id
             WHERE p.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function obtenerActivoPorEstudiante($estudianteId) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            "SELECT * FROM proyectos WHERE estudiante_id = ? AND estado NOT IN ('archivado','finalizado')"
        );
        $stmt->execute([$estudianteId]);
        return $stmt->fetch();
    }

    public static function crear($datos) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            'INSERT INTO proyectos (titulo, descripcion, area_conocimiento, estado, fecha_inicio, fecha_fin_estimada, estudiante_id, tutor_id)
             VALUES (:titulo, :descripcion, :area_conocimiento, :estado, :fecha_inicio, :fecha_fin_estimada, :estudiante_id, :tutor_id)'
        );
        $stmt->execute([
            ':titulo'             => $datos['titulo'],
            ':descripcion'        => $datos['descripcion'] ?? null,
            ':area_conocimiento'  => $datos['area_conocimiento'],
            ':estado'             => $datos['estado'] ?? 'borrador',
            ':fecha_inicio'       => $datos['fecha_inicio'],
            ':fecha_fin_estimada' => $datos['fecha_fin_estimada'] ?? null,
            ':estudiante_id'      => $datos['estudiante_id'],
            ':tutor_id'           => $datos['tutor_id'] ?? null,
        ]);
        return $conn->lastInsertId();
    }

    public static function editar($id, $datos) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            'UPDATE proyectos SET titulo=:titulo, descripcion=:descripcion,
             area_conocimiento=:area_conocimiento, fecha_inicio=:fecha_inicio,
             fecha_fin_estimada=:fecha_fin_estimada WHERE id=:id'
        );
        return $stmt->execute([
            ':titulo'             => $datos['titulo'],
            ':descripcion'        => $datos['descripcion'] ?? null,
            ':area_conocimiento'  => $datos['area_conocimiento'],
            ':fecha_inicio'       => $datos['fecha_inicio'],
            ':fecha_fin_estimada' => $datos['fecha_fin_estimada'] ?? null,
            ':id'                 => $id,
        ]);
    }

    public static function cambiarEstado($id, $estadoNuevo, $cambiadoPor, $motivo = null) {
        $conn     = Conexion::conectar();
        $proyecto = self::obtenerPorId($id);
        if (!$proyecto) 
            return false;

        $hist = $conn->prepare(
            'INSERT INTO historial_estado (proyecto_id, estado_anterior, estado_nuevo, cambiado_por, motivo)
             VALUES (?, ?, ?, ?, ?)'
        );
        $hist->execute([$id, $proyecto['estado'], $estadoNuevo, $cambiadoPor, $motivo]);

        $stmt = $conn->prepare('UPDATE proyectos SET estado=? WHERE id=?');
        return $stmt->execute([$estadoNuevo, $id]);
    }

    public static function asignarTutor($id, $tutorId) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare('UPDATE proyectos SET tutor_id=? WHERE id=?');
        return $stmt->execute([$tutorId, $id]);
    }

    public static function archivar($id) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("UPDATE proyectos SET estado='archivado' WHERE id=?");
        return $stmt->execute([$id]);
    }

    public static function obtenerHistorial($proyectoId) {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare(
            "SELECT h.*, CONCAT(u.nombre,' ',u.apellido) AS usuario_nombre
             FROM historial_estado h
             JOIN usuarios u ON h.cambiado_por = u.id
             WHERE h.proyecto_id = ? ORDER BY h.fecha_cambio DESC"
        );
        $stmt->execute([$proyectoId]);
        return $stmt->fetchAll();
    }
}
?>
