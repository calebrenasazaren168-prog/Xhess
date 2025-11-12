<?php
require 'conexion.php';

$perPage = 25;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page']>0 ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

$escuela=
// obtener total registros
$totalResult = $conexion->query("SELECT COUNT(*) AS total FROM torneo where ");
$totalRow = $totalResult->fetch_assoc();
$total = intval($totalRow['total']);
$totalPages = max(1, ceil($total / $perPage));

// obtener 25 registros ordenados alfabéticamente
$stmt = $conexion->prepare("SELECT id_torneo, nombre, estado FROM torneo ORDER BY id_torneo ASC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $perPage, $offset);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xhess</title>
</head>
<body>
    <header style="display: flex; justify-content: space-between; align-items: center">
        <h2>Xhess</h2>
        <a><button>Log in</button></a>
    </header>
    <h2>SELECCIONA TU ESCUELA</h2>
    <select id="escuela" onchange="mostrarTabla()">
        <option value="" disabled selected>Selecciona tu escuela</option>
        <option value="cbtis168">CBTIS 168</option>
    </select>
     <?php if ($total > 0): ?>
    <table>
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>estado</th></tr>
        </thead>
        <tbody>
            <?php while ($row = $res->fetch_assoc()): ?>
            <tr>
            <td><?php echo htmlspecialchars($row['id_torneo']); ?></td>
            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
            <td><?php echo htmlspecialchars($row['estado']); ?></td>            
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No hay vendedores registrados.</p>
    <?php endif; ?>
</body>
</html>