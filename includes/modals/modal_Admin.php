<?php if ($sistema->isAdmin()): ?>
        <div class="modal fade" id="admin" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Panel de Administración</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs" id="adminTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#torneos-tab" type="button">Todos los Torneos</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#usuarios-tab" type="button">Usuarios</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="torneos-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Creador</th>
                                                <th>Fecha Inicio</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $todosTorneos = $sistema->getTodosTorneos();
                                            if (empty($todosTorneos)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No hay torneos en el sistema.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($todosTorneos as $torneo): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($torneo['nombre']) ?></td>
                                                        <td><?= htmlspecialchars($torneo['creador'] ?? 'N/A') ?></td>
                                                        <td><?= $torneo['fecha_inicio'] ?></td>
                                                        <td>
                                                            <span class="badge bg-<?= $torneo['condicion'] == 'en_progreso' ? 'success' : ($torneo['condicion'] == 'finalizado' ? 'secondary' : 'warning') ?>">
                                                                <?= $torneo['condicion'] ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-outline-danger" 
                                                                    onclick="eliminarTorneo(<?= $torneo['id_torneo'] ?>, '<?= htmlspecialchars($torneo['nombre']) ?>')">
                                                                <i class="fas fa-trash"></i> Eliminar
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="tab-pane fade" id="usuarios-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Usuario</th>
                                                <th>Correo</th>
                                                <th>Rol</th>
                                                <th>Fecha Registro</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $todasCuentas = $sistema->getTodasCuentas();
                                            if (empty($todasCuentas)): ?>
                                                <tr>
                                                    <td colspan="5" class="text-center">No hay usuarios registrados.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($todasCuentas as $cuenta): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($cuenta['usuario']) ?></td>
                                                        <td><?= htmlspecialchars($cuenta['correo']) ?></td>
                                                        <td>
                                                            <span class="badge bg-<?= $cuenta['id_rol'] == 1 ? 'danger' : ($cuenta['id_rol'] == 2 ? 'warning' : 'info') ?>">
                                                                <?= $cuenta['rol_nombre'] ?>
                                                            </span>
                                                        </td>
                                                        <td><?= $cuenta['fecha_creacion'] ?></td>
                                                        <td>
                                                            <?php if ($cuenta['id_cuenta'] != 1): // No permitir eliminar al admin principal ?>
                                                                <button class="btn btn-sm btn-outline-danger" 
                                                                        onclick="eliminarCuenta(<?= $cuenta['id_cuenta'] ?>, '<?= htmlspecialchars($cuenta['usuario']) ?>')">
                                                                    <i class="fas fa-trash"></i> Eliminar
                                                                </button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>