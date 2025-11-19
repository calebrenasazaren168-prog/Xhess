<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Torneos por Escuela</title>
</head>
<body>
    <header style="display: flex; justify-content: space-between; align-items: center;">
        <h3>Xhess</h3>
       <a href="login2.php"> <button>Log in</button></a>
   </header>
    <h1>Consulta de Torneos por Escuela</h1>
    
    <form method="POST" action="">
        <label for="escuela">Seleccione una escuela:</label>
        <select name="escuela" id="escuela" onchange="this.form.submit()">
            <option value="" disabled selected>Seleccione su escuela</option>
            <?php
            // Incluir archivo de conexión
            include('conexion.php');
            
            // Consulta para obtener las escuelas únicas (lugares)
            $sql_escuelas = "SELECT DISTINCT lugar FROM torneo ORDER BY lugar";
            $result_escuelas = $conexion->query($sql_escuelas);
            
            if ($result_escuelas && $result_escuelas->num_rows > 0) {
                while($row = $result_escuelas->fetch_assoc()) {
                    $selected = "";
                    if (isset($_POST['escuela']) && $_POST['escuela'] == $row['lugar']) {
                        $selected = "selected";
                    }
                    echo "<option value='" . $row['lugar'] . "' $selected>" . $row['lugar'] . "</option>";
                }
            }
            ?>
        </select>
    </form>

    <?php
    // Mostrar tabla de torneos si se seleccionó una escuela
    if (isset($_POST['escuela']) && !empty($_POST['escuela'])) {
        $escuela_seleccionada = $_POST['escuela'];
        
        echo "<h2>Torneos de la escuela: $escuela_seleccionada</h2>";
        
        // Consulta para obtener los torneos de la escuela seleccionada
        $sql_torneos = "SELECT id_torneo, nombre, estado FROM torneo WHERE lugar = ? ORDER BY fecha_inicio DESC";
        $stmt = $conexion->prepare($sql_torneos);
        $stmt->bind_param("s", $escuela_seleccionada);
        $stmt->execute();
        $result_torneos = $stmt->get_result();
        
        if ($result_torneos && $result_torneos->num_rows > 0) {
            echo "<table border='1'>";
            echo "<tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Estado</th>                    
                  </tr>";
            
            while($torneo = $result_torneos->fetch_assoc()) {
                $estado = $torneo['estado'] ? "Activo" : "Finalizado";
                
                echo "<tr>
                        <td>" . $torneo['id_torneo'] . "</td>
                        <td>" . $torneo['nombre'] . "<form method='GET' action='jugadores_torneo.php' style='display:inline;'>
                                <input type='hidden' name='id_torneo' value='" . $torneo['id_torneo'] . "'>
                                <input type='submit' value='Ver Jugadores'>
                            </form></td>
                        <td>" . $estado . "</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No se encontraron torneos para esta escuela.</p>";
        }
        
        $stmt->close();
    }
    
    // Cerrar conexión
    $conexion->close();
    ?>
</body>
</html>