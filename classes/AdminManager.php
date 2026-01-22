<?php
require_once 'Database.php';

class AdminManager {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function obtenerTodasCuentas() {
        return $this->pdo->query("
            SELECT c.*, r.nombre as rol_nombre 
            FROM cuentas c 
            LEFT JOIN roles r ON c.id_rol = r.id_rol 
            ORDER BY c.fecha_creacion DESC
            LIMIT 50
        ")->fetchAll();
    }
    
    public function eliminarCuenta($id_cuenta) {
        if ($id_cuenta == 1) {
            throw new Exception("No se puede eliminar la cuenta de administrador principal");
        }
        
        $this->pdo->beginTransaction();
        try {
            // Encontrar jugador asociado
            $stmt = $this->pdo->prepare("SELECT id_jugador FROM jugadores WHERE id_cuenta = ?");
            $stmt->execute([$id_cuenta]);
            $jugador = $stmt->fetch();
            
            if ($jugador) {
                $id_jugador = $jugador['id_jugador'];
                
                // Eliminar de torneo_jugadores
                $stmt = $this->pdo->prepare("DELETE FROM torneo_jugadores WHERE id_jugador = ?");
                $stmt->execute([$id_jugador]);
                
                // Actualizar partidas (desvincular jugador)
                $stmt = $this->pdo->prepare("UPDATE partidas SET id_jugador_blancas = NULL WHERE id_jugador_blancas = ?");
                $stmt->execute([$id_jugador]);
                
                $stmt = $this->pdo->prepare("UPDATE partidas SET id_jugador_negras = NULL WHERE id_jugador_negras = ?");
                $stmt->execute([$id_jugador]);
                
                // Eliminar jugador
                $stmt = $this->pdo->prepare("DELETE FROM jugadores WHERE id_jugador = ?");
                $stmt->execute([$id_jugador]);
            }
            
            // Eliminar cuenta
            $stmt = $this->pdo->prepare("DELETE FROM cuentas WHERE id_cuenta = ?");
            $stmt->execute([$id_cuenta]);
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al eliminar cuenta: " . $e->getMessage());
        }
    }
    
    public function obtenerTodosTorneos() {
        return $this->pdo->query("
            SELECT t.*, c.usuario as creador 
            FROM torneos t 
            LEFT JOIN cuentas c ON t.id_cuenta = c.id_cuenta 
            ORDER BY t.created_at DESC
            LIMIT 50
        ")->fetchAll();
    }
    
    public function asignarJugadorAEquipo($id_jugador, $id_equipo) {
        $stmt = $this->pdo->prepare("UPDATE jugadores SET id_equipo = ? WHERE id_jugador = ?");
        return $stmt->execute([$id_equipo, $id_jugador]);
    }
    
    public function quitarJugadorDeEquipo($id_jugador) {
        $stmt = $this->pdo->prepare("UPDATE jugadores SET id_equipo = NULL WHERE id_jugador = ?");
        return $stmt->execute([$id_jugador]);
    }
    
    public function obtenerEstadisticasGenerales() {
        $stats = [];
        
        // Total de usuarios
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM cuentas WHERE activo = 1");
        $stats['usuarios'] = $stmt->fetch()['total'];
        
        // Total de jugadores
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM jugadores WHERE activo = 1");
        $stats['jugadores'] = $stmt->fetch()['total'];
        
        // Total de equipos
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM equipos");
        $stats['equipos'] = $stmt->fetch()['total'];
        
        // Total de torneos
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM torneos");
        $stats['torneos'] = $stmt->fetch()['total'];
        
        // Torneos activos
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM torneos WHERE condicion = 'en_progreso'");
        $stats['torneos_activos'] = $stmt->fetch()['total'];
        
        // Total de partidas
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM partidas");
        $stats['partidas'] = $stmt->fetch()['total'];
        
        return $stats;
    }
    
    public function obtenerActividadReciente($limite = 20) {
        return $this->pdo->query("
            SELECT 'torneo' as tipo, nombre, created_at as fecha 
            FROM torneos 
            ORDER BY created_at DESC 
            LIMIT {$limite}
        ")->fetchAll();
    }
}