<?php
require_once __DIR__ . '/../config/conexion.php';

class AsistenciaModelo {

    public static function obtenerTodos() {

        $conn = Conexion::conectar();

        $sql = "SELECT
                    a.*,
                    CONCAT(u.nombre,' ',u.apellido) AS estudiante_nombre,
                    p.titulo AS proyecto
                FROM asistencias a
                INNER JOIN usuarios u
                    ON a.estudiante_id = u.id
                INNER JOIN proyectos p
                    ON a.proyecto_id = p.id
                ORDER BY a.fecha DESC";

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public static function obtenerPorId($id) {

        $conn = Conexion::conectar();

        $stmt = $conn->prepare(
            "SELECT * FROM asistencias
             WHERE id = ?"
        );

        $stmt->execute([$id]);

        return $stmt->fetch();
    }

    public static function crear($datos) {

        $conn = Conexion::conectar();

        $stmt = $conn->prepare(
            "INSERT INTO asistencias
            (
                proyecto_id,
                estudiante_id,
                tutor_id,
                fecha,
                estado,
                modalidad,
                observaciones
            )
            VALUES
            (
                :proyecto_id,
                :estudiante_id,
                :tutor_id,
                :fecha,
                :estado,
                :modalidad,
                :observaciones
            )"
        );

        $stmt->execute([

            ':proyecto_id'   => $datos['proyecto_id'],
            ':estudiante_id' => $datos['estudiante_id'],
            ':tutor_id'      => $datos['tutor_id'],
            ':fecha'         => $datos['fecha'],
            ':estado'        => strtolower($datos['estado']),
            ':modalidad'     => strtolower($datos['modalidad']),
            ':observaciones' => $datos['observaciones'] ?? null

        ]);

        return $conn->lastInsertId();
    }

    public static function editar($id,$datos) {

        $conn = Conexion::conectar();

        $stmt = $conn->prepare(

            "UPDATE asistencias SET

                proyecto_id = :proyecto_id,
                estudiante_id = :estudiante_id,
                tutor_id = :tutor_id,
                fecha = :fecha,
                estado = :estado,
                modalidad = :modalidad,
                observaciones = :observaciones

             WHERE id = :id"

        );

        return $stmt->execute([

            ':proyecto_id'   => $datos['proyecto_id'],
            ':estudiante_id' => $datos['estudiante_id'],
            ':tutor_id'      => $datos['tutor_id'],
            ':fecha'         => $datos['fecha'],
            ':estado'        => strtolower($datos['estado']),
            ':modalidad'     => strtolower($datos['modalidad']),
            ':observaciones' => $datos['observaciones'] ?? null,
            ':id'            => $id

        ]);

    }

    public static function eliminar($id) {

        $conn = Conexion::conectar();

        $stmt = $conn->prepare(

            "DELETE FROM asistencias
             WHERE id = ?"

        );

        return $stmt->execute([$id]);

    }

    public static function obtenerProyecto($proyectoId) {

        $conn = Conexion::conectar();

        $stmt = $conn->prepare(

            "SELECT
                estudiante_id,
                tutor_id
             FROM proyectos
             WHERE id=?"

        );

        $stmt->execute([$proyectoId]);

        return $stmt->fetch();

    }

}
?>