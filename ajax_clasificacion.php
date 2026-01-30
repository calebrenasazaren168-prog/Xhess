<?php
require_once 'sistema_xhess.php';

$sistema = new XhessSystem();

// Headers para prevenir cache
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (isset($_GET['id_torneo'])) {
    $id_torneo = intval($_GET['id_torneo']);

    $clasificacionEquipos = $sistema->getClasificacionEquipos($id_torneo);
    $clasificacionJugadores = $sistema->getClasificacionJugadores($id_torneo);
    ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
    <h6>Clasificación de Equipos</h6>
    </div>

    <div id="seccion-equipos">
    <?php if (empty($clasificacionEquipos)): ?>
    <p class="text-muted">No hay datos de clasificación disponibles.</p>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table table-sm table-striped">
    <thead>
    <tr>
    <th>#</th>
    <th>Equipo</th>
    <th>PJ</th>
    <th>V</th>
    <th>E</th>
    <th>D</th>
    <th>Pts Match</th>
    <th>Pts Tab</th>
    </tr>
    </thead>
    <tbody>
    <?php $posicion = 1; ?>
    <?php foreach ($clasificacionEquipos as $equipo): ?>
    <tr>
    <td><strong><?= $posicion++ ?></strong></td>
    <td><?= htmlspecialchars($equipo['equipo_nombre']) ?></td>
    <td><?= $equipo['partidas_jugadas'] ?></td>
    <td><?= $equipo['victorias'] ?></td>
    <td><?= $equipo['empates'] ?></td>
    <td><?= $equipo['derrotas'] ?></td>
    <td><strong><?= number_format($equipo['puntos_match'], 1) ?></strong></td>
    <td><?= number_format($equipo['puntos_tableros'], 1) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    <?php endif; ?>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
    <h6>Clasificación de Jugadores</h6>
    </div>

    <div id="seccion-jugadores">
    <?php if (empty($clasificacionJugadores)): ?>
    <p class="text-muted">No hay datos de jugadores disponibles.</p>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table table-sm table-striped">
    <thead>
    <tr>
    <th>#</th>
    <th>Jugador</th>
    <th>Equipo</th>
    <th>Rating</th>
    <th>PJ</th>
    <th>V</th>
    <th>E</th>
    <th>D</th>
    <th>Pts</th>
    </tr>
    </thead>
    <tbody>
    <?php $posicion = 1; ?>
    <?php foreach ($clasificacionJugadores as $jugador): ?>
    <tr>
    <td><strong><?= $posicion++ ?></strong></td>
    <td><?= htmlspecialchars($jugador['nombre_jugador']) ?></td>
    <td><?= htmlspecialchars($jugador['equipo_nombre']) ?></td>
    <td><?= $jugador['rating'] ?></td>
    <td><?= $jugador['partidas_jugadas'] ?></td>
    <td><?= $jugador['victorias'] ?></td>
    <td><?= $jugador['empates'] ?></td>
    <td><?= $jugador['derrotas'] ?></td>
    <td><strong><?= number_format($jugador['puntos'], 1) ?></strong></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
    <?php endif; ?>
    </div>
    <?php
} else {
    echo '<p class="text-danger">Error: No se especificó el torneo</p>';
}
?>