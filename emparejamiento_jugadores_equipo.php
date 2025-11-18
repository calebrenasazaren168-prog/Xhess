<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emparejamientos por Equipo</title>
</head>
<body>
    <h1>Emparejamientos por Equipo</h1>
    
    <?php
    // Incluir archivo de conexión
    include('conexion.php');
    
    if (isset($_GET['id_torneo']) && !empty($_GET['id_torneo'])) {
        $id_torneo= $_GET['id_torneo'];
        $ronda_actual = isset($_GET['ronda']) ? intval($_GET['ronda']) : 1;
        $equipo_seleccionado = isset($_GET['equipo']) ? $_GET['equipo'] : '';
        
        // Consulta para obtener información del torneo
        $sql_torneo = "SELECT nombre FROM torneo WHERE id_torneo = ?";
        $stmt_torneo = $conexion->prepare($sql_torneo);
        $stmt_torneo->bind_param("i", $id_torneo);
        $stmt_torneo->execute();
        $result_torneo = $stmt_torneo->get_result();
        
        if ($result_torneo && $result_torneo->num_rows > 0) {
            $torneo = $result_torneo->fetch_assoc();
            echo "<h2>Torneo: " . $torneo['nombre'] . " - Ronda $ronda_actual</h2>";
        }
        $stmt_torneo->close();
        
        // Consulta para obtener el número total de rondas del torneo
        $sql_total_rondas = "SELECT MAX(numero_ronda) as total_rondas FROM rondas WHERE id_torneo = ?";
        $stmt_total = $conexion->prepare($sql_total_rondas);
        $stmt_total->bind_param("i", $id_torneo);
        $stmt_total->execute();
        $result_total = $stmt_total->get_result();
        $total_rondas = 1;
        if ($result_total && $result_total->num_rows > 0) {
            $row = $result_total->fetch_assoc();
            $total_rondas = $row['total_rondas'] ?: 1;
        }
        $stmt_total->close();
        
        // Botones para cambiar de ronda
        echo "<div style='margin-bottom: 20px;'>";
        echo "<strong>Seleccionar Ronda:</strong> ";
        for ($i = 1; $i <= $total_rondas; $i++) {
            if ($i == $ronda_actual) {
                echo "<button disabled>Ronda $i</button> ";
            } else {
                $url = "equipo_emparejamientos.php?id_torneo=$id_torneo&ronda=$i";
                if ($equipo_seleccionado) {
                    $url .= "&equipo=" . urlencode($equipo_seleccionado);
                }
                echo "<a href='$url'>";
                echo "<button type='button'>Ronda $i</button>";
                echo "</a> ";
            }
        }
        echo "</div>";
        
        // Consulta para obtener todos los equipos del torneo
        $sql_equipos = "SELECT DISTINCT e.nombre 
                       FROM equipos e
                       JOIN jugadores j ON e.id_equipo = j.id_equipo
                       JOIN emparejamientos em ON j.id_jugador = em.id_jugador
                       JOIN partida p ON em.id_partida = p.id_partida
                       JOIN rondas r ON p.id_ronda = r.id_ronda
                       WHERE r.id_torneo = ?
                       ORDER BY e.nombre";
        
        $stmt_equipos = $conexion->prepare($sql_equipos);
        $stmt_equipos->bind_param("i", $id_torneo);
        $stmt_equipos->execute();
        $result_equipos = $stmt_equipos->get_result();
        
        // Mostrar lista de equipos
        echo "<div style='margin-bottom: 20px;'>";
        echo "<h3>Seleccionar Equipo:</h3>";
        if ($result_equipos && $result_equipos->num_rows > 0) {
            while($equipo = $result_equipos->fetch_assoc()) {
                $nombre_equipo = $equipo['nombre'];
                if ($nombre_equipo == $equipo_seleccionado) {
                    echo "<button disabled style='margin: 5px;'>$nombre_equipo</button> ";
                } else {
                    echo "<a href='equipo_emparejamientos.php?id_torneo=$id_torneo&ronda=$ronda_actual&equipo=" . urlencode($nombre_equipo) . "'>";
                    echo "<button type='button' style='margin: 5px;'>$nombre_equipo</button>";
                    echo "</a> ";
                }
            }
        }
        echo "</div>";
        
        // Mostrar emparejamientos del equipo seleccionado
        if ($equipo_seleccionado) {
            echo "<h3>Emparejamientos de: $equipo_seleccionado</h3>";
            
            // Consulta para obtener los emparejamientos del equipo seleccionado
            $sql_emparejamientos = "SELECT 
                                    CASE 
                                        WHEN eb.nombre = ? THEN en.nombre
                                        ELSE eb.nombre
                                    END as equipo_rival,
                                    CASE 
                                        WHEN eb.nombre = ? THEN em_b.puntaje
                                        ELSE em_n.puntaje
                                    END as puntaje_equipo,
                                    CASE 
                                        WHEN eb.nombre = ? THEN em_n.puntaje
                                        ELSE em_b.puntaje
                                    END as puntaje_rival,
                                    CASE 
                                        WHEN eb.nombre = ? THEN 'blancas'
                                        ELSE 'negras'
                                    END as color_equipo
                                FROM partida p
                                JOIN rondas r ON p.id_ronda = r.id_ronda
                                JOIN emparejamientos em_b ON p.id_partida = em_b.id_partida AND em_b.color = 'blancas'
                                JOIN emparejamientos em_n ON p.id_partida = em_n.id_partida AND em_n.color = 'negras'
                                JOIN jugadores jb ON em_b.id_jugador = jb.id_jugador
                                JOIN jugadores jn ON em_n.id_jugador = jn.id_jugador
                                JOIN equipos eb ON jb.id_equipo = eb.id_equipo
                                JOIN equipos en ON jn.id_equipo = en.id_equipo
                                WHERE r.id_torneo = ? AND r.numero_ronda = ?
                                AND (eb.nombre = ? OR en.nombre = ?)";
            
            $stmt_emparejamientos = $conexion->prepare($sql_emparejamientos);
            $stmt_emparejamientos->bind_param("ssssiiiss", 
                $equipo_seleccionado, $equipo_seleccionado, $equipo_seleccionado, 
                $equipo_seleccionado, $id_torneo, $ronda_actual, 
                $equipo_seleccionado, $equipo_seleccionado);
            $stmt_emparejamientos->execute();
            $result_emparejamientos = $stmt_emparejamientos->get_result();
            
            if ($result_emparejamientos && $result_emparejamientos->num_rows > 0) {
                echo "<table border='1'>";
                echo "<tr>
                        <th>Equipo</th>
                        <th>Color</th>
                        <th>Resultado</th>
                        <th>Equipo Rival</th>
                        <th>Acción</th>
                      </tr>";
                
                while($emparejamiento = $result_emparejamientos->fetch_assoc()) {
                    $resultado = $emparejamiento['puntaje_equipo'] . "-" . $emparejamiento['puntaje_rival'];
                    
                    echo "<tr>
                            <td><strong>$equipo_seleccionado</strong></td>
                            <td>" . $emparejamiento['color_equipo'] . "</td>
                            <td><strong>" . $resultado . "</strong></td>
                            <td>" . $emparejamiento['equipo_rival'] . "</td>
                            <td>
                                <a href='equipo_emparejamientos.php?id_torneo=$id_torneo&ronda=$ronda_actual&equipo=" . urlencode($emparejamiento['equipo_rival']) . "'>
                                    <button type='button'>Ver " . $emparejamiento['equipo_rival'] . "</button>
                                </a>
                            </td>
                          </tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No se encontraron emparejamientos para el equipo $equipo_seleccionado en la ronda $ronda_actual.</p>";
            }
            
            $stmt_emparejamientos->close();
        }
        
        echo "<br><a href='emparejamientos_ronda.php?id_torneo=$id_torneo'>Volver a emparejamientos generales</a>";
        echo "<br><a href='jugadores_torneo.php?id_torneo=$id_torneo'>Volver a jugadores del torneo</a>";
        echo "<br><a href='index.php'>Volver a la lista de torneos</a>";
        
    } else {
        echo "<p>No se ha seleccionado ningún torneo.</p>";
        echo "<a href='index.php'>Volver a la lista de torneos</a>";
    }
    
    // Cerrar conexión
    $conexion->close();
    ?>
</body>
</html>