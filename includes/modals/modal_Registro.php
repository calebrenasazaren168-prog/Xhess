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