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
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .card-hover {
            transition: transform 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
        }
        .rating-badge {
            background: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8em;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-logo {
            width: 40px;
            height: 40px;
            filter: brightness(0) invert(1);
        }
        .table-responsive {
            max-height: 500px;
        }
        .tab-pane {
            padding: 20px 0;
        }
        .result-btn {
            width: 60px;
            margin: 2px;
        }
        .clasification-badge {
            font-size: 0.7em;
            margin-left: 5px;
        }
        .equipo-card {
            border-left: 4px solid #007bff;
        }
        .action-buttons {
            position: sticky;
            top: 0;
            background: white;
            z-index: 100;
            padding: 10px 0;
            margin-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .printable-section {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            background: white;
        }
        .resultado-seleccionado {
            background-color: #007bff !important;
            color: white !important;
        }
        .btn-group-vertical .btn {
            margin-bottom: 2px;
        }
        
        /* Footer mejorado - SIEMPRE VISIBLE */
        .footer {
            background: #2c3e50;
            color: white;
            padding: 40px 0 20px;
            margin-top: 60px;
            position: relative;
            width: 100%;
        }
        .footer h5 {
            color: #ecf0f1;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .footer-links a {
            color: #bdc3c7;
            text-decoration: none;
            transition: color 0.3s ease;
            display: block;
            margin-bottom: 8px;
        }
        .footer-links a:hover {
            color: #3498db;
        }
        .footer-social {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }
        .footer-social a {
            color: #bdc3c7;
            font-size: 1.2em;
            transition: color 0.3s ease;
        }
        .footer-social a:hover {
            color: #3498db;
        }
        .footer-bottom {
            border-top: 1px solid #34495e;
            padding-top: 20px;
            margin-top: 30px;
            text-align: center;
            color: #95a5a6;
        }
        .footer-logo {
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
            filter: brightness(0) invert(1);
        }
        
        /* Asegurar que el contenido tenga suficiente espacio para el footer */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .main-content {
            flex: 1;
        }

        /* Fix para modales que causan "nublado" */
        .modal-backdrop {
            z-index: 1040;
        }
        .modal {
            z-index: 1050;
        }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .main-content {
            flex: 1;
            padding-bottom: 20px; /* Espacio extra al final */
        }

        /* Footer siempre al fondo */
        .footer {
            margin-top: auto !important;
        }
        .footer {
            background: #2c3e50 !important;
            color: white !important;
            padding: 40px 0 20px !important;
            margin-top: auto !important;
            position: relative !important;
            width: 100% !important;
            display: block !important;
        }

        /* Asegurar que las secciones sean visibles */
        .container.py-5, .bg-light.py-5 {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Fix para contenido oculto */
        .main-content {
            min-height: 70vh;
        }

        /* Asegurar que el body tenga altura mínima */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
        }
    </style>
    <link rel="icon" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/svgs/solid/chess-knight.svg" type="image/svg+xml">   
</head>
<body>
    <div class="main-content">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <img src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/svgs/solid/chess-knight.svg" 
                         alt="XHESS Logo" class="navbar-logo">
                    XHESS
                </a>
                
                <div class="navbar-nav ms-auto">
                    <?php if (!$sistema->isLoggedIn()): ?>
                        <a class="nav-link" href="#login" data-bs-toggle="modal">Iniciar Sesión</a>
                        <a class="nav-link" href="#registro" data-bs-toggle="modal">Registrarse</a>
                    <?php else: ?>
                        <span class="navbar-text me-3">
                            Hola, <?= htmlspecialchars($_SESSION['username']) ?>
                            <?php if ($sistema->isJugador()): ?>
                                <small class="d-block text-muted">Jugador</small>
                            <?php elseif ($sistema->isAdmin()): ?>
                                <small class="d-block text-warning">Administrador</small>
                            <?php endif; ?>
                        </span>
                        
                        <?php if ($sistema->isAdmin()): ?>
                            <a class="nav-link" href="#admin" data-bs-toggle="modal">Admin</a>
                        <?php endif; ?>
                        
                        <?php if ($sistema->isJugador()): ?>
                            <a class="nav-link" href="#mi-equipo" data-bs-toggle="modal">Mi Equipo</a>
                        <?php endif; ?>
                        
                        <a class="nav-link" href="#mis-torneos" data-bs-toggle="modal">Mis Torneos</a>
                        <a class="nav-link" href="#crear-torneo" data-bs-toggle="modal">Crear Torneo</a>
                        
                        <?php if ($sistema->isAdmin()): ?>
                            <a class="nav-link" href="#gestion-equipos" data-bs-toggle="modal">Gestión Equipos</a>
                        <?php endif; ?>
                        
                        <form method="post" class="d-inline">
                            <input type="hidden" name="action" value="logout">
                            <button type="submit" class="btn btn-outline-light btn-sm">Cerrar Sesión</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <!-- Hero Section - SIEMPRE VISIBLE -->
        <section class="hero-section text-center">
            <div class="container">
                <img src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/svgs/solid/chess-knight.svg" 
                     alt="XHESS Logo" class="mb-4" style="width: 80px; height: 80px; filter: brightness(0) invert(1);">
                <h1 class="display-4 fw-bold">XHESS</h1>
                <p class="lead">Sistema Profesional de Gestión de Torneos de Ajedrez</p>
                <p class="mb-4">Clasificaciones • Gestión de Rondas • Resultados en Tiempo Real</p>
                <?php if (!$sistema->isLoggedIn()): ?>
                    <button class="btn btn-light btn-lg mt-3" data-bs-toggle="modal" data-bs-target="#registro">
                        <i class="fas fa-user-plus"></i> Comenzar Ahora
                    </button>
                <?php else: ?>
                    <button class="btn btn-outline-light btn-lg mt-3" data-bs-toggle="modal" data-bs-target="#crear-torneo">
                        <i class="fas fa-plus"></i> Crear Nuevo Torneo
                    </button>
                <?php endif; ?>
            </div>
        </section>

        <!-- Torneos Públicos - SIEMPRE VISIBLE PARA TODOS -->
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
        
        <!-- Modal Login -->
        <div class="modal fade" id="login" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Iniciar Sesión</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="post" id="loginForm">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="login">
                            <div class="mb-3">
                                <label class="form-label">Usuario</label>
                                <input type="text" name="usuario" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="contrasena" class="form-control" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Registro -->
        <div class="modal fade" id="registro" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Registrarse</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="post" id="registroForm">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="registro">
                            <div class="mb-3">
                                <label class="form-label">Nombre *</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Apellido</label>
                                <input type="text" name="apellido" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Usuario *</label>
                                <input type="text" name="usuario" class="form-control" required minlength="3">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Correo Electrónico *</label>
                                <input type="email" name="correo" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contraseña *</label>
                                <input type="password" name="contrasena" class="form-control" required minlength="6">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Registrarse</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Mi Equipo -->
        <?php if ($sistema->isLoggedIn() && $sistema->isJugador()): ?>
        <div class="modal fade" id="mi-equipo" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mi Equipo - Auto Gestión</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <?php
                        $equipo_actual = $sistema->getEquipoActualJugador($_SESSION['jugador_id']);
                        ?>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">Mi Equipo Actual</h6>
                            </div>
                            <div class="card-body">
                                <?php if ($equipo_actual): ?>
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h5><?= htmlspecialchars($equipo_actual['nombre']) ?></h5>
                                            <p class="mb-1"><strong>Club:</strong> <?= htmlspecialchars($equipo_actual['club_nombre']) ?></p>
                                            <p class="mb-0 text-muted">Eres miembro de este equipo</p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <form method="post" id="form-salir-equipo">
                                                <input type="hidden" name="action" value="salirse_equipo">
                                                <button type="button" class="btn btn-danger" 
                                                        onclick="confirmarAccion('¿Estás seguro de que quieres salirte de este equipo?', 'form-salir-equipo')">
                                                    <i class="fas fa-sign-out-alt"></i> Salir del Equipo
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center">
                                        <p class="text-muted mb-3">Actualmente no estás en ningún equipo.</p>
                                        <p>Puedes crear tu propio equipo o unirte a uno existente.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#crear-equipo-jugador">Crear Mi Equipo</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#unirse-equipo">Unirse a Equipo Existente</a>
                            </li>
                        </ul>

                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="crear-equipo-jugador">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Crear Mi Propio Equipo</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-3">
                                            Crea tu propio equipo y conviértete automáticamente en su capitán. Podrás gestionar tu equipo y participar en torneos.
                                        </p>
                                        
                                        <form method="post" id="form-crear-equipo">
                                            <input type="hidden" name="action" value="crear_equipo">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Nombre del Equipo *</label>
                                                    <input type="text" name="nombre" class="form-control" required 
                                                           placeholder="Ej: Los Ajedrecistas">
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Club *</label>
                                                    <select name="id_club" class="form-control" required>
                                                        <?php foreach ($sistema->getClubes() as $club): ?>
                                                            <option value="<?= $club['id_club'] ?>"><?= htmlspecialchars($club['nombre']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-success" onclick="confirmarEnvio('form-crear-equipo', '¿Crear este equipo?')">
                                                <i class="fas fa-plus-circle"></i> Crear Mi Equipo
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane fade" id="unirse-equipo">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Unirse a un Equipo Existente</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-muted mb-3">
                                            Selecciona un equipo de la lista para unirte. Puedes cambiarte de equipo en cualquier momento.
                                        </p>
                                        
                                        <div class="row">
                                            <?php 
                                            $equipos_disponibles = $sistema->getEquiposDisponiblesParaUnirse();
                                            foreach ($equipos_disponibles as $equipo): 
                                            ?>
                                                <div class="col-md-6 mb-3">
                                                    <div class="card h-100 equipo-card">
                                                        <div class="card-body">
                                                            <h6 class="card-title"><?= htmlspecialchars($equipo['nombre']) ?></h6>
                                                            <p class="card-text mb-1">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-users"></i> 
                                                                    <?= $equipo['num_jugadores'] ?> jugador(es)
                                                                </small>
                                                            </p>
                                                            <p class="card-text">
                                                                <small class="text-muted">
                                                                    <i class="fas fa-chess-board"></i> 
                                                                    <?= htmlspecialchars($equipo['club_nombre']) ?>
                                                                </small>
                                                            </p>
                                                            
                                                            <?php if ($equipo_actual && $equipo_actual['id_equipo'] == $equipo['id_equipo']): ?>
                                                                <button class="btn btn-success btn-sm w-100" disabled>
                                                                    <i class="fas fa-check"></i> Actual
                                                                </button>
                                                            <?php else: ?>
                                                                <form method="post" class="form-unirse-equipo">
                                                                    <input type="hidden" name="action" value="unirse_equipo">
                                                                    <input type="hidden" name="id_equipo" value="<?= $equipo['id_equipo'] ?>">
                                                                    <button type="button" class="btn btn-primary btn-sm w-100"
                                                                            onclick="confirmarAccion('¿Unirte al equipo <?= htmlspecialchars($equipo['nombre']) ?>?', this.closest('form'))">
                                                                        <i class="fas fa-user-plus"></i> Unirse
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        
                                        <?php if (empty($equipos_disponibles)): ?>
                                            <div class="text-center">
                                                <p class="text-muted">No hay equipos disponibles en este momento.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Modal Gestión de Equipos -->
        <?php if ($sistema->isLoggedIn() && $sistema->isAdmin()): ?>
        <div class="modal fade" id="gestion-equipos" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Gestión de Equipos - Panel Admin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#crear-equipo">Crear Equipo</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#asignar-jugadores">Asignar Jugadores</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#ver-equipos">Ver Equipos</a>
                            </li>
                        </ul>
                        
                        <div class="tab-content mt-3">
                            <div class="tab-pane fade show active" id="crear-equipo">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    Solo los administradores pueden crear equipos. Los jugadores pueden unirse por sí mismos.
                                </div>
                                <form method="post" id="form-crear-equipo-admin">
                                    <input type="hidden" name="action" value="crear_equipo">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nombre del Equipo *</label>
                                            <input type="text" name="nombre" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Club *</label>
                                            <select name="id_club" class="form-control" required>
                                                <?php foreach ($sistema->getClubes() as $club): ?>
                                                    <option value="<?= $club['id_club'] ?>"><?= htmlspecialchars($club['nombre']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-success" onclick="confirmarEnvio('form-crear-equipo-admin', '¿Crear este equipo?')">
                                        Crear Equipo
                                    </button>
                                </form>
                            </div>
                            
                            <div class="tab-pane fade" id="asignar-jugadores">
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    <strong>Función administrativa:</strong> Asignación forzada de jugadores a equipos.
                                </div>
                                
                                <form method="post" id="form-asignar-jugador">
                                    <input type="hidden" name="action" value="admin_asignar_jugador">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Jugador *</label>
                                            <select name="id_jugador" class="form-control" required>
                                                <option value="">Seleccionar jugador...</option>
                                                <?php foreach ($sistema->getJugadoresRegistrados() as $jugador): ?>
                                                    <option value="<?= $jugador['id_jugador'] ?>">
                                                        <?= htmlspecialchars($jugador['nombre'] . ' ' . $jugador['apellido']) ?> 
                                                        (<?= htmlspecialchars($jugador['usuario']) ?>)
                                                        <?php if ($jugador['equipo_actual']): ?>
                                                            - Actual: <?= htmlspecialchars($jugador['equipo_actual']) ?>
                                                        <?php endif; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Asignar a Equipo *</label>
                                            <select name="id_equipo" class="form-control" required>
                                                <option value="">Seleccionar equipo...</option>
                                                <?php foreach ($sistema->getEquiposDisponibles() as $equipo): ?>
                                                    <option value="<?= $equipo['id_equipo'] ?>">
                                                        <?= htmlspecialchars($equipo['nombre']) ?> 
                                                        (<?= htmlspecialchars($equipo['club_nombre']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-warning" 
                                            onclick="confirmarAccion('¿Asignar jugador a este equipo? Esta acción sobrescribirá cualquier equipo actual.', 'form-asignar-jugador')">
                                        <i class="fas fa-user-cog"></i> Asignar Forzadamente
                                    </button>
                                </form>
                                
                                <hr>
                                
                                <h6>Quitar Jugador de Equipo (Admin)</h6>
                                <form method="post" id="form-quitar-jugador">
                                    <input type="hidden" name="action" value="admin_quitar_jugador">
                                    <div class="row">
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label">Jugador con Equipo *</label>
                                            <select name="id_jugador" class="form-control" required>
                                                <option value="">Seleccionar jugador...</option>
                                                <?php foreach ($sistema->getJugadoresRegistrados() as $jugador): ?>
                                                    <?php if ($jugador['equipo_actual']): ?>
                                                        <option value="<?= $jugador['id_jugador'] ?>">
                                                            <?= htmlspecialchars($jugador['nombre'] . ' ' . $jugador['apellido']) ?> 
                                                            - <?= htmlspecialchars($jugador['equipo_actual']) ?>
                                                        </option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-danger w-100" 
                                                    onclick="confirmarAccion('¿Quitar a este jugador de su equipo actual?', 'form-quitar-jugador')">
                                                <i class="fas fa-user-times"></i> Quitar Forzadamente
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="tab-pane fade" id="ver-equipos">
                                <h6>Equipos por Club</h6>
                                <?php foreach ($sistema->getClubes() as $club): ?>
                                    <div class="card mb-3">
                                        <div class="card-header">
                                            <h6 class="mb-0"><?= htmlspecialchars($club['nombre']) ?></h6>
                                        </div>
                                        <div class="card-body">
                                            <?php 
                                            $equiposClub = $sistema->getEquiposPorClub($club['id_club']);
                                            if (empty($equiposClub)): ?>
                                                <p class="text-muted mb-0">No hay equipos en este club.</p>
                                            <?php else: ?>
                                                <div class="row">
                                                    <?php foreach ($equiposClub as $equipo): ?>
                                                        <div class="col-md-6 mb-2">
                                                            <div class="d-flex justify-content-between align-items-center border p-2 rounded">
                                                                <div>
                                                                    <strong><?= htmlspecialchars($equipo['nombre']) ?></strong>
                                                                    <br>
                                                                    <small class="text-muted">
                                                                        <?= $equipo['num_jugadores'] ?> jugador(es)
                                                                    </small>
                                                                </div>
                                                                <div>
                                                                    <button class="btn btn-sm btn-outline-danger" 
                                                                            onclick="eliminarEquipo(<?= $equipo['id_equipo'] ?>, '<?= htmlspecialchars($equipo['nombre']) ?>')"
                                                                            title="Eliminar equipo">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Modal Crear Torneo -->
        <?php if ($sistema->isLoggedIn()): ?>
        <div class="modal fade" id="crear-torneo" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Crear Nuevo Torneo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="post" id="form-crear-torneo">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="crear_torneo">
                            <input type="hidden" name="id_cuenta" value="<?= $_SESSION['user_id'] ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre del Torneo *</label>
                                    <input type="text" name="nombre" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Sistema de Juego *</label>
                                    <select name="sistema" class="form-control" required>
                                        <option value="round_robin">Round Robin Clásico</option>
                                        <option value="round_robin_ponderado" selected>Round Robin Ponderado</option>
                                        <option value="suizo">Sistema Suizo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Número de Tableros *</label>
                                    <input type="number" name="nro_tableros" class="form-control" value="4" min="1" max="10" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ritmo de Tiempo *</label>
                                    <select name="ritmo_tiempo" class="form-control" required>
                                        <option value="3min">3 minutos</option>
                                        <option value="5min">5 minutos</option>
                                        <option value="10min">10 minutos</option>
                                        <option value="15min+10s">15min + 10s</option>
                                        <option value="60+30" selected>60min + 30s</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="3" placeholder="Describe tu torneo..."></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Inicio *</label>
                                    <input type="date" name="fecha_inicio" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha Fin *</label>
                                    <input type="date" name="fecha_fin" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ubicación</label>
                                    <input type="text" name="ubicacion" class="form-control" placeholder="Ej: CBTIS 168 - Gimnasio">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Club *</label>
                                    <select name="id_club" class="form-control" required>
                                        <?php foreach ($sistema->getClubes() as $club): ?>
                                            <option value="<?= $club['id_club'] ?>" <?= $club['id_club'] == 5 ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($club['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="confirmarEnvio('form-crear-torneo', '¿Crear este torneo?')">Crear Torneo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Mis Torneos -->
        <div class="modal fade" id="mis-torneos" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mis Torneos - Gestión Completa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <?php 
                            $misTorneos = $sistema->getMisTorneos($_SESSION['user_id']);
                            if (empty($misTorneos)): ?>
                                <div class="col-12 text-center">
                                    <p class="text-muted">No has creado ningún torneo todavía.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($misTorneos as $torneo): ?>
                                    <div class="col-12 mb-5">
                                        <div class="card">
                                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-0"><?= htmlspecialchars($torneo['nombre']) ?></h5>
                                                    <small><?= htmlspecialchars($torneo['descripcion'] ?: 'Sin descripción') ?></small>
                                                </div>
                                                <span class="badge bg-<?= $torneo['condicion'] == 'en_progreso' ? 'success' : ($torneo['condicion'] == 'finalizado' ? 'secondary' : 'warning') ?>">
                                                    <?= $torneo['condicion'] == 'en_progreso' ? 'En Progreso' : ($torneo['condicion'] == 'finalizado' ? 'Finalizado' : 'Pendiente') ?>
                                                </span>
                                            </div>
                                            <div class="card-body">
                                                <ul class="nav nav-tabs" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-bs-toggle="tab" href="#equipos-<?= $torneo['id_torneo'] ?>">Equipos</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#clasificacion-<?= $torneo['id_torneo'] ?>">Clasificación</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#rondas-<?= $torneo['id_torneo'] ?>">Rondas</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#emparejamientos-<?= $torneo['id_torneo'] ?>">Emparejamientos</a>
                                                    </li>
                                                </ul>
                                                
                                                <div class="tab-content mt-3">
                                                    <div class="tab-pane fade show active" id="equipos-<?= $torneo['id_torneo'] ?>">
                                                        <h6>Equipos del Torneo</h6>
                                                        <?php 
                                                        $equiposTorneo = $sistema->getEquiposTorneo($torneo['id_torneo']);
                                                        $tieneRondas = $sistema->tieneRondasGeneradas($torneo['id_torneo']);
                                                        
                                                        if (empty($equiposTorneo)): ?>
                                                            <p class="text-muted">No hay equipos en este torneo.</p>
                                                        <?php else: ?>
                                                            <div class="row">
                                                                <?php foreach ($equiposTorneo as $equipo): ?>
                                                                    <div class="col-md-6 mb-3">
                                                                        <div class="card">
                                                                            <div class="card-body">
                                                                                <h6><?= htmlspecialchars($equipo['nombre']) ?></h6>
                                                                                <?php 
                                                                                $jugadoresEquipo = $sistema->getJugadoresPorEquipo($equipo['id_equipo']);
                                                                                if (!empty($jugadoresEquipo)): ?>
                                                                                    <ul class="list-unstyled">
                                                                                        <?php foreach ($jugadoresEquipo as $jugador): ?>
                                                                                            <li>
                                                                                                <small><?= htmlspecialchars($jugador['nombre'] . ' ' . $jugador['apellido']) ?> 
                                                                                                <span class="badge bg-secondary"><?= $jugador['rating'] ?></span></small>
                                                                                            </li>
                                                                                        <?php endforeach; ?>
                                                                                    </ul>
                                                                                <?php endif; ?>
                                                                                
                                                                                <!-- Mostrar opción de retirar equipo si ya hay rondas generadas -->
                                                                                <?php if ($tieneRondas): ?>
                                                                                    <form method="post" class="form-retirar-equipo mt-2">
                                                                                        <input type="hidden" name="action" value="retirar_equipo">
                                                                                        <input type="hidden" name="id_torneo" value="<?= $torneo['id_torneo'] ?>">
                                                                                        <input type="hidden" name="id_equipo" value="<?= $equipo['id_equipo'] ?>">
                                                                                        <button type="button" class="btn btn-sm btn-outline-warning" 
                                                                                                onclick="confirmarAccion('¿Retirar el equipo <?= htmlspecialchars($equipo['nombre']) ?> del torneo? El equipo recibirá BYE en las rondas restantes.', this.closest('form'))">
                                                                                            <i class="fas fa-door-open"></i> Retirar Equipo (BYE)
                                                                                        </button>
                                                                                    </form>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <!-- Solo permitir agregar equipos si no hay rondas generadas -->
                                                        <?php if (!$tieneRondas): ?>
                                                        <div class="card mt-3">
                                                        <div class="card-header">
                                                        <h6 class="mb-0">Agregar Equipos al Torneo</h6>
                                                        </div>
                                                        <div class="card-body">
                                                        <form method="post" id="form-agregar-equipos-<?= $torneo['id_torneo'] ?>">
                                                        <input type="hidden" name="action" value="agregar_equipos_multiples">
                                                        <input type="hidden" name="id_torneo" value="<?= $torneo['id_torneo'] ?>">

                                                        <div class="mb-3">
                                                        <label class="form-label">Seleccionar Equipos (Múltiple)</label>
                                                        <select name="id_equipos[]" class="form-control" multiple size="6" required style="height: 150px;">
                                                        <?php
                                                        $equiposDisponibles = $sistema->getEquiposDisponibles();
                                                        $equiposEnTorneo = $sistema->getEquiposTorneo($torneo['id_torneo']);
                                                        $idsEquiposEnTorneo = array_column($equiposEnTorneo, 'id_equipo');

                                                        foreach ($equiposDisponibles as $equipo):
                                                            if (!in_array($equipo['id_equipo'], $idsEquiposEnTorneo)):
                                                                ?>
                                                                <option value="<?= $equipo['id_equipo'] ?>">
                                                                <?= htmlspecialchars($equipo['nombre']) ?>
                                                                (<?= htmlspecialchars($equipo['club_nombre']) ?>)
                                                                </option>
                                                                <?php
                                                                endif;
                                                            endforeach;
                                                        ?>
                                                        </select>
                                                        <small class="form-text text-muted">
                                                        Mantén presionada la tecla Ctrl (Cmd en Mac) para seleccionar múltiples equipos
                                                        </small>
                                                        </div>

                                                        <button type="button" class="btn btn-success"
                                                        onclick="confirmarEnvio('form-agregar-equipos-<?= $torneo['id_torneo'] ?>', '¿Agregar los equipos seleccionados al torneo?')">
                                                        <i class="fas fa-plus-circle"></i> Agregar Equipos Seleccionados
                                                        </button>
                                                        </form>
                                                        </div>
                                                        </div>
                                                        <?php else: ?>
                                                        <div class="alert alert-info mt-3">
                                                        <i class="fas fa-info-circle"></i>
                                                        No se pueden agregar más equipos porque ya se han generado rondas para este torneo.
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="tab-pane fade" id="clasificacion-<?= $torneo['id_torneo'] ?>">
                                                        <div class="printable-section">
                                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                                <h6>Clasificación de Equipos</h6>
                                                            </div>
                                                            
                                                            <?php 
                                                            $clasificacionEquipos = $sistema->getClasificacionEquipos($torneo['id_torneo']);
                                                            if (empty($clasificacionEquipos)): ?>
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
                                                            
                                                            <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                                                                <h6>Clasificación de Jugadores</h6>
                                                            </div>
                                                            
                                                            <?php 
                                                            $clasificacionJugadores = $sistema->getClasificacionJugadores($torneo['id_torneo']);
                                                            if (empty($clasificacionJugadores)): ?>
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
                                                    </div>
                                                    
                                                    <div class="tab-pane fade" id="rondas-<?= $torneo['id_torneo'] ?>">
                                                        <?php 
                                                        $proximaRonda = $sistema->getProximaRonda($torneo['id_torneo']);
                                                        $equiposTorneo = $sistema->getEquiposTorneo($torneo['id_torneo']);
                                                        $num_equipos = count($equiposTorneo);
                                                        $max_rondas = $num_equipos - 1;
                                                        if ($num_equipos % 2 != 0) {
                                                            $max_rondas = $num_equipos;
                                                        }
                                                        
                                                        if ($proximaRonda <= $max_rondas): ?>
                                                            <div class="card mb-4">
                                                                <div class="card-header">
                                                                    <h6 class="mb-0">Generar Nueva Ronda</h6>
                                                                </div>
                                                                <div class="card-body">
                                                                    <form method="post" id="form-generar-ronda-<?= $torneo['id_torneo'] ?>">
                                                                        <input type="hidden" name="action" value="generar_ronda">
                                                                        <input type="hidden" name="id_torneo" value="<?= $torneo['id_torneo'] ?>">
                                                                        <div class="row">
                                                                            <div class="col-md-3 mb-3">
                                                                                <label class="form-label">Número de Ronda</label>
                                                                                <input type="number" name="numero_ronda" class="form-control" value="<?= $proximaRonda ?>" readonly>
                                                                            </div>
                                                                            <div class="col-md-4 mb-3">
                                                                                <label class="form-label">Fecha de la Ronda</label>
                                                                                <input type="date" name="fecha_ronda" class="form-control" required>
                                                                            </div>
                                                                            <div class="col-md-3 mb-3">
                                                                                <label class="form-label">Máximo de Rondas</label>
                                                                                <input type="text" class="form-control" value="<?= $max_rondas ?>" readonly>
                                                                                <small class="form-text text-muted">Para <?= $num_equipos ?> equipos</small>
                                                                            </div>
                                                                            <div class="col-md-2 mb-3">
                                                                                <label class="form-label">&nbsp;</label>
                                                                                <button type="button" class="btn btn-primary w-100" 
                                                                                        onclick="confirmarEnvio('form-generar-ronda-<?= $torneo['id_torneo'] ?>', '¿Generar ronda <?= $proximaRonda ?>?')">
                                                                                    Generar
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="alert alert-info">
                                                                <i class="fas fa-info-circle"></i> 
                                                                Se ha alcanzado el máximo de <?= $max_rondas ?> rondas para <?= $num_equipos ?> equipos.
                                                            </div>
                                                        <?php endif; ?>
                                                        
                                                        <?php 
                                                        $rondasTorneo = $sistema->getRondasTorneo($torneo['id_torneo']);
                                                        if (empty($rondasTorneo)): ?>
                                                            <p class="text-muted">No hay rondas generadas para este torneo.</p>
                                                        <?php else: ?>
                                                            <?php foreach ($rondasTorneo as $ronda): ?>
                                                                <div class="card mb-3">
                                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                                        <h6 class="mb-0">Ronda <?= $ronda['numero_ronda'] ?> - <?= date('d/m/Y', strtotime($ronda['fecha_ronda'])) ?></h6>
                                                                        <span class="badge bg-info"><?= $ronda['partidas_jugadas'] ?> partidas</span>
                                                                    </div>
                                                                    <div class="card-body">
                                                                        <?php 
                                                                        $partidasRonda = $sistema->getPartidasRonda($ronda['id_ronda']);
                                                                        if (empty($partidasRonda)): ?>
                                                                            <p class="text-muted">No hay partidas en esta ronda.</p>
                                                                        <?php else: ?>
                                                                            <form method="post" class="form-resultados" data-ronda="<?= $ronda['id_ronda'] ?>">
                                                                                <input type="hidden" name="action" value="actualizar_multiples_resultados">
                                                                                <div class="table-responsive">
                                                                                    <table class="table table-sm">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th>Tablero</th>
                                                                                                <th>Blancas</th>
                                                                                                <th>Negras</th>
                                                                                                <th>Resultado Actual</th>
                                                                                                <th>Nuevo Resultado</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            <?php foreach ($partidasRonda as $partida): ?>
                                                                                                <tr>
                                                                                                    <td><strong><?= $partida['tablero'] ?></strong></td>
                                                                                                    <td>
                                                                                                        <?= htmlspecialchars($partida['jugador_blancas_nombre'] . ' ' . $partida['jugador_blancas_apellido']) ?>
                                                                                                        <br><small class="text-muted"><?= htmlspecialchars($partida['equipo_blancas_nombre']) ?></small>
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        <?= htmlspecialchars($partida['jugador_negras_nombre'] . ' ' . $partida['jugador_negras_apellido']) ?>
                                                                                                        <br><small class="text-muted"><?= htmlspecialchars($partida['equipo_negras_nombre']) ?></small>
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        <span class="badge bg-<?= $partida['resultado'] == '*' ? 'secondary' : ($partida['resultado'] == '1-0' ? 'success' : ($partida['resultado'] == '0-1' ? 'danger' : 'warning')) ?>">
                                                                                                            <?= $partida['resultado'] ?>
                                                                                                        </span>
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        <input type="hidden" name="resultados[<?= $partida['id_partida'] ?>]" id="resultado-<?= $partida['id_partida'] ?>" value="<?= $partida['resultado'] ?>">
                                                                                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                                                                                            <button type="button" class="btn btn-outline-success result-btn <?= $partida['resultado'] == '1-0' ? 'resultado-seleccionado' : '' ?>" 
                                                                                                                    onclick="seleccionarResultado(<?= $partida['id_partida'] ?>, '1-0', this)">1-0</button>
                                                                                                            <button type="button" class="btn btn-outline-warning result-btn <?= $partida['resultado'] == '1/2' ? 'resultado-seleccionado' : '' ?>" 
                                                                                                                    onclick="seleccionarResultado(<?= $partida['id_partida'] ?>, '1/2', this)">½-½</button>
                                                                                                            <button type="button" class="btn btn-outline-danger result-btn <?= $partida['resultado'] == '0-1' ? 'resultado-seleccionado' : '' ?>" 
                                                                                                                    onclick="seleccionarResultado(<?= $partida['id_partida'] ?>, '0-1', this)">0-1</button>
                                                                                                        </div>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            <?php endforeach; ?>
                                                                                        </tbody>
                                                                                    </table>
                                                                                </div>
                                                                                <div class="text-end mt-3">
                                                                                    <button type="button" class="btn btn-primary" onclick="confirmarEnvioFormResultados(this.closest('form'))">
                                                                                        <i class="fas fa-save"></i> Confirmar Cambios
                                                                                    </button>
                                                                                </div>
                                                                            </form>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="tab-pane fade" id="emparejamientos-<?= $torneo['id_torneo'] ?>">
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
                                                                $ronda_actual = 0;
                                                                foreach ($emparejamientosTorneo as $emparejamiento): 
                                                                    if ($emparejamiento['numero_ronda'] != $ronda_actual): 
                                                                        $ronda_actual = $emparejamiento['numero_ronda'];
                                                                ?>
                                                                    <h6 class="mt-4">Ronda <?= $ronda_actual ?> - <?= date('d/m/Y', strtotime($emparejamiento['fecha_ronda'])) ?></h6>
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
                                                                <?php endif; ?>
                                                                            <tr>
                                                                                <td><?= $emparejamiento['tablero'] ?></td>
                                                                                <td>
                                                                                    <?= htmlspecialchars($emparejamiento['jugador_blancas_nombre'] . ' ' . $emparejamiento['jugador_blancas_apellido']) ?>
                                                                                    <br><small class="text-muted"><?= htmlspecialchars($emparejamiento['equipo_blancas_nombre']) ?></small>
                                                                                </td>
                                                                                <td>
                                                                                    <?= htmlspecialchars($emparejamiento['jugador_negras_nombre'] . ' ' . $emparejamiento['jugador_negras_apellido']) ?>
                                                                                    <br><small class="text-muted"><?= htmlspecialchars($emparejamiento['equipo_negras_nombre']) ?></small>
                                                                                </td>
                                                                                <td><?= $emparejamiento['resultado'] ?></td>
                                                                            </tr>
                                                                <?php 
                                                                    $siguiente = next($emparejamientosTorneo);
                                                                    if (!$siguiente || $siguiente['numero_ronda'] != $ronda_actual): 
                                                                        prev($emparejamientosTorneo);
                                                                ?>
                                                                        </tbody>
                                                                    </table>
                                                                <?php endif; ?>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Admin -->
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
        <?php endif; ?>

        <!-- Modal para Ver Clasificación -->
        <div class="modal fade" id="modal-clasificacion" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="clasificacion-titulo">Clasificación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="clasificacion-contenido">
                    </div>
                </div>
            </div>
        </div>

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

    <!-- Footer Profesional - SIEMPRE VISIBLE -->
    <footer class="footer">
    <div class="container">
    <div class="row">
    <div class="col-md-4 mb-4">
    <img src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6/svgs/solid/chess-knight.svg"
    alt="XHESS Logo" class="footer-logo">
    <h5>XHESS</h5>
    <p class="text-muted">Sistema profesional de gestión de torneos de ajedrez. Organiza, gestiona y disfruta del ajedrez competitivo.</p>
    <div class="footer-social">
    <a href="#"><i class="fab fa-facebook"></i></a>
    <a href="#"><i class="fab fa-twitter"></i></a>
    <a href="https://www.instagram.com/xhess.oficial?igsh=MWhjNGQ4d3JjaXdycQ==" target="_blank"><i class="fab fa-instagram"></i></a>
    <a href="#"><i class="fab fa-linkedin"></i></a>
    </div>
    </div>

    <div class="col-md-4 mb-4">
    <h5>Enlaces Rápidos</h5>
    <div class="footer-links">
    <a href="#" onclick="scrollToTop()"><i class="fas fa-chevron-right me-2"></i>Inicio</a>
    <a href="#" onclick="scrollToSection('torneos-activos')"><i class="fas fa-chevron-right me-2"></i>Torneos Activos</a>
    <a href="#" onclick="scrollToSection('jugadores-destacados')"><i class="fas fa-chevron-right me-2"></i>Jugadores</a>
    <?php if (!$sistema->isLoggedIn()): ?>
    <a href="#registro" data-bs-toggle="modal"><i class="fas fa-chevron-right me-2"></i>Registrarse</a>
    <a href="#login" data-bs-toggle="modal"><i class="fas fa-chevron-right me-2"></i>Iniciar Sesión</a>
    <?php else: ?>
    <a href="#crear-torneo" data-bs-toggle="modal"><i class="fas fa-chevron-right me-2"></i>Crear Torneo</a>
    <a href="#mis-torneos" data-bs-toggle="modal"><i class="fas fa-chevron-right me-2"></i>Mis Torneos</a>
    <?php endif; ?>
    </div>
    </div>

    <div class="col-md-4 mb-4">
    <h5>Contacto</h5>
    <div class="footer-links">
    <a href="mailto:xhess.oficial@gmail.com"><i class="fas fa-envelope me-2"></i> xhess.oficial@gmail.com</a>
    <p><i class="fas fa-phone me-2"></i> 449 434 2796</p>
    <p><i class="fas fa-map-marker-alt me-2"></i> CBtis 168, Rio Rhin s/n</p>
    </div>
    </div>
    </div>

    <div class="footer-bottom">
    <div class="row">
    <div class="col-md-6">
    <p>&copy; 2025 XHESS. Todos los derechos reservados.</p>
    </div>
    <div class="col-md-6">
    <div class="footer-links d-flex justify-content-md-end">
    <a href="#" class="me-3">Política de Privacidad</a>
    <a href="#">Términos de Servicio</a>
    </div>
    </div>
    </div>
    </div>
    </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.remove();
            });
        }, 5000);

        document.addEventListener('DOMContentLoaded', function() {
            const fechaInicio = document.querySelector('input[name="fecha_inicio"]');
            const fechaFin = document.querySelector('input[name="fecha_fin"]');

            if (fechaInicio && fechaFin) {
                const today = new Date();
                const todayString = today.toISOString().split('T')[0];

                // PERMITIR FECHA DE HOY - Cambio importante
                fechaInicio.min = todayString;
                fechaFin.min = todayString;

                // Establecer valores por defecto a hoy
                fechaInicio.value = todayString;

                // Calcular fecha fin por defecto (1 día después en lugar de 7)
                const endDate = new Date(today);
                endDate.setDate(today.getDate() + 1); // SOLO 1 DÍA DESPUÉS
                fechaFin.value = endDate.toISOString().split('T')[0];

                fechaInicio.addEventListener('change', function() {
                    fechaFin.min = this.value;
                    if (fechaFin.value < this.value) {
                        fechaFin.value = this.value;
                    }
                });
            }

            // Prevenir cierre automático de modales al enviar formularios
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    // Solo prevenir el comportamiento por defecto para formularios que no son de login/logout
                    if (!this.querySelector('input[name="action"][value="login"]') && 
                        !this.querySelector('input[name="action"][value="logout"]')) {
                        e.preventDefault();
                        
                        // Enviar el formulario via AJAX para mantener el modal abierto
                        const formData = new FormData(this);
                        
                        fetch('sistema_xhess.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            // Recargar la página para ver los cambios
                            window.location.reload();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error al procesar la solicitud');
                        });
                    }
                });
            });

            // Fix para pantalla "nublada" - cerrar modales automáticamente después del login
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('login_success')) {
                // Cerrar cualquier modal abierto
                const modals = document.querySelectorAll('.modal.show');
                modals.forEach(modal => {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                });
                // Remover backdrop
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        });

        function verClasificacion(idTorneo, nombreTorneo) {
            // Mostrar loading
            document.getElementById('clasificacion-contenido').innerHTML = `
            <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
            </div>
            <p class="mt-2">Cargando clasificación...</p>
            </div>
            `;

            const modal = new bootstrap.Modal(document.getElementById('modal-clasificacion'));
            modal.show();

            document.getElementById('clasificacion-titulo').textContent = 'Clasificación - ' + nombreTorneo;

            fetch(`ajax_clasificacion.php?id_torneo=${idTorneo}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.text();
            })
            .then(html => {
                document.getElementById('clasificacion-contenido').innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('clasificacion-contenido').innerHTML = `
                <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Error al cargar la clasificación. Por favor, intenta nuevamente.
                <br><small>${error.message}</small>
                </div>
                `;
            });
        }

        function eliminarEquipo(idEquipo, nombreEquipo) {
            if (confirm(`¿Estás seguro de que quieres eliminar el equipo "${nombreEquipo}"?\n\nEsta acción no se puede deshacer.`)) {
                document.getElementById('input-eliminar-equipo').value = idEquipo;
                document.getElementById('form-eliminar-equipo').submit();
            }
        }

        function eliminarTorneo(idTorneo, nombreTorneo) {
            if (confirm(`¿Estás seguro de que quieres eliminar el torneo "${nombreTorneo}"?\n\nEsta acción eliminará todas las rondas, partidas y estadísticas asociadas.\n\nEsta acción no se puede deshacer.`)) {
                document.getElementById('input-eliminar-torneo').value = idTorneo;
                document.getElementById('form-eliminar-torneo').submit();
            }
        }

        function eliminarCuenta(idCuenta, usuario) {
            if (confirm(`¿Estás seguro de que quieres eliminar la cuenta de "${usuario}"?\n\nEsta acción eliminará todos los datos asociados: jugador, equipos creados, torneos creados, etc.\n\nEsta acción no se puede deshacer.`)) {
                document.getElementById('input-eliminar-cuenta').value = idCuenta;
                document.getElementById('form-eliminar-cuenta').submit();
            }
        }

        function confirmarAccion(mensaje, formElement) {
            if (confirm(mensaje)) {
                if (typeof formElement === 'string') {
                    document.getElementById(formElement).submit();
                } else {
                    formElement.submit();
                }
            }
        }

        function confirmarEnvio(formId, mensaje) {
            if (confirm(mensaje)) {
                document.getElementById(formId).submit();
            }
        }

        function seleccionarResultado(idPartida, resultado, boton) {
            // Remover la clase de selección de todos los botones del mismo grupo
            const grupo = boton.closest('.btn-group-vertical');
            grupo.querySelectorAll('.result-btn').forEach(btn => {
                btn.classList.remove('resultado-seleccionado');
            });
            
            // Añadir la clase de selección al botón clickeado
            boton.classList.add('resultado-seleccionado');
            
            // Actualizar el valor del input hidden
            document.getElementById('resultado-' + idPartida).value = resultado;
        }

        function confirmarEnvioFormResultados(form) {
            if (confirm('¿Estás seguro de que quieres guardar todos los cambios de resultados?')) {
                form.submit();
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const modales = document.querySelectorAll('.modal');
            modales.forEach(modal => {
                modal.addEventListener('hide.bs.modal', function(event) {
                    const forms = this.querySelectorAll('form');
                    let hasUnsavedChanges = false;
                    
                    forms.forEach(form => {
                        const inputs = form.querySelectorAll('input, select, textarea');
                        inputs.forEach(input => {
                            if (input.value && !input.readOnly && input.type !== 'hidden') {
                                hasUnsavedChanges = true;
                            }
                        });
                    });
                    
                    if (hasUnsavedChanges) {
                        if (!confirm('Tienes cambios sin guardar. ¿Estás seguro de que quieres cerrar?')) {
                            event.preventDefault();
                        }
                    }
                });
            });
        });
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function scrollToSection(sectionId) {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            } else {
                // Si no existe la sección, ir al inicio
                scrollToTop();
            }
        }

        // Asegurar que el footer siempre sea visible
        document.addEventListener('DOMContentLoaded', function() {
            // Forzar el footer al final
            const mainContent = document.querySelector('.main-content');
            const footer = document.querySelector('.footer');

            if (mainContent && footer) {
                const updateFooterPosition = () => {
                    const windowHeight = window.innerHeight;
                    const mainContentHeight = mainContent.offsetHeight;
                    const footerHeight = footer.offsetHeight;

                    if (mainContentHeight + footerHeight < windowHeight) {
                        footer.style.marginTop = 'auto';
                    } else {
                        footer.style.marginTop = '60px';
                    }
                };

                updateFooterPosition();
                window.addEventListener('resize', updateFooterPosition);
            }
        });

    </script>
</body>
</html>
