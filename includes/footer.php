<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <img src="img/logo.png"
                     alt="XHESS Logo" class="footer-logo">
                <h5>XHESS</h5>
                <p class="text-muted">
                    Sistema profesional de gestión de torneos de ajedrez. 
                    Organiza, gestiona y disfruta del ajedrez competitivo.
                </p>
                <div class="footer-social">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/xhess.oficial?igsh=MWhjNGQ4d3JjaXdycQ==" target="_blank">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <h5>Enlaces Rápidos</h5>
                <div class="footer-links">
                    <a href="#" onclick="scrollToTop()">
                        <i class="fas fa-chevron-right me-2"></i>Inicio
                    </a>
                    <a href="#" onclick="scrollToSection('torneos-activos')">
                        <i class="fas fa-chevron-right me-2"></i>Torneos Activos
                    </a>
                    <a href="#" onclick="scrollToSection('jugadores-destacados')">
                        <i class="fas fa-chevron-right me-2"></i>Jugadores
                    </a>

                    <?php if (!$sistema->isLoggedIn()): ?>
                        <a href="#registro" data-bs-toggle="modal">
                            <i class="fas fa-chevron-right me-2"></i>Registrarse
                        </a>
                        <a href="#login" data-bs-toggle="modal">
                            <i class="fas fa-chevron-right me-2"></i>Iniciar Sesión
                        </a>
                    <?php else: ?>
                        <a href="#crear-torneo" data-bs-toggle="modal">
                            <i class="fas fa-chevron-right me-2"></i>Crear Torneo
                        </a>
                        <a href="#mis-torneos" data-bs-toggle="modal">
                            <i class="fas fa-chevron-right me-2"></i>Mis Torneos
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-4 mb-4">
                <h5>Contacto</h5>
                <div class="footer-links">
                    <a href="mailto:xhess.oficial@gmail.com">
                        <i class="fas fa-envelope me-2"></i> xhess.oficial@gmail.com
                    </a>
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
