<?php
require_once 'Database.php';

class RatingCalculator {
    private $pdo;
    private $K = 32;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function actualizar($id_jugador, $resultado, $rating_oponente) {
        // Obtener rating actual
        $stmt = $this->pdo->prepare("SELECT rating FROM jugadores WHERE id_jugador = ?");
        $stmt->execute([$id_jugador]);
        $jugador = $stmt->fetch();
        $rating_actual = $jugador['rating'];
        
        // Calcular expectativa
        $expectativa = 1 / (1 + pow(10, ($rating_oponente - $rating_actual) / 400));
        
        // Convertir resultado a puntos
        $puntos = $this->resultadoAPuntos($resultado);
        
        // Calcular nuevo rating
        $nuevo_rating = $rating_actual + $this->K * ($puntos - $expectativa);
        
        // Actualizar en BD
        $stmt = $this->pdo->prepare("UPDATE jugadores SET rating = ROUND(?) WHERE id_jugador = ?");
        return $stmt->execute([$nuevo_rating, $id_jugador]);
    }
    
    private function resultadoAPuntos($resultado) {
        switch ($resultado) {
            case '1-0': return 1;
            case '1/2': return 0.5;
            case '0-1': return 0;
            default: return 0;
        }
    }
    
    public function setFactorK($k) {
        $this->K = $k;
    }
}