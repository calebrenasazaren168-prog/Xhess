<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emparejamientos por Ronda</title>
</head>
<body>
    <h1>Emparejamientos por Ronda</h1>
    
    <?php
    // Incluir archivo de conexión
    include('conexion.php');
    
    if (isset($_GET['id_torneo']) && !empty($_GET['id_torneo'])) {
        $id_torneo = $_GET['id_torneo'];
        $ronda_actual = isset($_GET['ronda']) ? intval($_GET['ronda']) : 1;
        
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
        for ($i = 1; $i <= $total_rondas; $i++) {
            if ($i == $ronda_actual) {
                echo "<button disabled>Ronda $i</button> ";
            } else {
                echo "<a href='emparejamientos_ronda.php?id_torneo=$id_torneo&ronda=$i'>";
                echo "<button type='button'>Ronda $i</button>";
                echo "</a> ";
            }
        }
        echo "</div>";
        
        // Consulta para obtener los emparejamientos de la ronda actual
        $sql_emparejamientos = "SELECT 
                                jb.id_jugador as id_blancas,
                                jb.nombre as nombre_blancas,
                                jb.apellido as apellido_blancas,
                                jn.id_jugador as id_negras,
                                jn.nombre as nombre_negras,
                                jn.apellido as apellido_negras,
                                p.resultado_partida,
                                em_b.puntaje as puntaje_blancas,
                                em_n.puntaje as puntaje_negras,
                                p.mesa
                            FROM partida p
                            JOIN rondas r ON p.id_ronda = r.id_ronda
                            JOIN emparejamientos em_b ON p.id_partida = em_b.id_partida AND em_b.color = 'blancas'
                            JOIN emparejamientos em_n ON p.id_partida = em_n.id_partida AND em_n.color = 'negras'
                            JOIN jugadores jb ON em_b.id_jugador = jb.id_jugador
                            JOIN jugadores jn ON em_n.id_jugador = jn.id_jugador
                            WHERE r.id_torneo = ? AND r.numero_ronda = ?
                            ORDER BY p.mesa";
        
        $stmt_emparejamientos = $conexion->prepare($sql_emparejamientos);
        $stmt_emparejamientos->bind_param("ii", $id_torneo, $ronda_actual);
        $stmt_emparejamientos->execute();
        $result_emparejamientos = $stmt_emparejamientos->get_result();
        
        if ($result_emparejamientos && $result_emparejamientos->num_rows > 0) {
            echo "<table border='1'>";
            echo "<tr>
                    <th>Mesa</th>
                    <th>Blancas (ID)</th>
                    <th>Negras (ID)</th>
                    <th>Resultado</th>
                  </tr>";
            
            while($emparejamiento = $result_emparejamientos->fetch_assoc()) {
                $resultado = $emparejamiento['puntaje_blancas'] . "-" . $emparejamiento['puntaje_negras'];
                
                echo "<tr>
                        <td>" . $emparejamiento['mesa'] . "</td>
                        <td>" . $emparejamiento['nombre_blancas'] . " " . $emparejamiento['apellido_blancas'] . " (" . $emparejamiento['id_blancas'] . ")</td>
                        <td>" . $resultado . "</td>
                        <td>" . $emparejamiento['nombre_negras'] . " " . $emparejamiento['apellido_negras'] . " (" . $emparejamiento['id_negras'] . ")</td>                        
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No se encontraron emparejamientos para la ronda $ronda_actual.</p>";
        }
        
        $stmt_emparejamientos->close();
        
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

