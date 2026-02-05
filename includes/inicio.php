<section class="inicio-section text-center">
            <div class="container" >
                <img src="img/logo-inicio.png" 
                     alt="XHESS Logo-inicio" class="mb-4" style="width: 10em; height: 10em; filter: brightness(0) invert(1);">
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