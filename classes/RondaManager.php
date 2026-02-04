<?php
require_once 'Database.php';

class RondaManager {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function generar($id_torneo, $numero_ronda, $fecha_ronda) {
        $this->pdo->beginTransaction();
        try {
            // Obtener equipos del torneo
            $stmt = $this->pdo->prepare("
                SELECT e.* FROM equipos e
                INNER JOIN torneo_equipos te ON e.id_equipo = te.id_equipo
                WHERE te.id_torneo = ?
            ");
            $stmt->execute([$id_torneo]);
            $equipos = $stmt->fetchAll();
            $num_equipos = count($equipos);
            
            // Obtener sistema del torneo para validaciones
            $stmt = $this->pdo->prepare("SELECT sistema, nro_tableros FROM torneos WHERE id_torneo = ?");
            $stmt->execute([$id_torneo]);
            $torneo = $stmt->fetch();
            $sistema = $torneo['sistema'];
            $nro_tableros = $torneo['nro_tableros'] ?? 4;
            
            // Validaciones
            if ($num_equipos < 2) {
                throw new Exception("Se necesitan al menos 2 equipos para generar rondas");
            }
            
            // Calcular máximo de rondas según el sistema
            if ($sistema == 'suizo') {
                // Sistema Suizo: típicamente entre 5 y log2(n) * 2 rondas
                $max_rondas = min(ceil(log($num_equipos, 2) * 1.5), 10);
                if ($num_equipos <= 4) {
                    $max_rondas = $num_equipos - 1;
                }
            } else {
                // Round Robin: todos contra todos
                $max_rondas = $num_equipos - 1;
                if ($num_equipos % 2 != 0) {
                    $max_rondas = $num_equipos;
                }
            }
            
            if ($numero_ronda > $max_rondas) {
                throw new Exception("No se pueden generar más de $max_rondas rondas para $num_equipos equipos en sistema $sistema");
            }
            
            // Verificar si ya existe
            $stmt = $this->pdo->prepare("SELECT id_ronda FROM rondas WHERE id_torneo = ? AND numero_ronda = ?");
            $stmt->execute([$id_torneo, $numero_ronda]);
            if ($stmt->fetch()) {
                throw new Exception("La ronda $numero_ronda ya existe para este torneo");
            }
            
            // Crear ronda
            $stmt = $this->pdo->prepare("
                INSERT INTO rondas (id_torneo, numero_ronda, fecha_ronda, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt->execute([$id_torneo, $numero_ronda, $fecha_ronda]);
            $id_ronda = $this->pdo->lastInsertId();
            
            // Reiniciar estadísticas si es ronda 1
            if ($numero_ronda == 1) {
                $this->reiniciarEstadisticas($id_torneo);
            }
            
            // Generar emparejamientos según sistema
            if ($sistema == 'suizo') {
                // Sistema Suizo - emparejamientos por puntuación
                $emparejamientos = $this->generarEmparejamientosSuizo($equipos, $id_torneo, $numero_ronda);
                foreach ($emparejamientos as $emp) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO emparejamientos (id_ronda, equipo_blancas, equipo_negras, created_at) 
                        VALUES (?, ?, ?, NOW())
                    ");
                    $stmt->execute([$id_ronda, $emp['blancas'], $emp['negras']]);
                    $id_emp = $this->pdo->lastInsertId();
                    
                    $this->generarPartidasNormales($id_emp, $emp['blancas'], $emp['negras'], $nro_tableros);
                }
            } elseif ($sistema == 'round_robin_ponderado') {
                // Round Robin Ponderado
                $emparejamientos = $this->generarEmparejamientosRoundRobin($equipos, $numero_ronda);
                foreach ($emparejamientos as $emp) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO emparejamientos (id_ronda, equipo_blancas, equipo_negras, created_at) 
                        VALUES (?, ?, ?, NOW())
                    ");
                    $stmt->execute([$id_ronda, $emp['blancas'], $emp['negras']]);
                    $id_emp = $this->pdo->lastInsertId();
                    
                    $this->generarPartidasPonderadas($id_emp, $emp['blancas'], $emp['negras'], $id_torneo, $numero_ronda, $nro_tableros);
                }
            } else {
                // Round Robin Clásico
                $emparejamientos = $this->generarEmparejamientosRoundRobin($equipos, $numero_ronda);
                foreach ($emparejamientos as $emp) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO emparejamientos (id_ronda, equipo_blancas, equipo_negras, created_at) 
                        VALUES (?, ?, ?, NOW())
                    ");
                    $stmt->execute([$id_ronda, $emp['blancas'], $emp['negras']]);
                    $id_emp = $this->pdo->lastInsertId();
                    
                    $this->generarPartidasNormales($id_emp, $emp['blancas'], $emp['negras'], $nro_tableros);
                }
            }
            
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Error generando ronda: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Genera emparejamientos según el Sistema Suizo
     * Principios:
     * - Ronda 1: Empareja top vs bottom de rating
     * - Rondas siguientes: Empareja equipos con puntuación similar
     * - Evita repeticiones de enfrentamientos
     * - Alterna colores (blancas/negras)
     */
    private function generarEmparejamientosSuizo($equipos, $id_torneo, $numero_ronda) {
        $emparejamientos = [];
        
        if ($numero_ronda == 1) {
            // Primera ronda: emparejar top vs bottom por rating
            return $this->generarPrimeraRondaSuizo($equipos);
        }
        
        // Obtener puntuaciones actuales de los equipos
        $equipos_con_puntos = $this->obtenerEquiposConPuntuacion($equipos, $id_torneo);
        
        // Obtener historial de enfrentamientos
        $historial = $this->obtenerHistorialEnfrentamientos($id_torneo);
        
        // Obtener balance de colores
        $balances_colores = $this->obtenerBalanceColores($equipos, $id_torneo);
        
        // Ordenar por puntuación y desempate
        usort($equipos_con_puntos, function($a, $b) {
            if ($a['puntos'] != $b['puntos']) {
                return $b['puntos'] <=> $a['puntos']; // Mayor puntos primero
            }
            return $b['rating_promedio'] <=> $a['rating_promedio']; // Desempate por rating
        });
        
        // Añadir BYE si es número impar
        $num_equipos = count($equipos_con_puntos);
        $tiene_bye = false;
        if ($num_equipos % 2 != 0) {
            $tiene_bye = true;
            // El equipo con menor puntuación recibe BYE (y no lo ha tenido antes)
            for ($i = count($equipos_con_puntos) - 1; $i >= 0; $i--) {
                if (!$this->haRecibidoBye($equipos_con_puntos[$i]['id_equipo'], $historial)) {
                    $equipo_bye = array_splice($equipos_con_puntos, $i, 1)[0];
                    break;
                }
            }
            $num_equipos = count($equipos_con_puntos);
        }
        
        // Generar emparejamientos
        $ya_emparejados = [];
        $grupos_puntuacion = $this->agruparPorPuntuacion($equipos_con_puntos);
        
        foreach ($grupos_puntuacion as $grupo) {
            $equipos_grupo = $grupo['equipos'];
            
            while (count($equipos_grupo) >= 2) {
                $equipo1 = array_shift($equipos_grupo);
                $emparejado = false;
                
                // Buscar el mejor oponente
                for ($i = 0; $i < count($equipos_grupo); $i++) {
                    $equipo2 = $equipos_grupo[$i];
                    
                    // Verificar que no se hayan enfrentado antes
                    if ($this->yaSeEnfrentaron($equipo1['id_equipo'], $equipo2['id_equipo'], $historial)) {
                        continue;
                    }
                    
                    // Determinar colores basado en el balance
                    $balance1 = $balances_colores[$equipo1['id_equipo']] ?? 0;
                    $balance2 = $balances_colores[$equipo2['id_equipo']] ?? 0;
                    
                    if ($balance1 < $balance2) {
                        // equipo1 necesita más blancas
                        $blancas = $equipo1['id_equipo'];
                        $negras = $equipo2['id_equipo'];
                    } elseif ($balance2 < $balance1) {
                        // equipo2 necesita más blancas
                        $blancas = $equipo2['id_equipo'];
                        $negras = $equipo1['id_equipo'];
                    } else {
                        // Balance igual, aleatorio o por puntuación
                        if ($equipo1['puntos'] >= $equipo2['puntos']) {
                            $blancas = $equipo1['id_equipo'];
                            $negras = $equipo2['id_equipo'];
                        } else {
                            $blancas = $equipo2['id_equipo'];
                            $negras = $equipo1['id_equipo'];
                        }
                    }
                    
                    $emparejamientos[] = [
                        'blancas' => $blancas,
                        'negras' => $negras
                    ];
                    
                    array_splice($equipos_grupo, $i, 1);
                    $emparejado = true;
                    break;
                }
                
                // Si no pudo emparejar, buscar en otros grupos (fallback)
                if (!$emparejado && count($equipos_grupo) > 0) {
                    // Tomar el siguiente del mismo grupo
                    $equipo2 = array_shift($equipos_grupo);
                    $emparejamientos[] = [
                        'blancas' => $equipo1['id_equipo'],
                        'negras' => $equipo2['id_equipo']
                    ];
                }
            }
            
            // Si quedó un impar en el grupo, lo pasamos al siguiente
            if (count($equipos_grupo) == 1) {
                // Se agregará al siguiente grupo
                $siguiente_grupo_idx = array_search($grupo, $grupos_puntuacion) + 1;
                if ($siguiente_grupo_idx < count($grupos_puntuacion)) {
                    array_unshift($grupos_puntuacion[$siguiente_grupo_idx]['equipos'], $equipos_grupo[0]);
                }
            }
        }
        
        return $emparejamientos;
    }
    
    /**
     * Primera ronda del sistema suizo: empareja top vs bottom
     */
    private function generarPrimeraRondaSuizo($equipos) {
        $emparejamientos = [];
        
        // Obtener rating promedio de cada equipo
        foreach ($equipos as &$equipo) {
            $jugadores = $this->obtenerJugadoresOrdenados($equipo['id_equipo']);
            $suma_ratings = array_sum(array_column($jugadores, 'rating'));
            $equipo['rating_promedio'] = count($jugadores) > 0 ? $suma_ratings / count($jugadores) : 1200;
        }
        
        // Ordenar por rating
        usort($equipos, function($a, $b) {
            return $b['rating_promedio'] <=> $a['rating_promedio'];
        });
        
        $num_equipos = count($equipos);
        
        // Añadir BYE si es impar
        if ($num_equipos % 2 != 0) {
            $equipos[] = ['id_equipo' => null, 'nombre' => 'BYE'];
            $num_equipos++;
        }
        
        // Emparejar top mitad vs bottom mitad
        $mitad = $num_equipos / 2;
        for ($i = 0; $i < $mitad; $i++) {
            $equipo_top = $equipos[$i];
            $equipo_bottom = $equipos[$mitad + $i];
            
            if ($equipo_top['id_equipo'] !== null && $equipo_bottom['id_equipo'] !== null) {
                $emparejamientos[] = [
                    'blancas' => $equipo_top['id_equipo'],
                    'negras' => $equipo_bottom['id_equipo']
                ];
            }
        }
        
        return $emparejamientos;
    }
    
    /**
     * Obtiene equipos con su puntuación actual en el torneo
     */
    private function obtenerEquiposConPuntuacion($equipos, $id_torneo) {
        $equipos_con_puntos = [];
        
        foreach ($equipos as $equipo) {
            $stmt = $this->pdo->prepare("
                SELECT 
                    SUM(CASE 
                        WHEN (em.equipo_blancas = ? AND sub.puntos_blancas > sub.puntos_negras) OR
                             (em.equipo_negras = ? AND sub.puntos_negras > sub.puntos_blancas) THEN 2
                        WHEN sub.puntos_blancas = sub.puntos_negras THEN 1
                        ELSE 0
                    END) as puntos_match
                FROM emparejamientos em
                INNER JOIN rondas r ON em.id_ronda = r.id_ronda
                INNER JOIN (
                    SELECT id_emparejamiento,
                           SUM(CASE WHEN resultado = '1-0' THEN 1 WHEN resultado = '1/2' THEN 0.5 ELSE 0 END) as puntos_blancas,
                           SUM(CASE WHEN resultado = '0-1' THEN 1 WHEN resultado = '1/2' THEN 0.5 ELSE 0 END) as puntos_negras
                    FROM partidas
                    GROUP BY id_emparejamiento
                ) sub ON em.id_emparejamiento = sub.id_emparejamiento
                WHERE r.id_torneo = ? AND (em.equipo_blancas = ? OR em.equipo_negras = ?)
            ");
            $stmt->execute([
                $equipo['id_equipo'], 
                $equipo['id_equipo'], 
                $id_torneo, 
                $equipo['id_equipo'], 
                $equipo['id_equipo']
            ]);
            $resultado = $stmt->fetch();
            
            // Obtener rating promedio
            $jugadores = $this->obtenerJugadoresOrdenados($equipo['id_equipo']);
            $suma_ratings = array_sum(array_column($jugadores, 'rating'));
            $rating_promedio = count($jugadores) > 0 ? $suma_ratings / count($jugadores) : 1200;
            
            $equipos_con_puntos[] = [
                'id_equipo' => $equipo['id_equipo'],
                'nombre' => $equipo['nombre'],
                'puntos' => $resultado['puntos_match'] ?? 0,
                'rating_promedio' => $rating_promedio
            ];
        }
        
        return $equipos_con_puntos;
    }
    
    /**
     * Agrupa equipos por puntuación similar
     */
    private function agruparPorPuntuacion($equipos_con_puntos) {
        $grupos = [];
        $puntos_actual = null;
        $grupo_actual = [];
        
        foreach ($equipos_con_puntos as $equipo) {
            if ($puntos_actual === null || $puntos_actual == $equipo['puntos']) {
                $puntos_actual = $equipo['puntos'];
                $grupo_actual[] = $equipo;
            } else {
                if (!empty($grupo_actual)) {
                    $grupos[] = ['puntos' => $puntos_actual, 'equipos' => $grupo_actual];
                }
                $puntos_actual = $equipo['puntos'];
                $grupo_actual = [$equipo];
            }
        }
        
        if (!empty($grupo_actual)) {
            $grupos[] = ['puntos' => $puntos_actual, 'equipos' => $grupo_actual];
        }
        
        return $grupos;
    }
    
    /**
     * Obtiene el historial de enfrentamientos del torneo
     */
    private function obtenerHistorialEnfrentamientos($id_torneo) {
        $stmt = $this->pdo->prepare("
            SELECT em.equipo_blancas, em.equipo_negras
            FROM emparejamientos em
            INNER JOIN rondas r ON em.id_ronda = r.id_ronda
            WHERE r.id_torneo = ? AND em.equipo_blancas IS NOT NULL AND em.equipo_negras IS NOT NULL
        ");
        $stmt->execute([$id_torneo]);
        return $stmt->fetchAll();
    }
    
    /**
     * Verifica si dos equipos ya se enfrentaron
     */
    private function yaSeEnfrentaron($id_equipo1, $id_equipo2, $historial) {
        foreach ($historial as $enfrentamiento) {
            if (($enfrentamiento['equipo_blancas'] == $id_equipo1 && $enfrentamiento['equipo_negras'] == $id_equipo2) ||
                ($enfrentamiento['equipo_blancas'] == $id_equipo2 && $enfrentamiento['equipo_negras'] == $id_equipo1)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Verifica si un equipo ha recibido BYE
     */
    private function haRecibidoBye($id_equipo, $historial) {
        foreach ($historial as $enfrentamiento) {
            if (($enfrentamiento['equipo_blancas'] == $id_equipo && $enfrentamiento['equipo_negras'] === null) ||
                ($enfrentamiento['equipo_negras'] == $id_equipo && $enfrentamiento['equipo_blancas'] === null)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Obtiene el balance de colores de cada equipo (positivo = más blancas, negativo = más negras)
     */
    private function obtenerBalanceColores($equipos, $id_torneo) {
        $balances = [];
        
        foreach ($equipos as $equipo) {
            $stmt = $this->pdo->prepare("
                SELECT 
                    SUM(CASE WHEN em.equipo_blancas = ? THEN 1 ELSE 0 END) as veces_blancas,
                    SUM(CASE WHEN em.equipo_negras = ? THEN 1 ELSE 0 END) as veces_negras
                FROM emparejamientos em
                INNER JOIN rondas r ON em.id_ronda = r.id_ronda
                WHERE r.id_torneo = ? AND (em.equipo_blancas = ? OR em.equipo_negras = ?)
            ");
            $stmt->execute([
                $equipo['id_equipo'],
                $equipo['id_equipo'],
                $id_torneo,
                $equipo['id_equipo'],
                $equipo['id_equipo']
            ]);
            $resultado = $stmt->fetch();
            
            $veces_blancas = $resultado['veces_blancas'] ?? 0;
            $veces_negras = $resultado['veces_negras'] ?? 0;
            
            $balances[$equipo['id_equipo']] = $veces_blancas - $veces_negras;
        }
        
        return $balances;
    }
    
    private function generarEmparejamientosRoundRobin($equipos, $numero_ronda) {
        $num_equipos = count($equipos);
        $emparejamientos = [];
        
        // Añadir BYE si es impar
        if ($num_equipos % 2 != 0) {
            $equipos[] = ['id_equipo' => null, 'nombre' => 'BYE'];
            $num_equipos++;
        }
        
        $num_rondas = $num_equipos - 1;
        $equipos_fijos = $equipos;
        
        // Algoritmo Round Robin
        for ($ronda = 1; $ronda <= $num_rondas; $ronda++) {
            $emparejamientos_ronda = [];
            
            for ($i = 0; $i < $num_equipos / 2; $i++) {
                $equipo1 = $equipos_fijos[$i];
                $equipo2 = $equipos_fijos[$num_equipos - 1 - $i];
                
                if ($equipo1['id_equipo'] !== null && $equipo2['id_equipo'] !== null) {
                    // Alternar colores
                    if ($ronda % 2 == 0) {
                        $emparejamientos_ronda[] = [
                            'blancas' => $equipo1['id_equipo'],
                            'negras' => $equipo2['id_equipo']
                        ];
                    } else {
                        $emparejamientos_ronda[] = [
                            'blancas' => $equipo2['id_equipo'],
                            'negras' => $equipo1['id_equipo']
                        ];
                    }
                }
            }
            
            if ($ronda == $numero_ronda) {
                $emparejamientos = $emparejamientos_ronda;
                break;
            }
            
            // Rotar equipos (excepto el primero)
            $ultimo = array_pop($equipos_fijos);
            array_splice($equipos_fijos, 1, 0, [$ultimo]);
        }
        
        return $emparejamientos;
    }
    
    private function generarPartidasNormales($id_emparejamiento, $id_equipo_blancas, $id_equipo_negras, $nro_tableros) {
        $jugadores_blancas = $this->obtenerJugadoresOrdenados($id_equipo_blancas);
        $jugadores_negras = $this->obtenerJugadoresOrdenados($id_equipo_negras);
        
        for ($tablero = 1; $tablero <= $nro_tableros; $tablero++) {
            $id_jug_blancas = isset($jugadores_blancas[$tablero - 1]) ? $jugadores_blancas[$tablero - 1]['id_jugador'] : null;
            $id_jug_negras = isset($jugadores_negras[$tablero - 1]) ? $jugadores_negras[$tablero - 1]['id_jugador'] : null;
            
            if ($id_jug_blancas && $id_jug_negras) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO partidas (id_emparejamiento, tablero, id_jugador_blancas, id_jugador_negras, created_at) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$id_emparejamiento, $tablero, $id_jug_blancas, $id_jug_negras]);
            }
        }
    }
    
    private function generarPartidasPonderadas($id_emparejamiento, $id_equipo_blancas, $id_equipo_negras, $id_torneo, $numero_ronda, $nro_tableros) {
        $jugadores_blancas = $this->obtenerJugadoresPorPuntuacion($id_equipo_blancas, $id_torneo);
        $jugadores_negras = $this->obtenerJugadoresPorPuntuacion($id_equipo_negras, $id_torneo);
        
        $enfrentamientos_previos = $this->obtenerEnfrentamientosPrevios($id_equipo_blancas, $id_equipo_negras, $id_torneo, $numero_ronda);
        
        for ($tablero = 1; $tablero <= $nro_tableros; $tablero++) {
            $id_jug_blancas = isset($jugadores_blancas[$tablero - 1]) ? $jugadores_blancas[$tablero - 1]['id_jugador'] : null;
            $id_jug_negras = isset($jugadores_negras[$tablero - 1]) ? $jugadores_negras[$tablero - 1]['id_jugador'] : null;
            
            if ($id_jug_blancas && $id_jug_negras) {
                // Verificar si ya se enfrentaron
                $ya_jugaron = false;
                foreach ($enfrentamientos_previos as $enf) {
                    if (($enf['id_jugador_blancas'] == $id_jug_blancas && $enf['id_jugador_negras'] == $id_jug_negras) ||
                        ($enf['id_jugador_blancas'] == $id_jug_negras && $enf['id_jugador_negras'] == $id_jug_blancas)) {
                        $ya_jugaron = true;
                        break;
                    }
                }
                
                if (!$ya_jugaron) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO partidas (id_emparejamiento, tablero, id_jugador_blancas, id_jugador_negras, created_at) 
                        VALUES (?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([$id_emparejamiento, $tablero, $id_jug_blancas, $id_jug_negras]);
                }
            }
        }
    }
    
    public function obtenerRondas($id_torneo) {
        $stmt = $this->pdo->prepare("
            SELECT r.*, 
                   COUNT(DISTINCT em.id_emparejamiento) as num_emparejamientos,
                   COUNT(p.id_partida) as partidas_jugadas
            FROM rondas r
            LEFT JOIN emparejamientos em ON r.id_ronda = em.id_ronda
            LEFT JOIN partidas p ON em.id_emparejamiento = p.id_emparejamiento
            WHERE r.id_torneo = ?
            GROUP BY r.id_ronda, r.numero_ronda, r.fecha_ronda
            ORDER BY r.numero_ronda ASC
        ");
        $stmt->execute([$id_torneo]);
        return $stmt->fetchAll();
    }
    
    public function obtenerPartidas($id_ronda) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, em.equipo_blancas, em.equipo_negras,
                   eb.nombre as equipo_blancas_nombre, en.nombre as equipo_negras_nombre,
                   jb.nombre as jugador_blancas_nombre, jb.apellido as jugador_blancas_apellido,
                   jn.nombre as jugador_negras_nombre, jn.apellido as jugador_negras_apellido
            FROM partidas p
            INNER JOIN emparejamientos em ON p.id_emparejamiento = em.id_emparejamiento
            LEFT JOIN equipos eb ON em.equipo_blancas = eb.id_equipo
            LEFT JOIN equipos en ON em.equipo_negras = en.id_equipo
            LEFT JOIN jugadores jb ON p.id_jugador_blancas = jb.id_jugador
            LEFT JOIN jugadores jn ON p.id_jugador_negras = jn.id_jugador
            WHERE em.id_ronda = ?
            ORDER BY p.tablero
        ");
        $stmt->execute([$id_ronda]);
        return $stmt->fetchAll();
    }
    
    public function obtenerProximaRonda($id_torneo) {
        $stmt = $this->pdo->prepare("SELECT COALESCE(MAX(numero_ronda), 0) + 1 as proxima_ronda FROM rondas WHERE id_torneo = ?");
        $stmt->execute([$id_torneo]);
        $result = $stmt->fetch();
        return $result['proxima_ronda'];
    }
    
    public function obtenerEmparejamientos($id_torneo) {
        $stmt = $this->pdo->prepare("
            SELECT r.numero_ronda, r.fecha_ronda,
                   em.equipo_blancas, em.equipo_negras,
                   eb.nombre as equipo_blancas_nombre, en.nombre as equipo_negras_nombre,
                   p.tablero, p.resultado,
                   jb.nombre as jugador_blancas_nombre, jb.apellido as jugador_blancas_apellido,
                   jn.nombre as jugador_negras_nombre, jn.apellido as jugador_negras_apellido
            FROM rondas r
            INNER JOIN emparejamientos em ON r.id_ronda = em.id_ronda
            LEFT JOIN equipos eb ON em.equipo_blancas = eb.id_equipo
            LEFT JOIN equipos en ON em.equipo_negras = en.id_equipo
            LEFT JOIN partidas p ON em.id_emparejamiento = p.id_emparejamiento
            LEFT JOIN jugadores jb ON p.id_jugador_blancas = jb.id_jugador
            LEFT JOIN jugadores jn ON p.id_jugador_negras = jn.id_jugador
            WHERE r.id_torneo = ?
            ORDER BY r.numero_ronda, p.tablero
        ");
        $stmt->execute([$id_torneo]);
        return $stmt->fetchAll();
    }
    
    private function obtenerJugadoresOrdenados($id_equipo) {
        $stmt = $this->pdo->prepare("
            SELECT id_jugador, nombre, apellido, rating
            FROM jugadores
            WHERE id_equipo = ? AND activo = 1
            ORDER BY rating DESC
        ");
        $stmt->execute([$id_equipo]);
        return $stmt->fetchAll();
    }
    
    private function obtenerJugadoresPorPuntuacion($id_equipo, $id_torneo) {
        $stmt = $this->pdo->prepare("
            SELECT j.id_jugador, j.nombre, j.apellido, j.rating,
                   COALESCE((
                       SELECT SUM(
                           CASE
                               WHEN (p.id_jugador_blancas = j.id_jugador AND p.resultado = '1-0') OR
                                    (p.id_jugador_negras = j.id_jugador AND p.resultado = '0-1') THEN 1
                               WHEN p.resultado = '1/2' THEN 0.5
                               ELSE 0
                           END
                       )
                       FROM partidas p
                       INNER JOIN emparejamientos em ON p.id_emparejamiento = em.id_emparejamiento
                       INNER JOIN rondas r ON em.id_ronda = r.id_ronda
                       WHERE r.id_torneo = ? AND (p.id_jugador_blancas = j.id_jugador OR p.id_jugador_negras = j.id_jugador)
                   ), 0) as puntuacion_acumulada
            FROM jugadores j
            WHERE j.id_equipo = ? AND j.activo = 1
            ORDER BY puntuacion_acumulada DESC, j.rating DESC
        ");
        $stmt->execute([$id_torneo, $id_equipo]);
        return $stmt->fetchAll();
    }
    
    private function obtenerEnfrentamientosPrevios($id_equipo_blancas, $id_equipo_negras, $id_torneo, $ronda_actual) {
        $stmt = $this->pdo->prepare("
            SELECT p.id_jugador_blancas, p.id_jugador_negras
            FROM partidas p
            INNER JOIN emparejamientos em ON p.id_emparejamiento = em.id_emparejamiento
            INNER JOIN rondas r ON em.id_ronda = r.id_ronda
            WHERE r.id_torneo = ? AND r.numero_ronda < ?
            AND ((em.equipo_blancas = ? AND em.equipo_negras = ?) OR (em.equipo_blancas = ? AND em.equipo_negras = ?))
        ");
        $stmt->execute([$id_torneo, $ronda_actual, $id_equipo_blancas, $id_equipo_negras, $id_equipo_negras, $id_equipo_blancas]);
        return $stmt->fetchAll();
    }
    
    private function reiniciarEstadisticas($id_torneo) {
        $stmt = $this->pdo->prepare("DELETE FROM estadisticas_equipo WHERE id_torneo = ?");
        $stmt->execute([$id_torneo]);
        
        $stmt = $this->pdo->prepare("DELETE FROM estadisticas_jugador WHERE id_torneo = ?");
        $stmt->execute([$id_torneo]);
    }
}
