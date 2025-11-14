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
        
        // Consulta para obtener los enfrentamientos entre equipos con resultados
        $sql_enfrentamientos = "SELECT DISTINCT
                                eb.nombre as equipo_blancas,
                                en.nombre as equipo_negras,
                                em_b.puntaje as puntaje_blancas,
                                em_n.puntaje as puntaje_negras
                            FROM partida p
                            JOIN rondas r ON p.id_ronda = r.id_ronda
                            JOIN emparejamientos em_b ON p.id_partida = em_b.id_partida AND em_b.color = 'blancas'
                            JOIN emparejamientos em_n ON p.id_partida = em_n.id_partida AND em_n.color = 'negras'
                            JOIN jugadores jb ON em_b.id_jugador = jb.id_jugador
                            JOIN jugadores jn ON em_n.id_jugador = jn.id_jugador
                            JOIN equipos eb ON jb.id_equipo = eb.id_equipo
                            JOIN equipos en ON jn.id_equipo = en.id_equipo
                            WHERE r.id_torneo = ? AND r.numero_ronda = ?
                            ORDER BY eb.nombre";
        
        $stmt_enfrentamientos = $conexion->prepare($sql_enfrentamientos);
        $stmt_enfrentamientos->bind_param("ii", $id_torneo, $ronda_actual);
        $stmt_enfrentamientos->execute();
        $result_enfrentamientos = $stmt_enfrentamientos->get_result();
        
        if ($result_enfrentamientos && $result_enfrentamientos->num_rows > 0) {
            echo "<table border='1'>";
            echo "<tr>
                    <th>Equipo Blancas</th>
                    <th>Resultado</th>
                    <th>Equipo Negras</th>
                  </tr>";
            
            while($enfrentamiento = $result_enfrentamientos->fetch_assoc()) {
                $resultado = $enfrentamiento['puntaje_blancas'] . "-" . $enfrentamiento['puntaje_negras'];
                
                echo "<tr>
                        <td>" . $enfrentamiento['equipo_blancas'] . "</td>
                        <td><strong>" . $resultado . "</strong></td>
                        <td>" . $enfrentamiento['equipo_negras'] . "</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No se encontraron enfrentamientos entre equipos para la ronda $ronda_actual.</p>";
        }
        
        $stmt_enfrentamientos->close();
        
        echo "<br><a href='jugadores_torneo.php?id_torneo=$id_torneo'>Volver a jugadores del torneo</a>";
        echo "<br><a href='torneos_escuela.php'>Volver a la lista de torneos</a>";
        
    } else {
        echo "<p>No se ha seleccionado ningún torneo.</p>";
        echo "<a href='torneos_escuela.php'>Volver a la lista de torneos</a>";
    }
    
    // Cerrar conexión
    $conexion->close();
    ?>
    <a href="emparejamientos_ronda.php?id_torneo=<?php echo $id_torneo; ?>&ronda=<?php echo $ronda_actual; ?>">Refrescar página</a>
</body>
</html>