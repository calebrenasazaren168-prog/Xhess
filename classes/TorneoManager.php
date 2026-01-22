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
            
            $stmt = $this->pdo->prepare("DELETE FROM torneo_jugadores WHERE id_torneo = ?");
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
        $stmt = $this->pdo->prepare("
            SELECT e.id_equipo, e.nombre as equipo_nombre,
                COUNT(p.id_partida) as partidas_jugadas,
                SUM(CASE WHEN p.resultado = '1-0' AND em.equipo_blancas = e.id_equipo THEN 1 
                         WHEN p.resultado = '0-1' AND em.equipo_negras = e.id_equipo THEN 1 ELSE 0 END) as victorias,
                SUM(CASE WHEN p.resultado = '1/2' THEN 1 ELSE 0 END) as empates,
                SUM(CASE WHEN p.resultado = '0-1' AND em.equipo_blancas = e.id_equipo THEN 1 
                         WHEN p.resultado = '1-0' AND em.equipo_negras = e.id_equipo THEN 1 ELSE 0 END) as derrotas,
                SUM(CASE WHEN p.resultado = '1-0' AND em.equipo_blancas = e.id_equipo THEN 1 
                         WHEN p.resultado = '0-1' AND em.equipo_negras = e.id_equipo THEN 1 
                         WHEN p.resultado = '1/2' THEN 0.5 ELSE 0 END) as puntos_match,
                COALESCE(SUM(ee.puntos_tableros), 0) as puntos_tableros
            FROM equipos e
            INNER JOIN torneo_equipos te ON e.id_equipo = te.id_equipo
            LEFT JOIN emparejamientos em ON (em.equipo_blancas = e.id_equipo OR em.equipo_negras = e.id_equipo)
            LEFT JOIN partidas p ON em.id_emparejamiento = p.id_emparejamiento
            LEFT JOIN estadisticas_equipo ee ON (e.id_equipo = ee.id_equipo AND ee.id_torneo = ?)
            WHERE te.id_torneo = ?
            GROUP BY e.id_equipo, e.nombre
            ORDER BY puntos_match DESC, puntos_tableros DESC
        ");
        $stmt->execute([$id_torneo, $id_torneo]);
        return $stmt->fetchAll();
    }
    
    public function obtenerClasificacionJugadores($id_torneo) {
        $stmt = $this->pdo->prepare("
            SELECT j.id_jugador,
                CONCAT(j.nombre, ' ', COALESCE(j.apellido, '')) as nombre_jugador,
                e.nombre as equipo_nombre, j.rating,
                COUNT(p.id_partida) as partidas_jugadas,
                SUM(CASE WHEN (p.id_jugador_blancas = j.id_jugador AND p.resultado = '1-0') OR
                             (p.id_jugador_negras = j.id_jugador AND p.resultado = '0-1') THEN 1 ELSE 0 END) as victorias,
                SUM(CASE WHEN p.resultado = '1/2' THEN 1 ELSE 0 END) as empates,
                SUM(CASE WHEN (p.id_jugador_blancas = j.id_jugador AND p.resultado = '0-1') OR
                             (p.id_jugador_negras = j.id_jugador AND p.resultado = '1-0') THEN 1 ELSE 0 END) as derrotas,
                COALESCE(SUM(CASE WHEN (p.id_jugador_blancas = j.id_jugador AND p.resultado = '1-0') OR
                                       (p.id_jugador_negras = j.id_jugador AND p.resultado = '0-1') THEN 1
                                  WHEN p.resultado = '1/2' THEN 0.5 ELSE 0 END), 0) as puntos
            FROM jugadores j
            INNER JOIN torneo_jugadores tj ON j.id_jugador = tj.id_jugador
            LEFT JOIN equipos e ON j.id_equipo = e.id_equipo
            LEFT JOIN partidas p ON (p.id_jugador_blancas = j.id_jugador OR p.id_jugador_negras = j.id_jugador)
            LEFT JOIN emparejamientos em ON p.id_emparejamiento = em.id_emparejamiento
            LEFT JOIN rondas r ON em.id_ronda = r.id_ronda
            WHERE tj.id_torneo = ?
            GROUP BY j.id_jugador, j.nombre, j.apellido, e.nombre, j.rating
            ORDER BY puntos DESC, victorias DESC, j.rating DESC
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
}