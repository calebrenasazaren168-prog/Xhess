<div class="printable-section">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6>Emparejamientos del Torneo</h6>
    </div>
    
    <?php
    $emparejamientosTorneo = $sistema->getEmparejamientosTorneo($torneo['id_torneo']);
    if (empty($emparejamientosTorneo)): ?>
        <p class="text-muted">No hay emparejamientos para este torneo.</p>
    <?php else: ?>
        <?php
        // Agrupamos todas las partidas por número de ronda
        $emparejamientosPorRonda = [];
        foreach ($emparejamientosTorneo as $emp) {
            $ronda = $emp['numero_ronda'];
            $emparejamientosPorRonda[$ronda][] = $emp;
        }

        if (empty($emparejamientosPorRonda)) {
            ?>
            <p class="text-muted">No hay emparejamientos registrados para este torneo todavía.</p>
            <?php
        } else {
            foreach ($emparejamientosPorRonda as $numero_ronda => $partidasDeEstaRonda) {
                $fecha_ronda = date('d/m/Y', strtotime($partidasDeEstaRonda[0]['fecha_ronda']));
                ?>
                <h6 class="mt-4">Ronda <?= $numero_ronda ?> - <?= $fecha_ronda ?></h6>
                
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Tablero</th>
                            <th>Blancas</th>
                            <th>Negras</th>
                            <th>Resultado</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($partidasDeEstaRonda as $emparejamiento): ?>
                        <tr>
                            <td><?= htmlspecialchars($emparejamiento['tablero']) ?></td>
                            <td>
                                <?= htmlspecialchars($emparejamiento['jugador_blancas_nombre'] . ' ' . $emparejamiento['jugador_blancas_apellido']) ?>
                                <br><small class="text-muted"><?= htmlspecialchars($emparejamiento['equipo_blancas_nombre'] ?? '—') ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($emparejamiento['jugador_negras_nombre'] . ' ' . $emparejamiento['jugador_negras_apellido']) ?>
                                <br><small class="text-muted"><?= htmlspecialchars($emparejamiento['equipo_negras_nombre'] ?? '—') ?></small>
                            </td>
                            <td><?= htmlspecialchars($emparejamiento['resultado'] ?: '*') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
            }
        }
        ?>
    <?php endif; ?>
</div>