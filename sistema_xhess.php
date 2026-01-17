<?php
session_start();

class XhessSystem {
    private $pdo;
    
    public function __construct() {
        $this->connectDB();
    }
    
    private function connectDB() {
        try {
            $this->pdo = new PDO(
                "mysql:host=127.0.0.1;dbname=xhess;charset=utf8mb4",
                "root", 
                "",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    
    // ========== NUEVO MÉTODO: Verificar si un torneo tiene rondas generadas ==========
    public function tieneRondasGeneradas($id_torneo) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM rondas WHERE id_torneo = ?");
        $stmt->execute([$id_torneo]);
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }
    
    // ========== MÉTODOS PARA AUTO-GESTIÓN DE JUGADORES ==========
    
    public function unirseAEquipo($id_jugador, $id_equipo) {
    $this->pdo->beginTransaction();
    try {
        $stmt = $this->pdo->prepare("
            SELECT j.id_jugador, j.id_cuenta, j.id_equipo 
            FROM jugadores j 
            WHERE j.id_jugador = ? AND j.id_cuenta IS NOT NULL
        ");
        $stmt->execute([$id_jugador]);
        $jugador = $stmt->fetch();
        
        if (!$jugador) {
            throw new Exception("Jugador no válido o sin cuenta registrada");
        }
        
        $stmt = $this->pdo->prepare("SELECT id_equipo FROM equipos WHERE id_equipo = ?");
        $stmt->execute([$id_equipo]);
        if (!$stmt->fetch()) {
            throw new Exception("El equipo no existe");
        }
        
        $stmt = $this->pdo->prepare("SELECT id_equipo FROM jugadores WHERE id_jugador = ? AND id_equipo = ?");
        $stmt->execute([$id_jugador, $id_equipo]);
        if ($stmt->fetch()) {
            throw new Exception("Ya estás en este equipo");
        }
        
        // VERIFICACIÓN NUEVA: No permitir unirse si el equipo está en torneos después de ronda 2
        $stmt = $this->pdo->prepare("
            SELECT t.id_torneo, t.nombre, MAX(r.numero_ronda) as ronda_maxima
            FROM torneos t
            JOIN torneo_equipos te ON t.id_torneo = te.id_torneo
            LEFT JOIN rondas r ON t.id_torneo = r.id_torneo
            WHERE te.id_equipo = ? 
            AND t.condicion = 'en_progreso'
            GROUP BY t.id_torneo, t.nombre
            HAVING ronda_maxima >= 2
        ");
        $stmt->execute([$id_equipo]);
        $torneos_prohibidos = $stmt->fetchAll();
        
        if (!empty($torneos_prohibidos)) {
            $nombres_torneos = array_column($torneos_prohibidos, 'nombre');
            throw new Exception("No puedes unirte a este equipo porque está participando en torneos activos después de la segunda ronda: " . implode(', ', $nombres_torneos));
        }
        
        // Si el jugador ya tenía un equipo, sacarlo de los torneos activos
        if ($jugador['id_equipo']) {
            $this->removerJugadorDeTorneosActivos($id_jugador, $jugador['id_equipo']);
        }
        
        $stmt = $this->pdo->prepare("UPDATE jugadores SET id_equipo = ? WHERE id_jugador = ?");
        $stmt->execute([$id_equipo, $id_jugador]);
        
        // Inscribir al jugador en los torneos activos del nuevo equipo
        $this->inscribirJugadorEnTorneosEquipo($id_jugador, $id_equipo);
        
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
        $stmt = $this->pdo->prepare("
            SELECT j.id_jugador, j.id_cuenta, j.id_equipo 
            FROM jugadores j 
            WHERE j.id_jugador = ? AND j.id_cuenta IS NOT NULL
        ");
        $stmt->execute([$id_jugador]);
        $jugador = $stmt->fetch();
        
        if (!$jugador) {
            throw new Exception("Jugador no válido o sin cuenta registrada");
        }
        
        if (!$jugador['id_equipo']) {
            throw new Exception("No estás en ningún equipo actualmente");
        }
        
        // Remover al jugador de los torneos activos de su equipo actual
        $this->removerJugadorDeTorneosActivos($id_jugador, $jugador['id_equipo']);
        
        $stmt = $this->pdo->prepare("UPDATE jugadores SET id_equipo = NULL WHERE id_jugador = ?");
        $stmt->execute([$id_jugador]);
        
        $this->pdo->commit();
        return true;
        
    } catch (Exception $e) {
        $this->pdo->rollBack();
        throw $e;
    }
}
    
    public function getEquiposDisponiblesParaUnirse() {
        $stmt = $this->pdo->query("
            SELECT e.*, c.nombre as club_nombre,
                   (SELECT COUNT(*) FROM jugadores j WHERE j.id_equipo = e.id_equipo) as num_jugadores
            FROM equipos e 
            JOIN clubes c ON e.id_club = c.id_club 
            ORDER BY c.nombre, e.nombre
        ");
        return $stmt->fetchAll();
    }
    
    public function getEquipoActualJugador($id_jugador) {
        $stmt = $this->pdo->prepare("
            SELECT e.*, c.nombre as club_nombre
            FROM jugadores j
            JOIN equipos e ON j.id_equipo = e.id_equipo
            JOIN clubes c ON e.id_club = c.id_club
            WHERE j.id_jugador = ?
        ");
        $stmt->execute([$id_jugador]);
        return $stmt->fetch();
    }
    
    // ========== MÉTODOS PARA CREAR EQUIPOS (JUGADORES Y ADMINS) ==========
    
    public function crearEquipo($datos) {
        if (!$this->isAdmin() && !$this->isJugador()) {
            throw new Exception("No tienes permisos para crear equipos");
        }
        
        if ($this->existeEquipoEnClub($datos['nombre'], $datos['id_club'])) {
            throw new Exception("Ya existe un equipo con el nombre '{$datos['nombre']}' en este club.");
        }
        
        $id_creador = $_SESSION['user_id'];
        
        $stmt = $this->pdo->prepare("
            INSERT INTO equipos (nombre, id_club, id_coach, id_creador) 
            VALUES (?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $datos['nombre'],
            $datos['id_club'],
            $id_creador,
            $id_creador
        ]);
        
        if ($result && $this->isJugador() && isset($_SESSION['jugador_id'])) {
            $id_equipo = $this->pdo->lastInsertId();
            $stmt = $this->pdo->prepare("UPDATE jugadores SET id_equipo = ? WHERE id_jugador = ?");
            $stmt->execute([$id_equipo, $_SESSION['jugador_id']]);
        }
        
        return $result;
    }
    
    // ========== MÉTODOS SOLO PARA ADMINS ==========
    
    public function adminAsignarJugadorAEquipo($id_jugador, $id_equipo) {
        if (!$this->isAdmin()) {
            throw new Exception("Solo los administradores pueden asignar jugadores");
        }
        
        $stmt = $this->pdo->prepare("UPDATE jugadores SET id_equipo = ? WHERE id_jugador = ?");
        return $stmt->execute([$id_equipo, $id_jugador]);
    }
    
    public function adminQuitarJugadorDeEquipo($id_jugador) {
        if (!$this->isAdmin()) {
            throw new Exception("Solo los administradores pueden quitar jugadores de equipos");
        }
        
        $stmt = $this->pdo->prepare("UPDATE jugadores SET id_equipo = NULL WHERE id_jugador = ?");
        return $stmt->execute([$id_jugador]);
    }
    
    // ========== MÉTODOS DE VALIDACIÓN DE EQUIPOS ==========
    
    public function existeEquipoEnClub($nombre, $id_club, $excluir_id = null) {
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
    
    public function getEquiposPorClub($id_club) {
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
    
    public function eliminarEquipo($id_equipo) {
        if (!$this->isAdmin()) {
            throw new Exception("Solo los administradores pueden eliminar equipos");
        }
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM jugadores WHERE id_equipo = ?");
        $stmt->execute([$id_equipo]);
        $result = $stmt->fetch();
        
        if ($result['total'] > 0) {
            throw new Exception("No se puede eliminar el equipo porque tiene jugadores asignados.");
        }
        
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM torneo_equipos WHERE id_equipo = ?");
        $stmt->execute([$id_equipo]);
        $result = $stmt->fetch();
        
        if ($result['total'] > 0) {
            throw new Exception("No se puede eliminar el equipo porque está participando en torneos.");
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM equipos WHERE id_equipo = ?");
        return $stmt->execute([$id_equipo]);
    }
    
    // ========== MÉTODOS DE AUTENTICACIÓN ==========
    
    public function login($usuario, $contrasena) {
        $stmt = $this->pdo->prepare("SELECT * FROM cuentas WHERE usuario = ? AND activo = 1");
        $stmt->execute([$usuario]);
        $cuenta = $stmt->fetch();
        
        if ($cuenta && password_verify($contrasena, $cuenta['contrasena'])) {
            $_SESSION['usuario'] = $cuenta;
            $_SESSION['user_id'] = $cuenta['id_cuenta'];
            $_SESSION['username'] = $cuenta['usuario'];
            $_SESSION['user_role'] = $cuenta['id_rol'];
            
            $stmt_jugador = $this->pdo->prepare("SELECT id_jugador FROM jugadores WHERE id_cuenta = ?");
            $stmt_jugador->execute([$cuenta['id_cuenta']]);
            $jugador = $stmt_jugador->fetch();
            if ($jugador) {
                $_SESSION['jugador_id'] = $jugador['id_jugador'];
            }
            
            return true;
        }
        return false;
    }
    
    public function logout() {
        session_destroy();
        header("Location: index.php");
        exit;
    }
    
    public function registrarUsuario($datos) {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("SELECT id_cuenta FROM cuentas WHERE usuario = ? OR correo = ?");
            $stmt->execute([$datos['usuario'], $datos['correo']]);
            if ($stmt->fetch()) {
                $this->pdo->rollBack();
                throw new Exception("El usuario o correo electrónico ya existen");
            }
            
            $hash = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO cuentas (usuario, contrasena, correo, id_rol) VALUES (?, ?, ?, 3)");
            
            if (!$stmt->execute([$datos['usuario'], $hash, $datos['correo']])) {
                $this->pdo->rollBack();
                throw new Exception("Error al crear la cuenta");
            }
            
            $id_cuenta = $this->pdo->lastInsertId();
            
            $stmt_jugador = $this->pdo->prepare("
                INSERT INTO jugadores (nombre, apellido, rating, id_cuenta, activo) 
                VALUES (?, ?, 1200, ?, 1)
            ");
            
            $nombre = $datos['nombre'] ?? $datos['usuario'];
            $apellido = $datos['apellido'] ?? '';
            
            if (!$stmt_jugador->execute([$nombre, $apellido, $id_cuenta])) {
                $this->pdo->rollBack();
                throw new Exception("Error al crear el perfil de jugador");
            }
            
            $id_jugador = $this->pdo->lastInsertId();
            
            $this->pdo->commit();
            
            $_SESSION['user_id'] = $id_cuenta;
            $_SESSION['username'] = $datos['usuario'];
            $_SESSION['user_role'] = 3;
            $_SESSION['jugador_id'] = $id_jugador;
            
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    // ========== MÉTODOS DE UTILIDAD ==========
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1;
    }
    
    public function isJugador() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 3;
    }
    
    public function getJugadoresRegistrados() {
        $stmt = $this->pdo->query("
            SELECT j.*, c.usuario, c.correo, e.nombre as equipo_actual, 
                   e.id_equipo as id_equipo_actual
            FROM jugadores j 
            JOIN cuentas c ON j.id_cuenta = c.id_cuenta 
            LEFT JOIN equipos e ON j.id_equipo = e.id_equipo
            WHERE j.activo = 1
            ORDER BY j.nombre, j.apellido
        ");
        return $stmt->fetchAll();
    }
    
    public function getJugadoresPublicos() {
        $stmt = $this->pdo->query("
            SELECT j.*, e.nombre as equipo_nombre
            FROM jugadores j 
            LEFT JOIN equipos e ON j.id_equipo = e.id_equipo
            WHERE j.activo = 1
            ORDER BY j.rating DESC
            LIMIT 12
        ");
        return $stmt->fetchAll();
    }
    
    public function getJugadoresPorEquipo($id_equipo) {
        $stmt = $this->pdo->prepare("
            SELECT j.*, c.usuario, c.correo 
            FROM jugadores j 
            JOIN cuentas c ON j.id_cuenta = c.id_cuenta 
            WHERE j.id_equipo = ? AND j.activo = 1
        ");
        $stmt->execute([$id_equipo]);
        return $stmt->fetchAll();
    }
    
    public function getEquiposDisponibles() {
        $stmt = $this->pdo->query("
            SELECT e.*, c.nombre as club_nombre,
                   (SELECT COUNT(*) FROM jugadores j WHERE j.id_equipo = e.id_equipo) as num_jugadores
            FROM equipos e 
            JOIN clubes c ON e.id_club = c.id_club 
            ORDER BY c.nombre, e.nombre
        ");
        return $stmt->fetchAll();
    }
    
    public function getClubes() {
        $stmt = $this->pdo->query("SELECT * FROM clubes ORDER BY nombre");
        return $stmt->fetchAll();
    }
    
    // ========== MÉTODOS DE TORNEOS MEJORADOS ==========
    
    public function getTorneosPublicos() {
        $torneos = $this->pdo->query("
        SELECT t.*, c.nombre as club_nombre
        FROM torneos t
        LEFT JOIN clubes c ON t.id_club = c.id_club
        WHERE t.condicion IN ('pendiente', 'en_progreso', 'finalizado')
        ORDER BY
        CASE
        WHEN t.condicion = 'en_progreso' THEN 1
        WHEN t.condicion = 'pendiente' THEN 2
        WHEN t.condicion = 'finalizado' THEN 3
        END,
        t.fecha_inicio DESC
        LIMIT 20
        ")->fetchAll();

        // Actualizar estados
        foreach ($torneos as &$torneo) {
            $this->actualizarEstadoTorneo($torneo['id_torneo']);
        }

        return $torneos;
    }
    
    public function getMisTorneos($id_cuenta) {
        $stmt = $this->pdo->prepare("
            SELECT t.*, c.nombre as club_nombre 
            FROM torneos t 
            LEFT JOIN clubes c ON t.id_club = c.id_club 
            WHERE t.id_cuenta = ? 
            ORDER BY t.created_at DESC
            LIMIT 20  -- Aumentado el límite para mostrar más torneos
        ");
        $stmt->execute([$id_cuenta]);
        return $stmt->fetchAll();
    }
    
    public function crearTorneo($datos) {
        $stmt = $this->pdo->prepare("
            INSERT INTO torneos (nombre, descripcion, tipo, sistema, nro_tableros, fecha_inicio, fecha_fin, ubicacion, ritmo_tiempo, id_club, id_cuenta) 
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
    
    public function getClasificacionEquipos($id_torneo) {
        $stmt = $this->pdo->prepare("
            SELECT 
                e.id_equipo,
                e.nombre as equipo_nombre,
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
    
    public function getClasificacionJugadores($id_torneo) {
        $stmt = $this->pdo->prepare("
        SELECT
        j.id_jugador,
        CONCAT(j.nombre, ' ', COALESCE(j.apellido, '')) as nombre_jugador,
                                    e.nombre as equipo_nombre,
                                    j.rating,
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

    
    public function getRondasTorneo($id_torneo) {
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
    
    public function getPartidasRonda($id_ronda) {
        $stmt = $this->pdo->prepare("
            SELECT 
                p.*,
                em.equipo_blancas,
                em.equipo_negras,
                eb.nombre as equipo_blancas_nombre,
                en.nombre as equipo_negras_nombre,
                jb.nombre as jugador_blancas_nombre,
                jb.apellido as jugador_blancas_apellido,
                jn.nombre as jugador_negras_nombre,
                jn.apellido as jugador_negras_apellido
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
    
    public function actualizarResultadoPartida($id_partida, $resultado) {
        $stmt = $this->pdo->prepare("UPDATE partidas SET resultado = ? WHERE id_partida = ?");
        return $stmt->execute([$resultado, $id_partida]);
    }
    
    public function actualizarMultiplesResultados($resultados) {
        $this->pdo->beginTransaction();
        try {
            foreach ($resultados as $id_partida => $resultado) {
                // Actualizar resultado
                $stmt = $this->pdo->prepare("UPDATE partidas SET resultado = ? WHERE id_partida = ?");
                $stmt->execute([$resultado, $id_partida]);

                // Actualizar ratings si el resultado no es *
                if ($resultado != '*') {
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
                        // Actualizar rating del jugador de blancas
                        $this->actualizarRatingJugador(
                            $partida['id_jugador_blancas'],
                            $resultado,
                            $partida['rating_negras']
                        );

                        // Actualizar rating del jugador de negras
                        $resultado_negras = ($resultado == '1-0') ? '0-1' : (($resultado == '0-1') ? '1-0' : '1/2');
                        $this->actualizarRatingJugador(
                            $partida['id_jugador_negras'],
                            $resultado_negras,
                            $partida['rating_blancas']
                        );
                    }
                }
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function getEquiposTorneo($id_torneo) {
        $stmt = $this->pdo->prepare("
            SELECT e.* FROM equipos e
            INNER JOIN torneo_equipos te ON e.id_equipo = te.id_equipo
            WHERE te.id_torneo = ?
        ");
        $stmt->execute([$id_torneo]);
        return $stmt->fetchAll();
    }
    
    public function agregarEquipoATorneo($id_torneo, $id_equipo) {
        if (!$this->verificarPropiedadTorneo($id_torneo, $_SESSION['user_id'])) {
            return false;
        }
        
        $stmt = $this->pdo->prepare("INSERT IGNORE INTO torneo_equipos (id_torneo, id_equipo) VALUES (?, ?)");
        return $stmt->execute([$id_torneo, $id_equipo]);
    }
    
    public function agregarEquiposMultiplesATorneo($id_torneo, $id_equipos) {
        if (!$this->verificarPropiedadTorneo($id_torneo, $_SESSION['user_id'])) {
            return false;
        }

        $this->pdo->beginTransaction();
        try {
            foreach ($id_equipos as $id_equipo) {
                $stmt = $this->pdo->prepare("INSERT IGNORE INTO torneo_equipos (id_torneo, id_equipo) VALUES (?, ?)");
                $stmt->execute([$id_torneo, $id_equipo]);
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function retirarEquipoDeTorneo($id_torneo, $id_equipo) {
        if (!$this->verificarPropiedadTorneo($id_torneo, $_SESSION['user_id'])) {
            return false;
        }

        // Verificar si hay rondas generadas
        $tieneRondas = $this->tieneRondasGeneradas($id_torneo);

        if ($tieneRondas) {
            // Si hay rondas, marcar el equipo como retirado
            $stmt = $this->pdo->prepare("
            UPDATE torneo_equipos
            SET retirado = 1
            WHERE id_torneo = ? AND id_equipo = ?
            ");
            return $stmt->execute([$id_torneo, $id_equipo]);
        } else {
            // Si no hay rondas, eliminar completamente
            $stmt = $this->pdo->prepare("DELETE FROM torneo_equipos WHERE id_torneo = ? AND id_equipo = ?");
            return $stmt->execute([$id_torneo, $id_equipo]);
        }
    }
    
    public function getTodosTorneos() {
        if ($_SESSION['user_role'] != 1) return [];
        
        $stmt = $this->pdo->query("
            SELECT t.*, c.usuario as creador 
            FROM torneos t 
            LEFT JOIN cuentas c ON t.id_cuenta = c.id_cuenta 
            ORDER BY t.created_at DESC
            LIMIT 50  -- Aumentado el límite
        ");
        return $stmt->fetchAll();
    }
    
    public function getTodasCuentas() {
        if ($_SESSION['user_role'] != 1) return [];
        
        $stmt = $this->pdo->query("
            SELECT c.*, r.nombre as rol_nombre 
            FROM cuentas c 
            LEFT JOIN roles r ON c.id_rol = r.id_rol 
            ORDER BY c.fecha_creacion DESC
            LIMIT 50  -- Aumentado el límite
        ");
        return $stmt->fetchAll();
    }
    
    private function verificarPropiedadTorneo($id_torneo, $id_cuenta) {
        if ($_SESSION['user_role'] == 1) return true;
        
        $stmt = $this->pdo->prepare("SELECT id_torneo FROM torneos WHERE id_torneo = ? AND id_cuenta = ?");
        $stmt->execute([$id_torneo, $id_cuenta]);
        return $stmt->fetch() !== false;
    }
    
    public function getProximaRonda($id_torneo) {
        $stmt = $this->pdo->prepare("SELECT COALESCE(MAX(numero_ronda), 0) + 1 as proxima_ronda FROM rondas WHERE id_torneo = ?");
        $stmt->execute([$id_torneo]);
        $result = $stmt->fetch();
        return $result['proxima_ronda'];
    }
    
    // ========== NUEVOS MÉTODOS ADMIN ==========
    
    public function eliminarTorneo($id_torneo) {
        if (!$this->isAdmin()) {
            throw new Exception("Solo los administradores pueden eliminar torneos");
        }
        
        $this->pdo->beginTransaction();
        try {
            // Eliminar partidas relacionadas
            $stmt = $this->pdo->prepare("
                DELETE p FROM partidas p
                INNER JOIN emparejamientos em ON p.id_emparejamiento = em.id_emparejamiento
                INNER JOIN rondas r ON em.id_ronda = r.id_ronda
                WHERE r.id_torneo = ?
            ");
            $stmt->execute([$id_torneo]);
            
            // Eliminar emparejamientos
            $stmt = $this->pdo->prepare("
                DELETE em FROM emparejamientos em
                INNER JOIN rondas r ON em.id_ronda = r.id_ronda
                WHERE r.id_torneo = ?
            ");
            $stmt->execute([$id_torneo]);
            
            // Eliminar rondas
            $stmt = $this->pdo->prepare("DELETE FROM rondas WHERE id_torneo = ?");
            $stmt->execute([$id_torneo]);
            
            // Eliminar equipos del torneo
            $stmt = $this->pdo->prepare("DELETE FROM torneo_equipos WHERE id_torneo = ?");
            $stmt->execute([$id_torneo]);
            
            // Eliminar jugadores del torneo
            $stmt = $this->pdo->prepare("DELETE FROM torneo_jugadores WHERE id_torneo = ?");
            $stmt->execute([$id_torneo]);
            
            // Finalmente eliminar el torneo
            $stmt = $this->pdo->prepare("DELETE FROM torneos WHERE id_torneo = ?");
            $stmt->execute([$id_torneo]);
            
            $this->pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function eliminarCuenta($id_cuenta) {
        if (!$this->isAdmin()) {
            throw new Exception("Solo los administradores pueden eliminar cuentas");
        }

        if ($id_cuenta == 1) {
            throw new Exception("No se puede eliminar la cuenta de administrador principal");
        }

        $this->pdo->beginTransaction();
        try {
            // PRIMERO: Encontrar el jugador asociado a esta cuenta
            $stmt = $this->pdo->prepare("SELECT id_jugador FROM jugadores WHERE id_cuenta = ?");
            $stmt->execute([$id_cuenta]);
            $jugador = $stmt->fetch();

            if ($jugador) {
                $id_jugador = $jugador['id_jugador'];

                // ELIMINAR de torneo_jugadores
                $stmt = $this->pdo->prepare("DELETE FROM torneo_jugadores WHERE id_jugador = ?");
                $stmt->execute([$id_jugador]);

                // ELIMINAR de partidas (como jugador_blancas o jugador_negras)
                $stmt = $this->pdo->prepare("UPDATE partidas SET id_jugador_blancas = NULL WHERE id_jugador_blancas = ?");
                $stmt->execute([$id_jugador]);

                $stmt = $this->pdo->prepare("UPDATE partidas SET id_jugador_negras = NULL WHERE id_jugador_negras = ?");
                $stmt->execute([$id_jugador]);

                // ELIMINAR el jugador
                $stmt = $this->pdo->prepare("DELETE FROM jugadores WHERE id_jugador = ?");
                $stmt->execute([$id_jugador]);
            }

            // FINALMENTE eliminar la cuenta
            $stmt = $this->pdo->prepare("DELETE FROM cuentas WHERE id_cuenta = ?");
            $stmt->execute([$id_cuenta]);

            $this->pdo->commit();
            return true;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Error al eliminar cuenta: " . $e->getMessage());
        }
    }
    
    // ========== GENERACIÓN DE RONDAS MEJORADA ==========
    public function generarRonda($id_torneo, $numero_ronda, $fecha_ronda) {
        try {
            $this->pdo->beginTransaction();
            
            // Verificar número máximo de rondas según equipos
            $equipos = $this->getEquiposTorneo($id_torneo);
            $num_equipos = count($equipos);
            
            if ($num_equipos < 2) {
                throw new Exception("Se necesitan al menos 2 equipos para generar rondas");
            }
            
            // Calcular máximo de rondas para round robin
            $max_rondas = $num_equipos - 1;
            if ($num_equipos % 2 != 0) {
                $max_rondas = $num_equipos;
            }
            
            if ($numero_ronda > $max_rondas) {
                throw new Exception("No se pueden generar más de $max_rondas rondas para $num_equipos equipos");
            }
            
            // Verificar si ya existe esta ronda
            $stmt = $this->pdo->prepare("SELECT id_ronda FROM rondas WHERE id_torneo = ? AND numero_ronda = ?");
            $stmt->execute([$id_torneo, $numero_ronda]);
            if ($stmt->fetch()) {
                throw new Exception("La ronda $numero_ronda ya existe para este torneo");
            }
            
            $stmt_ronda = $this->pdo->prepare("
                INSERT INTO rondas (id_torneo, numero_ronda, fecha_ronda, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            $stmt_ronda->execute([$id_torneo, $numero_ronda, $fecha_ronda]);
            $id_ronda = $this->pdo->lastInsertId();
            
            $stmt_ronda->execute([$id_torneo, $numero_ronda, $fecha_ronda]);
            $id_ronda = $this->pdo->lastInsertId();

            // NUEVO: Reiniciar estadísticas de equipos para esta ronda
            $this->reiniciarEstadisticasEquiposRonda($id_torneo, $numero_ronda);

            $stmt = $this->pdo->prepare("SELECT sistema FROM torneos WHERE id_torneo = ?");
            $stmt->execute([$id_torneo]);
            $torneo = $stmt->fetch();
            $sistema = $torneo['sistema'];
            
            if ($sistema == 'round_robin_ponderado') {
                $emparejamientos = $this->generarEmparejamientosRoundRobinPonderado($equipos, $id_torneo, $numero_ronda);
            } else {
                $emparejamientos = $this->generarEmparejamientosRoundRobin($equipos, $numero_ronda);
            }
            
            foreach ($emparejamientos as $emparejamiento) {
                $stmt_emp = $this->pdo->prepare("
                    INSERT INTO emparejamientos (id_ronda, equipo_blancas, equipo_negras, created_at) 
                    VALUES (?, ?, ?, NOW())
                ");
                $stmt_emp->execute([$id_ronda, $emparejamiento['blancas'], $emparejamiento['negras']]);
                $id_emparejamiento = $this->pdo->lastInsertId();
                
                if ($sistema == 'round_robin_ponderado') {
                    $this->generarPartidasPonderadas($id_emparejamiento, $emparejamiento['blancas'], $emparejamiento['negras'], $id_torneo, $numero_ronda);
                } else {
                    $this->generarPartidasEmparejamiento($id_emparejamiento, $emparejamiento['blancas'], $emparejamiento['negras'], $id_torneo);
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
    
    // ========== MÉTODOS PARA ROUND ROBIN PONDERADO MEJORADOS ==========
    private function generarEmparejamientosRoundRobinPonderado($equipos, $id_torneo, $numero_ronda) {
        // Para round robin ponderado, usamos emparejamientos fijos de round robin clásico
        // pero el orden interno de jugadores es por puntuación (como suizo)
        return $this->generarEmparejamientosRoundRobin($equipos, $numero_ronda);
    }
    
    private function generarPartidasPonderadas($id_emparejamiento, $id_equipo_blancas, $id_equipo_negras, $id_torneo, $numero_ronda) {
        $stmt = $this->pdo->prepare("SELECT nro_tableros FROM torneos WHERE id_torneo = ?");
        $stmt->execute([$id_torneo]);
        $torneo = $stmt->fetch();
        $nro_tableros = $torneo['nro_tableros'] ?? 4;
        
        $jugadores_blancas = $this->getJugadoresEquipoOrdenadosPorPuntuacion($id_equipo_blancas, $id_torneo);
        $jugadores_negras = $this->getJugadoresEquipoOrdenadosPorPuntuacion($id_equipo_negras, $id_torneo);
        
        // Verificar enfrentamientos previos para evitar repeticiones
        $enfrentamientos_previos = $this->getEnfrentamientosPrevios($id_equipo_blancas, $id_equipo_negras, $id_torneo, $numero_ronda);
        
        for ($tablero = 1; $tablero <= $nro_tableros; $tablero++) {
            $id_jugador_blancas = isset($jugadores_blancas[$tablero - 1]) ? $jugadores_blancas[$tablero - 1]['id_jugador'] : null;
            $id_jugador_negras = isset($jugadores_negras[$tablero - 1]) ? $jugadores_negras[$tablero - 1]['id_jugador'] : null;
            
            // Verificar si ya se enfrentaron
            if ($id_jugador_blancas && $id_jugador_negras) {
                $ya_se_enfrentaron = false;
                foreach ($enfrentamientos_previos as $enfrentamiento) {
                    if (($enfrentamiento['id_jugador_blancas'] == $id_jugador_blancas && $enfrentamiento['id_jugador_negras'] == $id_jugador_negras) ||
                        ($enfrentamiento['id_jugador_blancas'] == $id_jugador_negras && $enfrentamiento['id_jugador_negras'] == $id_jugador_blancas)) {
                        $ya_se_enfrentaron = true;
                        break;
                    }
                }
                
                if (!$ya_se_enfrentaron) {
                    $stmt_partida = $this->pdo->prepare("
                        INSERT INTO partidas (id_emparejamiento, tablero, id_jugador_blancas, id_jugador_negras, created_at) 
                        VALUES (?, ?, ?, ?, NOW())
                    ");
                    $stmt_partida->execute([$id_emparejamiento, $tablero, $id_jugador_blancas, $id_jugador_negras]);
                }
            }
        }
    }
    
    private function getEnfrentamientosPrevios($id_equipo_blancas, $id_equipo_negras, $id_torneo, $ronda_actual) {
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
    // ========== MÉTODOS PARA ROUND ROBIN CLÁSICO ==========
    private function generarEmparejamientosRoundRobin($equipos, $numero_ronda) {
        $num_equipos = count($equipos);
        $emparejamientos = [];
        
        if ($num_equipos % 2 != 0) {
            $equipos[] = ['id_equipo' => null, 'nombre' => 'BYE'];
            $num_equipos++;
        }
        
        $num_rondas = $num_equipos - 1;
        $equipos_fijos = $equipos;
        
        // Rotación para round robin
        for ($ronda = 1; $ronda <= $num_rondas; $ronda++) {
            $emparejamientos_ronda = [];
            
            for ($i = 0; $i < $num_equipos / 2; $i++) {
                $equipo1 = $equipos_fijos[$i];
                $equipo2 = $equipos_fijos[$num_equipos - 1 - $i];
                
                if ($equipo1['id_equipo'] !== null && $equipo2['id_equipo'] !== null) {
                    // Alternar colores de manera inteligente
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
    
    private function generarPartidasEmparejamiento($id_emparejamiento, $id_equipo_blancas, $id_equipo_negras, $id_torneo) {
        $stmt = $this->pdo->prepare("SELECT nro_tableros FROM torneos WHERE id_torneo = ?");
        $stmt->execute([$id_torneo]);
        $torneo = $stmt->fetch();
        $nro_tableros = $torneo['nro_tableros'] ?? 4;
        
        $jugadores_blancas = $this->getJugadoresEquipoOrdenados($id_equipo_blancas);
        $jugadores_negras = $this->getJugadoresEquipoOrdenados($id_equipo_negras);
        
        for ($tablero = 1; $tablero <= $nro_tableros; $tablero++) {
            $id_jugador_blancas = isset($jugadores_blancas[$tablero - 1]) ? $jugadores_blancas[$tablero - 1]['id_jugador'] : null;
            $id_jugador_negras = isset($jugadores_negras[$tablero - 1]) ? $jugadores_negras[$tablero - 1]['id_jugador'] : null;
            
            if ($id_jugador_blancas && $id_jugador_negras) {
                $stmt_partida = $this->pdo->prepare("
                    INSERT INTO partidas (id_emparejamiento, tablero, id_jugador_blancas, id_jugador_negras, created_at) 
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt_partida->execute([$id_emparejamiento, $tablero, $id_jugador_blancas, $id_jugador_negras]);
            }
        }
    }
    
    private function getJugadoresEquipoOrdenadosPorPuntuacion($id_equipo, $id_torneo) {
        $stmt = $this->pdo->prepare("
        SELECT
        j.id_jugador,
        j.nombre,
        j.apellido,
        j.rating,
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

    private function getJugadoresEquipoOrdenados($id_equipo) {
        $stmt = $this->pdo->prepare("
        SELECT id_jugador, nombre, apellido, rating
        FROM jugadores
        WHERE id_equipo = ? AND activo = 1
        ORDER BY rating DESC
        ");
        $stmt->execute([$id_equipo]);
        return $stmt->fetchAll();
    }

    // ========== NUEVOS MÉTODOS PARA IMPRESIÓN ==========

    public function getTorneoCompleto($id_torneo) {
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

    public function actualizarEstadoTorneo($id_torneo) {
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

    public function getEmparejamientosTorneo($id_torneo) {
        $stmt = $this->pdo->prepare("
        SELECT
        r.numero_ronda,
        r.fecha_ronda,
        em.equipo_blancas,
        em.equipo_negras,
        eb.nombre as equipo_blancas_nombre,
        en.nombre as equipo_negras_nombre,
        p.tablero,
        jb.nombre as jugador_blancas_nombre,
        jb.apellido as jugador_blancas_apellido,
        jn.nombre as jugador_negras_nombre,
        jn.apellido as jugador_negras_apellido,
        p.resultado
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
        public function actualizarRatingJugador($id_jugador, $resultado, $rating_oponente) {
        $K = 32; // Factor K para jugadores principiantes

        $stmt = $this->pdo->prepare("SELECT rating FROM jugadores WHERE id_jugador = ?");
        $stmt->execute([$id_jugador]);
        $jugador = $stmt->fetch();
        $rating_actual = $jugador['rating'];

        // Calcular expectativa
        $expectativa = 1 / (1 + pow(10, ($rating_oponente - $rating_actual) / 400));

        // Calcular nuevo rating
        if ($resultado == '1-0') {
            $puntos = 1;
        } elseif ($resultado == '1/2') {
            $puntos = 0.5;
        } else {
            $puntos = 0;
        }

        $nuevo_rating = $rating_actual + $K * ($puntos - $expectativa);

        // Actualizar en base de datos
        $stmt = $this->pdo->prepare("UPDATE jugadores SET rating = ROUND(?) WHERE id_jugador = ?");
        return $stmt->execute([$nuevo_rating, $id_jugador]);
    }
    private function removerJugadorDeTorneosActivos($id_jugador, $id_equipo) {
    $stmt = $this->pdo->prepare("
        SELECT t.id_torneo 
        FROM torneos t
        JOIN torneo_equipos te ON t.id_torneo = te.id_torneo
        WHERE te.id_equipo = ? 
        AND t.condicion = 'en_progreso'
        AND te.retirado = 0
    ");
    $stmt->execute([$id_equipo]);
    $torneos_activos = $stmt->fetchAll();
    
    foreach ($torneos_activos as $torneo) {
        $stmt_delete = $this->pdo->prepare("DELETE FROM torneo_jugadores WHERE id_torneo = ? AND id_jugador = ?");
        $stmt_delete->execute([$torneo['id_torneo'], $id_jugador]);
        
        // Si el jugador tiene partidas pendientes, marcarlas como perdidas (0-1)
        $this->marcarPartidasComoPerdidas($id_jugador, $torneo['id_torneo']);
    }
}

private function inscribirJugadorEnTorneosEquipo($id_jugador, $id_equipo) {
    $stmt = $this->pdo->prepare("
        SELECT t.id_torneo, t.nro_tableros, 
               COUNT(DISTINCT r.id_ronda) as rondas_generadas
        FROM torneos t
        JOIN torneo_equipos te ON t.id_torneo = te.id_torneo
        LEFT JOIN rondas r ON t.id_torneo = r.id_torneo
        WHERE te.id_equipo = ? 
        AND t.condicion = 'en_progreso'
        AND te.retirado = 0
        GROUP BY t.id_torneo, t.nro_tableros
        HAVING rondas_generadas <= 1  
    ");
    $stmt->execute([$id_equipo]);
    $torneos_inscribir = $stmt->fetchAll();
    
    foreach ($torneos_inscribir as $torneo) {
        // Verificar que no esté ya inscrito
        $stmt_check = $this->pdo->prepare("SELECT * FROM torneo_jugadores WHERE id_torneo = ? AND id_jugador = ?");
        $stmt_check->execute([$torneo['id_torneo'], $id_jugador]);
        
        if (!$stmt_check->fetch()) {
            $stmt_insc = $this->pdo->prepare("INSERT INTO torneo_jugadores (id_torneo, id_jugador, id_equipo) VALUES (?, ?, ?)");
            $stmt_insc->execute([$torneo['id_torneo'], $id_jugador, $id_equipo]);
            
            // Si ya hay una ronda generada, agregar al jugador en los emparejamientos
            if ($torneo['rondas_generadas'] > 0) {
                $this->agregarJugadorAEmparejamientosRonda1($id_jugador, $id_equipo, $torneo['id_torneo'], $torneo['nro_tableros']);
            }
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
        WHERE r.id_torneo = ?
        AND p.resultado = '*'
        AND (p.id_jugador_blancas = ? OR p.id_jugador_negras = ?)
    ");
    $stmt->execute([$id_jugador, $id_jugador, $id_torneo, $id_jugador, $id_jugador]);
}

private function agregarJugadorAEmparejamientosRonda1($id_jugador, $id_equipo, $id_torneo, $nro_tableros) {
    // Obtener información del jugador
    $stmt_jugador = $this->pdo->prepare("SELECT rating FROM jugadores WHERE id_jugador = ?");
    $stmt_jugador->execute([$id_jugador]);
    $jugador_info = $stmt_jugador->fetch();
    
    // Buscar la ronda 1 del torneo
    $stmt_ronda = $this->pdo->prepare("SELECT id_ronda FROM rondas WHERE id_torneo = ? AND numero_ronda = 1");
    $stmt_ronda->execute([$id_torneo]);
    $ronda = $stmt_ronda->fetch();
    
    if (!$ronda) {
        return; // No hay ronda 1 aún
    }
    
    // Buscar emparejamiento del equipo en la ronda 1
    $stmt_emp = $this->pdo->prepare("
        SELECT em.id_emparejamiento, em.equipo_blancas, em.equipo_negras
        FROM emparejamientos em
        WHERE em.id_ronda = ?
        AND (em.equipo_blancas = ? OR em.equipo_negras = ?)
    ");
    $stmt_emp->execute([$ronda['id_ronda'], $id_equipo, $id_equipo]);
    $emparejamiento = $stmt_emp->fetch();
    
    if (!$emparejamiento) {
        return; // El equipo no tiene emparejamiento en ronda 1
    }
    
    // Encontrar un tablero disponible
    $stmt_partidas = $this->pdo->prepare("
        SELECT tablero, id_jugador_blancas, id_jugador_negras
        FROM partidas
        WHERE id_emparejamiento = ?
        ORDER BY tablero
    ");
    $stmt_partidas->execute([$emparejamiento['id_emparejamiento']]);
    $partidas = $stmt_partidas->fetchAll();
    
    // Buscar tablero vacío
    $tablero_disponible = null;
    foreach ($partidas as $partida) {
        if (($emparejamiento['equipo_blancas'] == $id_equipo && $partida['id_jugador_blancas'] === null) ||
            ($emparejamiento['equipo_negras'] == $id_equipo && $partida['id_jugador_negras'] === null)) {
            $tablero_disponible = $partida['tablero'];
            break;
        }
    }
    
    // Si no hay tablero vacío, crear uno nuevo
    if ($tablero_disponible === null) {
        $tablero_disponible = count($partidas) + 1;
        if ($tablero_disponible <= $nro_tableros) {
            // Crear nueva partida
            if ($emparejamiento['equipo_blancas'] == $id_equipo) {
                $stmt_nueva = $this->pdo->prepare("
                    INSERT INTO partidas (id_emparejamiento, tablero, id_jugador_blancas, resultado, created_at) 
                    VALUES (?, ?, ?, '*', NOW())
                ");
                $stmt_nueva->execute([$emparejamiento['id_emparejamiento'], $tablero_disponible, $id_jugador]);
            } else {
                $stmt_nueva = $this->pdo->prepare("
                    INSERT INTO partidas (id_emparejamiento, tablero, id_jugador_negras, resultado, created_at) 
                    VALUES (?, ?, ?, '*', NOW())
                ");
                $stmt_nueva->execute([$emparejamiento['id_emparejamiento'], $tablero_disponible, $id_jugador]);
            }
        }
    } else {
        // Actualizar partida existente
        foreach ($partidas as $partida) {
            if ($partida['tablero'] == $tablero_disponible) {
                if ($emparejamiento['equipo_blancas'] == $id_equipo && $partida['id_jugador_blancas'] === null) {
                    $stmt_update = $this->pdo->prepare("UPDATE partidas SET id_jugador_blancas = ? WHERE id_emparejamiento = ? AND tablero = ?");
                    $stmt_update->execute([$id_jugador, $emparejamiento['id_emparejamiento'], $tablero_disponible]);
                } elseif ($emparejamiento['equipo_negras'] == $id_equipo && $partida['id_jugador_negras'] === null) {
                    $stmt_update = $this->pdo->prepare("UPDATE partidas SET id_jugador_negras = ? WHERE id_emparejamiento = ? AND tablero = ?");
                    $stmt_update->execute([$id_jugador, $emparejamiento['id_emparejamiento'], $tablero_disponible]);
                }
                break;
            }
        }
    }
}

private function reiniciarEstadisticasEquiposRonda($id_torneo, $numero_ronda) {
    // Solo reiniciar si es la primera ronda
    if ($numero_ronda == 1) {
        $stmt = $this->pdo->prepare("DELETE FROM estadisticas_equipo WHERE id_torneo = ?");
        $stmt->execute([$id_torneo]);
        
        $stmt = $this->pdo->prepare("DELETE FROM estadisticas_jugador WHERE id_torneo = ?");
        $stmt->execute([$id_torneo]);
    }
}

}

// Inicializar sistema
$sistema = new XhessSystem();

// Procesar acciones
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'login':
            if ($sistema->login($_POST['usuario'], $_POST['contrasena'])) {
                header("Location: index.php");
                exit;
            } else {
                $error = "Usuario o contraseña incorrectos";
            }
            break;
            
        case 'registro':
            try {
                if ($sistema->registrarUsuario($_POST)) {
                    $success = "Usuario registrado exitosamente";
                    header("Location: index.php");
                    exit;
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            break;
            
        case 'unirse_equipo':
            if ($sistema->isLoggedIn() && $sistema->isJugador()) {
                try {
                    if ($sistema->unirseAEquipo($_SESSION['jugador_id'], $_POST['id_equipo'])) {
                        $success = "Te has unido al equipo exitosamente";
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
            break;
            
        case 'salirse_equipo':
            if ($sistema->isLoggedIn() && $sistema->isJugador()) {
                try {
                    if ($sistema->salirseDeEquipo($_SESSION['jugador_id'])) {
                        $success = "Te has salido del equipo exitosamente";
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
            break;
            
        case 'crear_equipo':
            if ($sistema->isLoggedIn()) {
                try {
                    if ($sistema->crearEquipo($_POST)) {
                        $success = "Equipo creado exitosamente";
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
            break;
            
        case 'admin_asignar_jugador':
            if ($sistema->isLoggedIn() && $sistema->isAdmin()) {
                try {
                    if ($sistema->adminAsignarJugadorAEquipo($_POST['id_jugador'], $_POST['id_equipo'])) {
                        $success = "Jugador asignado al equipo exitosamente";
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
            break;
            
        case 'admin_quitar_jugador':
            if ($sistema->isLoggedIn() && $sistema->isAdmin()) {
                try {
                    if ($sistema->adminQuitarJugadorDeEquipo($_POST['id_jugador'])) {
                        $success = "Jugador quitado del equipo exitosamente";
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
            break;
            
        case 'eliminar_equipo':
            if ($sistema->isLoggedIn() && $sistema->isAdmin()) {
                try {
                    if ($sistema->eliminarEquipo($_POST['id_equipo'])) {
                        $success = "Equipo eliminado exitosamente";
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
            break;
            
        case 'crear_torneo':
            if ($sistema->isLoggedIn()) {
                $_POST['id_cuenta'] = $_SESSION['user_id'];
                if ($sistema->crearTorneo($_POST)) {
                    $success = "Torneo creado exitosamente";
                } else {
                    $error = "Error al crear torneo";
                }
            }
            break;
            
        case 'agregar_equipo':
            if ($sistema->isLoggedIn()) {
                if ($sistema->agregarEquipoATorneo($_POST['id_torneo'], $_POST['id_equipo'])) {
                    $success = "Equipo agregado al torneo";
                } else {
                    $error = "Error al agregar equipo";
                }
            }
            break;

        case 'agregar_equipos_multiples':
            if ($sistema->isLoggedIn()) {
                if ($sistema->agregarEquiposMultiplesATorneo($_POST['id_torneo'], $_POST['id_equipos'])) {
                    $success = "Equipos agregados al torneo exitosamente";
                } else {
                    $error = "Error al agregar equipos";
                }
            }
            break;
            
        case 'retirar_equipo':
            if ($sistema->isLoggedIn()) {
                if ($sistema->retirarEquipoDeTorneo($_POST['id_torneo'], $_POST['id_equipo'])) {
                    $success = "Equipo retirado del torneo";
                } else {
                    $error = "Error al retirar equipo";
                }
            }
            break;
            
        case 'generar_ronda':
            if ($sistema->isLoggedIn()) {
                try {
                    if ($sistema->generarRonda($_POST['id_torneo'], $_POST['numero_ronda'], $_POST['fecha_ronda'])) {
                        $success = "Ronda generada exitosamente";
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
            break;
            
        case 'actualizar_resultado':
            if ($sistema->isLoggedIn()) {
                if ($sistema->actualizarResultadoPartida($_POST['id_partida'], $_POST['resultado'])) {
                    $success = "Resultado actualizado exitosamente";
                } else {
                    $error = "Error al actualizar resultado";
                }
            }
            break;

        case 'actualizar_multiples_resultados':
            if ($sistema->isLoggedIn()) {
                try {
                    $resultados = $_POST['resultados'] ?? [];
                    if ($sistema->actualizarMultiplesResultados($resultados)) {
                        $success = "Resultados actualizados exitosamente";
                    } else {
                        $error = "Error al actualizar resultados";
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
            break;
            
        case 'eliminar_torneo':
            if ($sistema->isLoggedIn() && $sistema->isAdmin()) {
                try {
                    if ($sistema->eliminarTorneo($_POST['id_torneo'])) {
                        $success = "Torneo eliminado exitosamente";
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
            break;
            
        case 'eliminar_cuenta':
            if ($sistema->isLoggedIn() && $sistema->isAdmin()) {
                try {
                    if ($sistema->eliminarCuenta($_POST['id_cuenta'])) {
                        $success = "Cuenta eliminada exitosamente";
                    }
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
            break;
            
        case 'logout':
            $sistema->logout();
            break;
    }
}
?>
