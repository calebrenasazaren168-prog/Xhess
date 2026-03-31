<?php
require_once 'sistema_xhess.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XHESS - Sistema Completo de Torneos de Ajedrez</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="img/chess-knight.svg" type="image/svg+xml">   
</head>
<body>
    <div class="main-content">
        <!-- Navbar -->
        <?php include 'includes/navbar.php'; ?>

        <!-- Inicio -->
        <?php include 'includes/inicio.php'; ?>

        <!-- Torneos Públicos -->
        <section class="container py-5">
            <h2 class="text-center mb-5"><i class="fas fa-trophy"></i> Torneos Activos</h2>
            <div class="row">
                <?php 
                $torneos = $sistema->getTorneosPublicos();
                if (empty($torneos)): ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">No hay torneos activos en este momento.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($torneos as $torneo): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card card-hover h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($torneo['nombre']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($torneo['descripcion'] ?: 'Sin descripción') ?></p>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> 
                                            <?= date('d/m/Y', strtotime($torneo['fecha_inicio'])) ?> - 
                                            <?= date('d/m/Y', strtotime($torneo['fecha_fin'])) ?>
                                        </small>
                                    </p>
                                    <p class="card-text">
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt"></i> 
                                            <?= htmlspecialchars($torneo['ubicacion'] ?: 'Ubicación no especificada') ?>
                                        </small>
                                    </p>
                                    <span class="badge bg-<?= $torneo['condicion'] == 'en_progreso' ? 'success' : ($torneo['condicion'] == 'finalizado' ? 'secondary' : 'warning') ?>">
                                        <?= $torneo['condicion'] == 'en_progreso' ? 'En Progreso' : ($torneo['condicion'] == 'finalizado' ? 'Finalizado' : 'Pendiente') ?>
                                    </span>
                                    
                                    <div class="mt-3">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="verClasificacion(<?= $torneo['id_torneo'] ?>, '<?= htmlspecialchars($torneo['nombre']) ?>')">
                                            <i class="fas fa-table"></i> Ver Clasificación
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Jugadores Públicos - SIEMPRE VISIBLE PARA TODOS -->
        <section class="bg-light py-5">
            <div class="container">
                <h2 class="text-center mb-5"><i class="fas fa-users"></i> Jugadores Destacados</h2>
                <div class="row">
                    <?php 
                    $jugadores = $sistema->getJugadoresPublicos();
                    if (empty($jugadores)): ?>
                        <div class="col-12 text-center">
                            <p class="text-muted">No hay jugadores registrados.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($jugadores as $jugador): ?>
                            <div class="col-md-3 mb-3">
                                <div class="card card-hover">
                                    <div class="card-body text-center">
                                        <h6 class="card-title"><?= htmlspecialchars($jugador['nombre'] . ' ' . ($jugador['apellido'] ?? '')) ?></h6>
                                        <span class="rating-badge">Rating: <?= $jugador['rating'] ?></span>
                                        <?php if (!empty($jugador['equipo_nombre'])): ?>
                                            <p class="card-text mt-2">
                                                <small class="text-muted"><?= htmlspecialchars($jugador['equipo_nombre']) ?></small>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Modales Principales -->
        
        <?php include 'includes/modals/modal_Login.php'; ?>

        <?php include 'includes/modals/modal_Registro.php'; ?>
        
        <?php include 'includes/modals/modal_MiEquipo.php'; ?>
        
        <?php include 'includes/modals/modal_GestionEquipo.php'; ?>
        
        <?php if ($sistema->isLoggedIn()): ?>
        <?php include 'includes/modals/modal_CrearTorneo.php'; ?>
        
        <?php include 'includes/modals/modal_MisTorneos.php'; ?>
        
        <?php include 'includes/modals/modal_Admin.php'; ?>

        <?php include 'includes/modals/modal_SolicitudesEquipo.php'; ?>

        <?php endif; ?>
        
        <?php include 'includes/modals/modal_VerClasificacion.php'; ?>

        <!-- Formularios ocultos para eliminar -->
        <form method="post" id="form-eliminar-equipo" style="display: none;">
            <input type="hidden" name="action" value="eliminar_equipo">
            <input type="hidden" name="id_equipo" id="input-eliminar-equipo">
        </form>

        <form method="post" id="form-eliminar-torneo" style="display: none;">
            <input type="hidden" name="action" value="eliminar_torneo">
            <input type="hidden" name="id_torneo" id="input-eliminar-torneo">
        </form>

        <form method="post" id="form-eliminar-cuenta" style="display: none;">
            <input type="hidden" name="action" value="eliminar_cuenta">
            <input type="hidden" name="id_cuenta" id="input-eliminar-cuenta">
        </form>

        <!-- Alertas -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index: 9999;">
                <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index: 9999;">
                <i class="fas fa-check-circle"></i> <?= $success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer  -->
    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script src="script.js"></script>
</body>
</html>