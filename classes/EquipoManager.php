<?php
require_once 'Database.php';

class EquipoManager {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function crear($datos) {
        if ($this->existeEnClub($datos['nombre'], $datos['id_club'])) {
            throw new Exception("Ya existe un equipo con ese nombre en este club");
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO equipos (nombre, id_club, id_coach, id_creador) 
            VALUES (?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $datos['nombre'],
            $datos['id_club'],
            $datos['id_creador'],
            $datos['id_creador']
        ]);
    }
    
    public function eliminar($id_equipo) {
        // Verificar jugadores
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM jugadores WHERE id_equipo = ?");
        $stmt->execute([$id_equipo]);
        if ($stmt->fetch()['total'] > 0) {
            throw new Exception("No se puede eliminar el equipo porque tiene jugadores asignados");
        }
        
        // Verificar torneos
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM torneo_equipos WHERE id_equipo = ?");
        $stmt->execute([$id_equipo]);
        if ($stmt->fetch()['total'] > 0) {
            throw new Exception("No se puede eliminar el equipo porque está participando en torneos");
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM equipos WHERE id_equipo = ?");
        return $stmt->execute([$id_equipo]);
    }
    
    public function obtenerTodos() {
        return $this->pdo->query("
            SELECT e.*, c.nombre as club_nombre,
                   (SELECT COUNT(*) FROM jugadores j WHERE j.id_equipo = e.id_equipo) as num_jugadores
            FROM equipos e 
            JOIN clubes c ON e.id_club = c.id_club 
            ORDER BY c.nombre, e.nombre
        ")->fetchAll();
    }
    
    public function obtenerPorClub($id_club) {
        $stmt = $this->pdo->prepare("
            SELECT e.*, c.nombre as club_nombre,
                   (SELECT COUNT(*) FROM jugadores j WHERE j.id_equipo = e.id_equipo) as num_jugadores
            FROM equipos e 
            JOIN clubes c ON e.id_club = c.id_club 
            WHERE e.id_club = ? 
            ORDER BY e.nombre
        ");
        $stmt->execute([$id_club]);
        return $stmt->fetchAll();
    }
    
    public function obtenerJugadores($id_equipo) {
        $stmt = $this->pdo->prepare("
            SELECT j.*, c.usuario, c.correo 
            FROM jugadores j 
            JOIN cuentas c ON j.id_cuenta = c.id_cuenta 
            WHERE j.id_equipo = ? AND j.activo = 1
            ORDER BY j.rating DESC
        ");
        $stmt->execute([$id_equipo]);
        return $stmt->fetchAll();
    }
    
    public function existeEnClub($nombre, $id_club, $excluir_id = null) {
        $sql = "SELECT id_equipo FROM equipos WHERE nombre = ? AND id_club = ?";
        $params = [$nombre, $id_club];
        
        if ($excluir_id) {
            $sql .= " AND id_equipo != ?";
            $params[] = $excluir_id;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    // ─── SOLICITUDES ────────────────────────────────────────────────────────────

    /**
     * Envía una solicitud de ingreso al equipo.
     * Lanza excepción si ya existe una solicitud pendiente o si ya es miembro.
     */
    public function solicitarIngreso($id_jugador, $id_equipo, $mensaje = '') {
        // ¿Ya es miembro?
        $stmt = $this->pdo->prepare("SELECT id_equipo FROM jugadores WHERE id_jugador = ?");
        $stmt->execute([$id_jugador]);
        $jug = $stmt->fetch();
        if ($jug && $jug['id_equipo'] == $id_equipo) {
            throw new Exception("Ya eres miembro de este equipo.");
        }

        // ¿Ya tiene solicitud pendiente para este equipo?
        $stmt = $this->pdo->prepare("
            SELECT id_solicitud FROM solicitudes_equipo 
            WHERE id_jugador = ? AND id_equipo = ? AND estado = 'pendiente'
        ");
        $stmt->execute([$id_jugador, $id_equipo]);
        if ($stmt->fetch()) {
            throw new Exception("Ya tienes una solicitud pendiente para este equipo.");
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO solicitudes_equipo (id_jugador, id_equipo, mensaje) 
            VALUES (?, ?, ?)
        ");
        return $stmt->execute([$id_jugador, $id_equipo, $mensaje]);
    }

    /**
     * Retorna todas las solicitudes pendientes de un equipo (para el capitán).
     */
    public function obtenerSolicitudesPendientes($id_equipo) {
        $stmt = $this->pdo->prepare("
            SELECT s.*, 
                   j.nombre as jugador_nombre, j.apellido as jugador_apellido, j.rating,
                   c.usuario
            FROM solicitudes_equipo s
            JOIN jugadores j ON s.id_jugador = j.id_jugador
            JOIN cuentas c ON j.id_cuenta = c.id_cuenta
            WHERE s.id_equipo = ? AND s.estado = 'pendiente'
            ORDER BY s.fecha_solicitud ASC
        ");
        $stmt->execute([$id_equipo]);
        return $stmt->fetchAll();
    }

    /**
     * Retorna el historial de solicitudes (todas) de un equipo.
     */
    public function obtenerTodasSolicitudes($id_equipo) {
        $stmt = $this->pdo->prepare("
            SELECT s.*, 
                   j.nombre as jugador_nombre, j.apellido as jugador_apellido, j.rating,
                   c.usuario
            FROM solicitudes_equipo s
            JOIN jugadores j ON s.id_jugador = j.id_jugador
            JOIN cuentas c ON j.id_cuenta = c.id_cuenta
            WHERE s.id_equipo = ?
            ORDER BY s.fecha_solicitud DESC
        ");
        $stmt->execute([$id_equipo]);
        return $stmt->fetchAll();
    }

    /**
     * Retorna las solicitudes que un jugador ha enviado.
     */
    public function obtenerSolicitudesJugador($id_jugador) {
        $stmt = $this->pdo->prepare("
            SELECT s.*, e.nombre as equipo_nombre, c2.nombre as club_nombre
            FROM solicitudes_equipo s
            JOIN equipos e ON s.id_equipo = e.id_equipo
            JOIN clubes c2 ON e.id_club = c2.id_club
            WHERE s.id_jugador = ?
            ORDER BY s.fecha_solicitud DESC
        ");
        $stmt->execute([$id_jugador]);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta solicitudes pendientes para los equipos que creó una cuenta.
     */
    public function contarSolicitudesPendientesCapitan($id_cuenta) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total
            FROM solicitudes_equipo s
            JOIN equipos e ON s.id_equipo = e.id_equipo
            WHERE e.id_creador = ? AND s.estado = 'pendiente'
        ");
        $stmt->execute([$id_cuenta]);
        return (int)$stmt->fetch()['total'];
    }

    /**
     * Acepta una solicitud: asigna el jugador al equipo y marca como aceptada.
     * Verifica que quien acepta sea el creador del equipo.
     */
    public function aceptarSolicitud($id_solicitud, $id_cuenta_capitan) {
        // Verificar que la solicitud existe y pertenece a un equipo del capitán
        $stmt = $this->pdo->prepare("
            SELECT s.*, e.id_creador, e.id_equipo
            FROM solicitudes_equipo s
            JOIN equipos e ON s.id_equipo = e.id_equipo
            WHERE s.id_solicitud = ? AND s.estado = 'pendiente'
        ");
        $stmt->execute([$id_solicitud]);
        $sol = $stmt->fetch();

        if (!$sol) {
            throw new Exception("Solicitud no encontrada o ya procesada.");
        }
        if ($sol['id_creador'] != $id_cuenta_capitan) {
            throw new Exception("No tienes permisos para gestionar esta solicitud.");
        }

        $this->pdo->beginTransaction();
        try {
            // Marcar solicitud como aceptada
            $stmt = $this->pdo->prepare("
                UPDATE solicitudes_equipo 
                SET estado = 'aceptada', fecha_respuesta = NOW() 
                WHERE id_solicitud = ?
            ");
            $stmt->execute([$id_solicitud]);

            // Asignar jugador al equipo
            $stmt = $this->pdo->prepare("
                UPDATE jugadores SET id_equipo = ? WHERE id_jugador = ?
            ");
            $stmt->execute([$sol['id_equipo'], $sol['id_jugador']]);

            // Rechazar otras solicitudes pendientes del mismo jugador
            $stmt = $this->pdo->prepare("
                UPDATE solicitudes_equipo 
                SET estado = 'rechazada', fecha_respuesta = NOW()
                WHERE id_jugador = ? AND id_solicitud != ? AND estado = 'pendiente'
            ");
            $stmt->execute([$sol['id_jugador'], $id_solicitud]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Rechaza una solicitud. Verifica que quien rechaza sea el creador del equipo.
     */
    public function rechazarSolicitud($id_solicitud, $id_cuenta_capitan) {
        $stmt = $this->pdo->prepare("
            SELECT s.*, e.id_creador
            FROM solicitudes_equipo s
            JOIN equipos e ON s.id_equipo = e.id_equipo
            WHERE s.id_solicitud = ? AND s.estado = 'pendiente'
        ");
        $stmt->execute([$id_solicitud]);
        $sol = $stmt->fetch();

        if (!$sol) {
            throw new Exception("Solicitud no encontrada o ya procesada.");
        }
        if ($sol['id_creador'] != $id_cuenta_capitan) {
            throw new Exception("No tienes permisos para gestionar esta solicitud.");
        }

        $stmt = $this->pdo->prepare("
            UPDATE solicitudes_equipo 
            SET estado = 'rechazada', fecha_respuesta = NOW() 
            WHERE id_solicitud = ?
        ");
        return $stmt->execute([$id_solicitud]);
    }

    /**
     * Cancela una solicitud pendiente (por el propio jugador).
     */
    public function cancelarSolicitud($id_solicitud, $id_jugador) {
        $stmt = $this->pdo->prepare("
            DELETE FROM solicitudes_equipo 
            WHERE id_solicitud = ? AND id_jugador = ? AND estado = 'pendiente'
        ");
        return $stmt->execute([$id_solicitud, $id_jugador]);
    }

    /**
     * Devuelve los equipos que administra (creó) una cuenta, con sus solicitudes pendientes.
     */
    public function obtenerEquiposCapitan($id_cuenta) {
        $stmt = $this->pdo->prepare("
            SELECT e.*, c.nombre as club_nombre,
                   (SELECT COUNT(*) FROM jugadores j WHERE j.id_equipo = e.id_equipo) as num_jugadores,
                   (SELECT COUNT(*) FROM solicitudes_equipo s WHERE s.id_equipo = e.id_equipo AND s.estado = 'pendiente') as solicitudes_pendientes
            FROM equipos e
            JOIN clubes c ON e.id_club = c.id_club
            WHERE e.id_creador = ?
            ORDER BY e.nombre
        ");
        $stmt->execute([$id_cuenta]);
        return $stmt->fetchAll();
    }
}