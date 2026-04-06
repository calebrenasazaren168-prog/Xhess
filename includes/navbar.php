<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <img src="img/logo.png" 
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
                            <?php 
                            // Mostrar enlace a solicitudes solo si el jugador es capitán de algún equipo
                            $equipos_capitan = $sistema->getEquiposCapitan($_SESSION['user_id']);
                            if (!empty($equipos_capitan)):
                                $pendientes = array_sum(array_column($equipos_capitan, 'solicitudes_pendientes'));
                            ?>
                                <a class="nav-link position-relative" href="#modal-solicitudes-equipo" data-bs-toggle="modal">
                                    Solicitudes de Equipo
                                    <?php if ($pendientes > 0): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:0.6rem">
                                            <?= $pendientes ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            <?php endif; ?>
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