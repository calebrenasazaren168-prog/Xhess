<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jugadores del Torneo</title>
</head>
<body>
    <h1>Jugadores del Torneo</h1>
    
    <?php
    // Incluir archivo de conexión
    include('conexion.php');
    
    if (isset($_GET['id_torneo']) && !empty($_GET['id_torneo'])) {
        $id_torneo = $_GET['id_torneo'];
        
        // Consulta para obtener información del torneo
        $sql_torneo = "SELECT nombre FROM torneo WHERE id_torneo = ?";
        $stmt_torneo = $conexion->prepare($sql_torneo);
        $stmt_torneo->bind_param("i", $id_torneo);
        $stmt_torneo->execute();
        $result_torneo = $stmt_torneo->get_result();
        
        if ($result_torneo && $result_torneo->num_rows > 0) {
            $torneo = $result_torneo->fetch_assoc();
            echo "<h2>Torneo: " . $torneo['nombre'] . "</h2>";
        } else {
            echo "<p>No se encontró el torneo.</p>";
        }
        $stmt_torneo->close();
        
        // Consulta para obtener los jugadores del torneo
        $sql_jugadores = "SELECT DISTINCT j.id_jugador, j.nombre, j.apellido, j.elo, j.ciudad_club, e.nombre as equipo
                         FROM jugadores j
                         JOIN equipos e ON j.id_equipo = e.id_equipo
                         JOIN emparejamientos em ON j.id_jugador = em.id_jugador
                         JOIN partida p ON em.id_partida = p.id_partida
                         JOIN rondas r ON p.id_ronda = r.id_ronda
                         WHERE r.id_torneo = ?
                         ORDER BY j.apellido, j.nombre";
        
        $stmt_jugadores = $conexion->prepare($sql_jugadores);
        $stmt_jugadores->bind_param("i", $id_torneo);
        $stmt_jugadores->execute();
        $result_jugadores = $stmt_jugadores->get_result();
        
        if ($result_jugadores && $result_jugadores->num_rows > 0) {
            echo "<table border='1'>";
            echo "<tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>ELO</th>
                    <th>Equipo</th>
                    <th>Ciudad/Club</th>
                  </tr>";
            
            while($jugador = $result_jugadores->fetch_assoc()) {
                echo "<tr>
                        <td>" . $jugador['id_jugador'] . "</td>
                        <td>" . $jugador['nombre'] . "</td>
                        <td>" . $jugador['apellido'] . "</td>
                        <td>" . $jugador['elo'] . "</td>
                        <td>" . $jugador['equipo'] . "</td>
                        <td>" . $jugador['ciudad_club'] . "</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No se encontraron jugadores para este torneo.</p>";
        }
        
        $stmt_jugadores->close();
        
        // Botón para ver emparejamientos por ronda
        echo "<br>";
        echo "<a href='emparejamientos_ronda.php?id_torneo=" . $id_torneo . "&ronda=1'>";
        echo "<button type='button'>Ver Emparejamientos por Ronda</button>";
        echo "</a>";
        
        echo "<br><br><a href='index.php'>Volver a la lista de torneos</a>";
        
    } else {
        echo "<p>No se ha seleccionado ningún torneo.</p>";
        echo "<a href='index.php'>Volver a la lista de torneos</a>";
    }
    
    // Cerrar conexión
    $conexion->close();
    ?>
</body>
</html>