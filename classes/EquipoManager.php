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
}