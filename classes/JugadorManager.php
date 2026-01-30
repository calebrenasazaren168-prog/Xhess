<?php
require_once 'Database.php';

class JugadorManager {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function unirseAEquipo($id_jugador, $id_equipo) {
        $this->pdo->beginTransaction();
        try {
            // Validar jugador
            $jugador = $this->obtenerJugador($id_jugador);
            if (!$jugador || !$jugador['id_cuenta']) {
                throw new Exception("Jugador no válido o sin cuenta registrada");
            }
            
            // Validar equipo existe
            if (!$this->equipoExiste($id_equipo)) {
                throw new Exception("El equipo no existe");
            }
            
            // Verificar si ya está en el equipo
            if ($jugador['id_equipo'] == $id_equipo) {
                throw new Exception("Ya estás en este equipo");
            }
            
            // Verificar torneos activos avanzados
            $this->validarTorneosActivos($id_equipo);
            
            // Remover de equipo anterior si existe
            if ($jugador['id_equipo']) {
                $this->removerDeTorneosActivos($id_jugador, $jugador['id_equipo']);
            }
            
            // Actualizar equipo
            $stmt = $this->pdo->prepare("UPDATE jugadores SET id_equipo = ? WHERE id_jugador = ?");
            $stmt->execute([$id_equipo, $id_jugador]);
            
            // Inscribir en torneos del nuevo equipo
            $this->inscribirEnTorneosEquipo($id_jugador, $id_equipo);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function salirseDeEquipo($id_jugador) {
        $this->pdo->beginTransaction();
        try {
            $jugador = $this->obtenerJugador($id_jugador);
            
            if (!$jugador || !$jugador['id_cuenta']) {
                throw new Exception("Jugador no válido");
            }
            
            if (!$jugador['id_equipo']) {
                throw new Exception("No estás en ningún equipo");
            }
            
            $this->removerDeTorneosActivos($id_jugador, $jugador['id_equipo']);
            
            $stmt = $this->pdo->prepare("UPDATE jugadores SET id_equipo = NULL WHERE id_jugador = ?");
            $stmt->execute([$id_jugador]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function obtenerJugador($id_jugador) {
        $stmt = $this->pdo->prepare("
            SELECT j.*, 
                   e.nombre as equipo_nombre,
                   e.id_equipo as id_equipo,
                   c.nombre as club_nombre
            FROM jugadores j 
            LEFT JOIN equipos e ON j.id_equipo = e.id_equipo
            LEFT JOIN clubes c ON e.id_club = c.id_club
            WHERE j.id_jugador = ?
        ");
        $stmt->execute([$id_jugador]);
        return $stmt->fetch();
    }
    
    public function obtenerPublicos($limite = 12) {
        return $this->pdo->query("
            SELECT j.*, e.nombre as equipo_nombre
            FROM jugadores j 
            LEFT JOIN equipos e ON j.id_equipo = e.id_equipo
            WHERE j.activo = 1
            ORDER BY j.rating DESC
            LIMIT {$limite}
        ")->fetchAll();
    }
    
    public function obtenerRegistrados() {
        return $this->pdo->query("
            SELECT j.*, c.usuario, c.correo, e.nombre as equipo_actual, e.id_equipo as id_equipo_actual
            FROM jugadores j 
            JOIN cuentas c ON j.id_cuenta = c.id_cuenta 
            LEFT JOIN equipos e ON j.id_equipo = e.id_equipo
            WHERE j.activo = 1
            ORDER BY j.nombre, j.apellido
        ")->fetchAll();
    }
    
    private function equipoExiste($id_equipo) {
        $stmt = $this->pdo->prepare("SELECT id_equipo FROM equipos WHERE id_equipo = ?");
        $stmt->execute([$id_equipo]);
        return $stmt->fetch() !== false;
    }
    
    private function validarTorneosActivos($id_equipo) {
        $stmt = $this->pdo->prepare("
            SELECT t.id_torneo, t.nombre, MAX(r.numero_ronda) as ronda_maxima
            FROM torneos t
            JOIN torneo_equipos te ON t.id_torneo = te.id_torneo
            LEFT JOIN rondas r ON t.id_torneo = r.id_torneo
            WHERE te.id_equipo = ? AND t.condicion = 'en_progreso'
            GROUP BY t.id_torneo, t.nombre
            HAVING ronda_maxima >= 2
        ");
        $stmt->execute([$id_equipo]);
        $torneos = $stmt->fetchAll();
        
        if (!empty($torneos)) {
            $nombres = array_column($torneos, 'nombre');
            throw new Exception("No puedes unirte a este equipo. Está en torneos después de ronda 2: " . implode(', ', $nombres));
        }
    }
    
    private function removerDeTorneosActivos($id_jugador, $id_equipo) {
        $stmt = $this->pdo->prepare("
            SELECT t.id_torneo 
            FROM torneos t
            JOIN torneo_equipos te ON t.id_torneo = te.id_torneo
            WHERE te.id_equipo = ? AND t.condicion = 'en_progreso' AND te.retirado = 0
        ");
        $stmt->execute([$id_equipo]);
        $torneos = $stmt->fetchAll();
        
        foreach ($torneos as $torneo) {
            $stmt = $this->pdo->prepare("DELETE FROM torneo_jugadores WHERE id_torneo = ? AND id_jugador = ?");
            $stmt->execute([$torneo['id_torneo'], $id_jugador]);
            
            $this->marcarPartidasComoPerdidas($id_jugador, $torneo['id_torneo']);
        }
    }
    
    private function inscribirEnTorneosEquipo($id_jugador, $id_equipo) {
        $stmt = $this->pdo->prepare("
            SELECT t.id_torneo, t.nro_tableros, COUNT(DISTINCT r.id_ronda) as rondas_generadas
            FROM torneos t
            JOIN torneo_equipos te ON t.id_torneo = te.id_torneo
            LEFT JOIN rondas r ON t.id_torneo = r.id_torneo
            WHERE te.id_equipo = ? AND t.condicion = 'en_progreso' AND te.retirado = 0
            GROUP BY t.id_torneo, t.nro_tableros
            HAVING rondas_generadas <= 1
        ");
        $stmt->execute([$id_equipo]);
        $torneos = $stmt->fetchAll();
        
        foreach ($torneos as $torneo) {
            $stmt_check = $this->pdo->prepare("SELECT * FROM torneo_jugadores WHERE id_torneo = ? AND id_jugador = ?");
            $stmt_check->execute([$torneo['id_torneo'], $id_jugador]);
            
            if (!$stmt_check->fetch()) {
                $stmt = $this->pdo->prepare("INSERT INTO torneo_jugadores (id_torneo, id_jugador, id_equipo) VALUES (?, ?, ?)");
                $stmt->execute([$torneo['id_torneo'], $id_jugador, $id_equipo]);
            }
        }
    }
    
    private function marcarPartidasComoPerdidas($id_jugador, $id_torneo) {
        $stmt = $this->pdo->prepare("
            UPDATE partidas p
            JOIN emparejamientos em ON p.id_emparejamiento = em.id_emparejamiento
            JOIN rondas r ON em.id_ronda = r.id_ronda
            SET p.resultado = CASE 
                WHEN p.id_jugador_blancas = ? THEN '0-1'
                WHEN p.id_jugador_negras = ? THEN '1-0'
                ELSE p.resultado
            END
            WHERE r.id_torneo = ? AND p.resultado = '*'
            AND (p.id_jugador_blancas = ? OR p.id_jugador_negras = ?)
        ");
        $stmt->execute([$id_jugador, $id_jugador, $id_torneo, $id_jugador, $id_jugador]);
    }
}