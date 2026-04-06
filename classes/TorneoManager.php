<?php
require_once 'Database.php';
require_once 'RatingCalculator.php';

class TorneoManager {
    private $pdo;
    private $ratingCalc;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
        $this->ratingCalc = new RatingCalculator();
    }
    
    public function crear($datos) {
        $stmt = $this->pdo->prepare("
            INSERT INTO torneos (nombre, descripcion, tipo, sistema, nro_tableros, 
                                fecha_inicio, fecha_fin, ubicacion, ritmo_tiempo, id_club, id_cuenta) 
            VALUES (?, ?, 'equipos', ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $datos['nombre'],
            $datos['descripcion'] ?? '',
            $datos['sistema'] ?? 'round_robin',
            $datos['nro_tableros'] ?? 4,
            $datos['fecha_inicio'],
            $datos['fecha_fin'],
            $datos['ubicacion'] ?? '',
            $datos['ritmo_tiempo'] ?? '60+30',
            $datos['id_club'] ?? 5,
            $datos['id_cuenta']
        ]);
    }
    
    public function eliminar($id_torneo) {
        $this->pdo->beginTransaction();
        try {
            // Eliminar en cascada
            $stmt = $this->pdo->prepare("DELETE p FROM partidas p 
                INNER JOIN emparejamientos em ON p.id_emparejamiento = em.id_emparejamiento
                INNER JOIN rondas r ON em.id_ronda = r.id_ronda
                WHERE r.id_torneo = ?");
            $stmt->execute([$id_torneo]);
            
            $stmt = $this->pdo->prepare("DELETE em FROM emparejamientos em 
                INNER JOIN rondas r ON em.id_ronda = r.id_ronda WHERE r.id_torneo = ?");
            $stmt->execute([$id_torneo]);
            
            $stmt = $this->pdo->prepare("DELETE FROM rondas WHERE id_torneo = ?");
            $stmt->execute([$id_torneo]);
            
            $stmt = $this->pdo->prepare("DELETE FROM torneo_equipos WHERE id_torneo = ?");
            $stmt->execute([$id_torneo]);
            
            $stmt = $this->pdo->prepare("DELETE FROM torneos WHERE id_torneo = ?");
            $stmt->execute([$id_torneo]);
            
            $stmt = $this->pdo->prepare("DELETE FROM torneos WHERE id_torneo = ?");
            $stmt->execute([$id_torneo]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function obtenerPublicos() {
        $torneos = $this->pdo->query("
            SELECT t.*, c.nombre as club_nombre
            FROM torneos t
            LEFT JOIN clubes c ON t.id_club = c.id_club
            WHERE t.condicion IN ('pendiente', 'en_progreso', 'finalizado')
            ORDER BY
                CASE WHEN t.condicion = 'en_progreso' THEN 1
                     WHEN t.condicion = 'pendiente' THEN 2
                     WHEN t.condicion = 'finalizado' THEN 3 END,
                t.fecha_inicio DESC
            LIMIT 20
        ")->fetchAll();
        
        foreach ($torneos as &$torneo) {
            $this->actualizarEstado($torneo['id_torneo']);
        }
        
        return $torneos;
    }
    
    public function obtenerMisTorneos($id_cuenta) {
        $stmt = $this->pdo->prepare("
            SELECT t.*, c.nombre as club_nombre 
            FROM torneos t 
            LEFT JOIN clubes c ON t.id_club = c.id_club 
            WHERE t.id_cuenta = ? 
            ORDER BY t.created_at DESC LIMIT 20
        ");
        $stmt->execute([$id_cuenta]);
        return $stmt->fetchAll();
    }
    
    public function obtenerCompleto($id_torneo) {
        $stmt = $this->pdo->prepare("
            SELECT t.*, c.nombre as club_nombre, cu.usuario as creador_usuario
            FROM torneos t
            LEFT JOIN clubes c ON t.id_club = c.id_club
            LEFT JOIN cuentas cu ON t.id_cuenta = cu.id_cuenta
            WHERE t.id_torneo = ?
        ");
        $stmt->execute([$id_torneo]);
        return $stmt->fetch();
    }
    
    public function agregarEquipo($id_torneo, $id_equipo) {
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO torneo_equipos (id_torneo, id_equipo) VALUES (?, ?)");
        return $stmt->execute([$id_torneo, $id_equipo]);
    }
    
    public function agregarEquiposMultiples($id_torneo, $id_equipos) {
        $this->pdo->beginTransaction();
        try {
            foreach ($id_equipos as $id_equipo) {
                $this->agregarEquipo($id_torneo, $id_equipo);
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function retirarEquipo($id_torneo, $id_equipo) {
        if ($this->tieneRondas($id_torneo)) {
            $stmt = $this->pdo->prepare("UPDATE torneo_equipos SET retirado = 1 WHERE id_torneo = ? AND id_equipo = ?");
        } else {
            $stmt = $this->pdo->prepare("DELETE FROM torneo_equipos WHERE id_torneo = ? AND id_equipo = ?");
        }
        return $stmt->execute([$id_torneo, $id_equipo]);
    }
    
    public function obtenerEquipos($id_torneo) {
        $stmt = $this->pdo->prepare("
            SELECT e.* FROM equipos e
            INNER JOIN torneo_equipos te ON e.id_equipo = te.id_equipo
            WHERE te.id_torneo = ?
        ");
        $stmt->execute([$id_torneo]);
        return $stmt->fetchAll();
    }
    
   public function obtenerClasificacionEquipos($id_torneo) {
    $sql = "
        SELECT 
            e.id_equipo,
            e.nombre AS equipo_nombre,
            COUNT(DISTINCT em.id_emparejamiento) AS partidas_jugadas,
            COALESCE(SUM(CASE 
                WHEN sub_punt.puntuacion_blancas IS NOT NULL AND sub_punt.puntuacion_negras IS NOT NULL AND
                     ((em.equipo_blancas = e.id_equipo AND sub_punt.puntuacion_blancas > sub_punt.puntuacion_negras) 
                      OR (em.equipo_negras = e.id_equipo AND sub_punt.puntuacion_negras > sub_punt.puntuacion_blancas)) 
                THEN 1 ELSE 0 
            END), 0) AS victorias,
            COALESCE(SUM(CASE 
                WHEN sub_punt.puntuacion_blancas IS NOT NULL AND sub_punt.puntuacion_negras IS NOT NULL AND
                     sub_punt.puntuacion_blancas = sub_punt.puntuacion_negras 
                THEN 1 ELSE 0 
            END), 0) AS empates,
            COALESCE(SUM(CASE 
                WHEN sub_punt.puntuacion_blancas IS NOT NULL AND sub_punt.puntuacion_negras IS NOT NULL AND
                     ((em.equipo_blancas = e.id_equipo AND sub_punt.puntuacion_blancas < sub_punt.puntuacion_negras) 
                      OR (em.equipo_negras = e.id_equipo AND sub_punt.puntuacion_negras < sub_punt.puntuacion_blancas)) 
                THEN 1 ELSE 0 
            END), 0) AS derrotas,
            COALESCE(SUM(CASE 
                WHEN sub_punt.puntuacion_blancas IS NOT NULL AND sub_punt.puntuacion_negras IS NOT NULL AND
                     ((em.equipo_blancas = e.id_equipo AND sub_punt.puntuacion_blancas > sub_punt.puntuacion_negras) 
                      OR (em.equipo_negras = e.id_equipo AND sub_punt.puntuacion_negras > sub_punt.puntuacion_blancas)) 
                THEN 2
                WHEN sub_punt.puntuacion_blancas = sub_punt.puntuacion_negras 
                THEN 1
                ELSE 0
            END), 0) AS puntos_match,
            COALESCE(SUM(CASE 
                WHEN em.equipo_blancas = e.id_equipo THEN COALESCE(sub_punt.puntuacion_blancas, 0)
                WHEN em.equipo_negras = e.id_equipo THEN COALESCE(sub_punt.puntuacion_negras, 0)
                ELSE 0
            END), 0) AS puntos_tableros
        FROM 
            equipos e
INNER JOIN 
            torneo_equipos te ON e.id_equipo = te.id_equipo
LEFT JOIN 
            (SELECT * FROM emparejamientos WHERE id_ronda IN (SELECT id_ronda FROM rondas WHERE id_torneo = ?)) em ON (em.equipo_blancas = e.id_equipo OR em.equipo_negras = e.id_equipo)
LEFT JOIN (
    SELECT 
        id_emparejamiento,
        SUM(CASE WHEN resultado = '1-0' THEN 1 WHEN resultado = '1/2' THEN 0.5 ELSE 0 END) AS puntuacion_blancas,
        SUM(CASE WHEN resultado = '0-1' THEN 1 WHEN resultado = '1/2' THEN 0.5 ELSE 0 END) AS puntuacion_negras
    FROM partidas 
    GROUP BY id_emparejamiento
) sub_punt ON em.id_emparejamiento = sub_punt.id_emparejamiento
WHERE 
    te.id_torneo = ?
    AND te.retirado = 0
GROUP BY 
    e.id_equipo, e.nombre
ORDER BY 
    puntos_match DESC, puntos_tableros DESC, e.nombre
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$id_torneo, $id_torneo]);
    return $stmt->fetchAll();
}
    
    public function obtenerClasificacionJugadores($id_torneo) {
        $stmt = $this->pdo->prepare("
            SELECT 
    j.id_jugador,  -- Para uso interno si necesitas
    CONCAT(j.nombre, ' ', j.apellido) AS nombre_jugador,
    e.nombre AS equipo_nombre,
    j.rating,
    COUNT(DISTINCT p.id_partida) AS partidas_jugadas,  -- Cuenta únicas para evitar duplicados
    SUM(CASE 
        WHEN (p.id_jugador_blancas = j.id_jugador AND p.resultado = '1-0') 
             OR (p.id_jugador_negras = j.id_jugador AND p.resultado = '0-1') 
        THEN 1 ELSE 0 END) AS victorias,
    SUM(CASE WHEN p.resultado = '1/2' THEN 1 ELSE 0 END) AS empates,
    SUM(CASE 
        WHEN (p.id_jugador_blancas = j.id_jugador AND p.resultado = '0-1') 
             OR (p.id_jugador_negras = j.id_jugador AND p.resultado = '1-0') 
        THEN 1 ELSE 0 END) AS derrotas,
    SUM(CASE 
        WHEN (p.id_jugador_blancas = j.id_jugador AND p.resultado = '1-0') 
             OR (p.id_jugador_negras = j.id_jugador AND p.resultado = '0-1') 
        THEN 1
        WHEN p.resultado = '1/2' THEN 0.5
        ELSE 0
    END) AS puntos
FROM 
    jugadores j
INNER JOIN 
    equipos e ON j.id_equipo = e.id_equipo
INNER JOIN 
    torneo_equipos te ON e.id_equipo = te.id_equipo
LEFT JOIN  -- LEFT para incluir jugadores sin partidas (puntos 0)
    partidas p ON (p.id_jugador_blancas = j.id_jugador OR p.id_jugador_negras = j.id_jugador)
LEFT JOIN 
    emparejamientos em ON p.id_emparejamiento = em.id_emparejamiento
LEFT JOIN 
    rondas r ON em.id_ronda = r.id_ronda
WHERE 
    te.id_torneo = ?  -- Parámetro: id_torneo
    AND (r.id_torneo = te.id_torneo OR r.id_torneo IS NULL)  -- Filtra partidas del torneo; permite jugadores sin partidas
    AND j.activo = 1  -- Solo jugadores activos
    AND te.retirado = 0  -- Equipos no retirados
GROUP BY 
    j.id_jugador, e.nombre, j.rating  -- Agrupa por jugador único
ORDER BY 
    puntos DESC, j.rating DESC;  -- Orden para clasificación
        ");
        $stmt->execute([$id_torneo]);
        return $stmt->fetchAll();
    }
    
    public function actualizarResultado($id_partida, $resultado) {
        $stmt = $this->pdo->prepare("UPDATE partidas SET resultado = ? WHERE id_partida = ?");
        return $stmt->execute([$resultado, $id_partida]);
    }
    
    public function actualizarMultiplesResultados($resultados) {
        $this->pdo->beginTransaction();
        try {
            foreach ($resultados as $id_partida => $resultado) {
                $this->actualizarResultado($id_partida, $resultado);
                
                if ($resultado != '*') {
                    $this->actualizarRatings($id_partida, $resultado);
                }
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    private function actualizarRatings($id_partida, $resultado) {
        $stmt = $this->pdo->prepare("
            SELECT p.id_jugador_blancas, p.id_jugador_negras,
                   jb.rating as rating_blancas, jn.rating as rating_negras
            FROM partidas p
            JOIN jugadores jb ON p.id_jugador_blancas = jb.id_jugador
            JOIN jugadores jn ON p.id_jugador_negras = jn.id_jugador
            WHERE p.id_partida = ?
        ");
        $stmt->execute([$id_partida]);
        $partida = $stmt->fetch();
        
        if ($partida) {
            $this->ratingCalc->actualizar($partida['id_jugador_blancas'], $resultado, $partida['rating_negras']);
            
            $resultado_negras = ($resultado == '1-0') ? '0-1' : (($resultado == '0-1') ? '1-0' : '1/2');
            $this->ratingCalc->actualizar($partida['id_jugador_negras'], $resultado_negras, $partida['rating_blancas']);
        }
    }
    
    public function tieneRondas($id_torneo) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM rondas WHERE id_torneo = ?");
        $stmt->execute([$id_torneo]);
        return $stmt->fetch()['total'] > 0;
    }
    
    public function actualizarEstado($id_torneo) {
        $stmt = $this->pdo->prepare("SELECT * FROM torneos WHERE id_torneo = ?");
        $stmt->execute([$id_torneo]);
        $torneo = $stmt->fetch();
        
        if (!$torneo) return false;
        
        $hoy = date('Y-m-d');
        $nuevoEstado = $torneo['condicion'];
        
        if ($hoy < $torneo['fecha_inicio']) {
            $nuevoEstado = 'pendiente';
        } elseif ($hoy >= $torneo['fecha_inicio'] && $hoy <= $torneo['fecha_fin']) {
            $nuevoEstado = 'en_progreso';
        } elseif ($hoy > $torneo['fecha_fin']) {
            $nuevoEstado = 'finalizado';
        }
        
        if ($nuevoEstado != $torneo['condicion']) {
            $stmt = $this->pdo->prepare("UPDATE torneos SET condicion = ? WHERE id_torneo = ?");
            $stmt->execute([$nuevoEstado, $id_torneo]);
        }
        
        return $nuevoEstado;
    }
    
    public function verificarPropiedad($id_torneo, $id_cuenta, $es_admin = false) {
        if ($es_admin) return true;
        
        $stmt = $this->pdo->prepare("SELECT id_torneo FROM torneos WHERE id_torneo = ? AND id_cuenta = ?");
        $stmt->execute([$id_torneo, $id_cuenta]);
        return $stmt->fetch() !== false;
    }
    
        public function solicitarUnirse(int $id_torneo, int $id_cuenta, ?int $id_equipo, string $mensaje = ''): void {
        // Obtener datos del torneo
        $stmt = $this->pdo->prepare("SELECT tipo, condicion FROM torneos WHERE id_torneo = ?");
        $stmt->execute([$id_torneo]);
        $torneo = $stmt->fetch();
        if (!$torneo) {
            throw new \Exception("Torneo no encontrado.");
        }
        if ($torneo['condicion'] === 'finalizado') {
            throw new \Exception("No puedes solicitar unirte a un torneo ya finalizado.");
        }
 
        if ($torneo['tipo'] === 'equipos') {
            if (!$id_equipo) {
                throw new \Exception("Debes indicar el equipo que quieres inscribir.");
            }
            // Verificar que la cuenta sea creadora del equipo
            $stmt = $this->pdo->prepare("SELECT id_equipo FROM equipos WHERE id_equipo = ? AND id_creador = ?");
            $stmt->execute([$id_equipo, $id_cuenta]);
            if (!$stmt->fetch()) {
                throw new \Exception("Solo el capitán del equipo puede solicitar su inscripción.");
            }
            // Verificar que el equipo no esté ya inscrito
            $stmt = $this->pdo->prepare("SELECT 1 FROM torneo_equipos WHERE id_torneo = ? AND id_equipo = ?");
            $stmt->execute([$id_torneo, $id_equipo]);
            if ($stmt->fetch()) {
                throw new \Exception("Este equipo ya está inscrito en el torneo.");
            }
            // Verificar solicitud duplicada (por cuenta en este torneo)
            $stmt = $this->pdo->prepare("SELECT estado FROM solicitudes_torneo WHERE id_torneo = ? AND id_cuenta = ?");
            $stmt->execute([$id_torneo, $id_cuenta]);
            $existente = $stmt->fetch();
            if ($existente && $existente['estado'] === 'pendiente') {
                throw new \Exception("Ya tienes una solicitud pendiente para este torneo.");
            }
            if ($existente && $existente['estado'] === 'aceptada') {
                throw new \Exception("Tu equipo ya fue aceptado en este torneo.");
            }
        } else {
            // Torneo individual
            $stmt = $this->pdo->prepare("SELECT estado FROM solicitudes_torneo WHERE id_torneo = ? AND id_cuenta = ?");
            $stmt->execute([$id_torneo, $id_cuenta]);
            $existente = $stmt->fetch();
            if ($existente && $existente['estado'] === 'pendiente') {
                throw new \Exception("Ya tienes una solicitud pendiente para este torneo.");
            }
            if ($existente && $existente['estado'] === 'aceptada') {
                throw new \Exception("Ya fuiste aceptado en este torneo.");
            }
            $id_equipo = null;
        }
 
        // Insertar (o reemplazar si fue rechazada anteriormente)
        $stmt = $this->pdo->prepare("
            INSERT INTO solicitudes_torneo (id_torneo, id_cuenta, id_equipo, mensaje)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                estado = 'pendiente',
                id_equipo = VALUES(id_equipo),
                mensaje = VALUES(mensaje),
                fecha_solicitud = NOW(),
                fecha_respuesta = NULL
        ");
        $stmt->execute([$id_torneo, $id_cuenta, $id_equipo, $mensaje]);
    }
 
    /**
     * Acepta una solicitud de torneo.
     * - Si el torneo es de equipos, inscribe automáticamente el equipo en torneo_equipos.
     * - Solo el propietario del torneo (o admin) puede aceptar.
     */
    public function aceptarSolicitudTorneo(int $id_solicitud, int $id_cuenta_org, bool $es_admin = false): void {
        $stmt = $this->pdo->prepare("
            SELECT st.*, t.tipo, t.id_cuenta as id_org
            FROM solicitudes_torneo st
            JOIN torneos t ON st.id_torneo = t.id_torneo
            WHERE st.id_solicitud = ? AND st.estado = 'pendiente'
        ");
        $stmt->execute([$id_solicitud]);
        $sol = $stmt->fetch();
        if (!$sol) {
            throw new \Exception("Solicitud no encontrada o ya procesada.");
        }
        if (!$es_admin && $sol['id_org'] != $id_cuenta_org) {
            throw new \Exception("No tienes permisos para gestionar esta solicitud.");
        }
 
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                UPDATE solicitudes_torneo
                SET estado = 'aceptada', fecha_respuesta = NOW()
                WHERE id_solicitud = ?
            ");
            $stmt->execute([$id_solicitud]);
 
            // Inscribir equipo automáticamente si es torneo de equipos
            if ($sol['tipo'] === 'equipos' && $sol['id_equipo']) {
                $stmt = $this->pdo->prepare("
                    INSERT IGNORE INTO torneo_equipos (id_torneo, id_equipo) VALUES (?, ?)
                ");
                $stmt->execute([$sol['id_torneo'], $sol['id_equipo']]);
            }
 
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
 
    /**
     * Rechaza una solicitud de torneo.
     */
    public function rechazarSolicitudTorneo(int $id_solicitud, int $id_cuenta_org, bool $es_admin = false): void {
        $stmt = $this->pdo->prepare("
            SELECT st.id_solicitud, t.id_cuenta as id_org
            FROM solicitudes_torneo st
            JOIN torneos t ON st.id_torneo = t.id_torneo
            WHERE st.id_solicitud = ? AND st.estado = 'pendiente'
        ");
        $stmt->execute([$id_solicitud]);
        $sol = $stmt->fetch();
        if (!$sol) {
            throw new \Exception("Solicitud no encontrada o ya procesada.");
        }
        if (!$es_admin && $sol['id_org'] != $id_cuenta_org) {
            throw new \Exception("No tienes permisos para gestionar esta solicitud.");
        }
 
        $stmt = $this->pdo->prepare("
            UPDATE solicitudes_torneo
            SET estado = 'rechazada', fecha_respuesta = NOW()
            WHERE id_solicitud = ?
        ");
        $stmt->execute([$id_solicitud]);
    }
 
    /**
     * Cancela una solicitud propia (por el solicitante).
     */
    public function cancelarSolicitudTorneo(int $id_solicitud, int $id_cuenta): void {
        $stmt = $this->pdo->prepare("
            DELETE FROM solicitudes_torneo
            WHERE id_solicitud = ? AND id_cuenta = ? AND estado = 'pendiente'
        ");
        $stmt->execute([$id_solicitud, $id_cuenta]);
    }
 
    /**
     * Devuelve todas las solicitudes de un torneo (para el organizador).
     */
    public function obtenerSolicitudesTorneo(int $id_torneo): array {
        $stmt = $this->pdo->prepare("
            SELECT
                st.*,
                c.usuario,
                c.correo,
                j.nombre  AS jugador_nombre,
                j.apellido AS jugador_apellido,
                j.rating,
                e.nombre  AS equipo_nombre,
                cl.nombre AS club_nombre
            FROM solicitudes_torneo st
            JOIN cuentas c ON st.id_cuenta = c.id_cuenta
            LEFT JOIN jugadores j ON j.id_cuenta = c.id_cuenta
            LEFT JOIN equipos e ON st.id_equipo = e.id_equipo
            LEFT JOIN clubes cl ON e.id_club = cl.id_club
            WHERE st.id_torneo = ?
            ORDER BY st.estado = 'pendiente' DESC, st.fecha_solicitud ASC
        ");
        $stmt->execute([$id_torneo]);
        return $stmt->fetchAll();
    }
 
    /**
     * Devuelve las solicitudes que una cuenta ha enviado a torneos.
     */
    public function obtenerMisSolicitudesTorneos(int $id_cuenta): array {
        $stmt = $this->pdo->prepare("
            SELECT st.*, t.nombre AS torneo_nombre, t.tipo, t.condicion,
                   e.nombre AS equipo_nombre
            FROM solicitudes_torneo st
            JOIN torneos t ON st.id_torneo = t.id_torneo
            LEFT JOIN equipos e ON st.id_equipo = e.id_equipo
            WHERE st.id_cuenta = ?
            ORDER BY st.fecha_solicitud DESC
        ");
        $stmt->execute([$id_cuenta]);
        return $stmt->fetchAll();
    }
 
    /**
     * Cuenta solicitudes pendientes de todos los torneos que posee una cuenta.
     */
    public function contarSolicitudesPendientesTorneos(int $id_cuenta): int {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) AS total
            FROM solicitudes_torneo st
            JOIN torneos t ON st.id_torneo = t.id_torneo
            WHERE t.id_cuenta = ? AND st.estado = 'pendiente'
        ");
        $stmt->execute([$id_cuenta]);
        return (int)$stmt->fetch()['total'];
    }
}