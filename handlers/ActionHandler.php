<?php
require_once __DIR__ . '/../classes/RondaManager.php';
require_once __DIR__ . '/../classes/AdminManager.php';

class ActionHandler {
    private $auth;
    private $jugador;
    private $equipo;
    private $torneo;
    private $ronda;
    private $admin;
    public $error = null;
    public $success = null;
    
    public function __construct($auth, $jugador, $equipo, $torneo) {
        $this->auth = $auth;
        $this->jugador = $jugador;
        $this->equipo = $equipo;
        $this->torneo = $torneo;
        $this->ronda = new RondaManager();
        $this->admin = new AdminManager();
    }
    
    public function process($post) {
        $action = $post['action'] ?? '';
        
        try {
            switch ($action) {

                case 'login':
                    return $this->login($post);
                    
                case 'registro':
                    return $this->registro($post);
                    
                case 'logout':
                    $this->auth->logout();
                    return ['redirect' => 'index.php'];

                // ── SOLICITUDES ──────────────────────────────────────────────
                case 'solicitar_equipo':
                    if (!$this->auth->isLoggedIn() || !$this->auth->isJugador()) {
                        throw new Exception("No autorizado");
                    }
                    $mensaje = trim($post['mensaje'] ?? '');
                    $this->equipo->solicitarIngreso(
                        $this->auth->getJugadorId(),
                        $post['id_equipo'],
                        $mensaje
                    );
                    $this->success = "Solicitud enviada exitosamente. El capitán del equipo la revisará pronto.";
                    break;

                case 'aceptar_solicitud':
                    if (!$this->auth->isLoggedIn()) {
                        throw new Exception("No autorizado");
                    }
                    $this->equipo->aceptarSolicitud($post['id_solicitud'], $this->auth->getUserId());
                    $this->success = "Solicitud aceptada. El jugador ha sido agregado al equipo.";
                    break;

                case 'rechazar_solicitud':
                    if (!$this->auth->isLoggedIn()) {
                        throw new Exception("No autorizado");
                    }
                    $this->equipo->rechazarSolicitud($post['id_solicitud'], $this->auth->getUserId());
                    $this->success = "Solicitud rechazada.";
                    break;

                case 'cancelar_solicitud':
                    if (!$this->auth->isLoggedIn() || !$this->auth->isJugador()) {
                        throw new Exception("No autorizado");
                    }
                    $this->equipo->cancelarSolicitud($post['id_solicitud'], $this->auth->getJugadorId());
                    $this->success = "Solicitud cancelada.";
                    break;

                // ────────────────────────────────────────────────────────────
                    
                case 'salirse_equipo':
                    if (!$this->auth->isLoggedIn() || !$this->auth->isJugador()) {
                        throw new Exception("No autorizado");
                    }
                    $this->jugador->salirseDeEquipo($this->auth->getJugadorId());
                    $this->success = "Te has salido del equipo exitosamente";
                    break;
                    
                case 'crear_equipo':
                    if (!$this->auth->isLoggedIn()) {
                        throw new Exception("No autorizado");
                    }
                    $post['id_creador'] = $this->auth->getUserId();
                    $this->equipo->crear($post);
                    
                    // Si es jugador, asignarlo automáticamente
                    if ($this->auth->isJugador()) {
                        $id_equipo = Database::getInstance()->getConnection()->lastInsertId();
                        $pdo = Database::getInstance()->getConnection();
                        $stmt = $pdo->prepare("UPDATE jugadores SET id_equipo = ? WHERE id_jugador = ?");
                        $stmt->execute([$id_equipo, $this->auth->getJugadorId()]);
                    }
                    
                    $this->success = "Equipo creado exitosamente";
                    break;
                    
                case 'eliminar_equipo':
                    if (!$this->auth->isAdmin()) {
                        throw new Exception("Solo administradores");
                    }
                    $this->equipo->eliminar($post['id_equipo']);
                    $this->success = "Equipo eliminado exitosamente";
                    break;

                case 'admin_asignar_jugador':
                    if (!$this->auth->isAdmin()) {
                        throw new Exception("Solo administradores");
                    }
                    $this->admin->asignarJugadorAEquipo($post['id_jugador'], $post['id_equipo']);
                    $this->success = "Jugador asignado al equipo exitosamente";
                    break;
                    
                case 'admin_quitar_jugador':
                    if (!$this->auth->isAdmin()) {
                        throw new Exception("Solo administradores");
                    }
                    $this->admin->quitarJugadorDeEquipo($post['id_jugador']);
                    $this->success = "Jugador quitado del equipo exitosamente";
                    break;

                case 'crear_torneo':
                    if (!$this->auth->isLoggedIn()) {
                        throw new Exception("No autorizado");
                    }
                    $post['id_cuenta'] = $this->auth->getUserId();
                    $this->torneo->crear($post);
                    $this->success = "Torneo creado exitosamente";
                    break;
                    
                case 'agregar_equipo':
                    if (!$this->auth->isLoggedIn()) {
                        throw new Exception("No autorizado");
                    }
                    if (!$this->torneo->verificarPropiedad($post['id_torneo'], $this->auth->getUserId(), $this->auth->isAdmin())) {
                        throw new Exception("No tienes permisos");
                    }
                    $this->torneo->agregarEquipo($post['id_torneo'], $post['id_equipo']);
                    $this->success = "Equipo agregado al torneo";
                    break;
                    
                case 'agregar_equipos_multiples':
                    if (!$this->auth->isLoggedIn()) {
                        throw new Exception("No autorizado");
                    }
                    if (!$this->torneo->verificarPropiedad($post['id_torneo'], $this->auth->getUserId(), $this->auth->isAdmin())) {
                        throw new Exception("No tienes permisos");
                    }
                    $this->torneo->agregarEquiposMultiples($post['id_torneo'], $post['id_equipos']);
                    $this->success = "Equipos agregados exitosamente";
                    break;
                    
                case 'retirar_equipo':
                    if (!$this->auth->isLoggedIn()) {
                        throw new Exception("No autorizado");
                    }
                    if (!$this->torneo->verificarPropiedad($post['id_torneo'], $this->auth->getUserId(), $this->auth->isAdmin())) {
                        throw new Exception("No tienes permisos");
                    }
                    $this->torneo->retirarEquipo($post['id_torneo'], $post['id_equipo']);
                    $this->success = "Equipo retirado del torneo";
                    break;
                    
                case 'actualizar_resultado':
                    if (!$this->auth->isLoggedIn()) {
                        throw new Exception("No autorizado");
                    }
                    $this->torneo->actualizarResultado($post['id_partida'], $post['resultado']);
                    $this->success = "Resultado actualizado exitosamente";
                    break;
                    
                case 'actualizar_multiples_resultados':
                    if (!$this->auth->isLoggedIn()) {
                        throw new Exception("No autorizado");
                    }
                    $this->torneo->actualizarMultiplesResultados($post['resultados'] ?? []);
                    $this->success = "Resultados actualizados exitosamente";
                    break;
                    
                case 'eliminar_torneo':
                    if (!$this->auth->isAdmin()) {
                        throw new Exception("Solo administradores");
                    }
                    $this->torneo->eliminar($post['id_torneo']);
                    $this->success = "Torneo eliminado exitosamente";
                    break;

                // ── SOLICITUDES DE TORNEO ─────────────────────────────────────
                case 'solicitar_torneo':
                    if (!$this->auth->isLoggedIn()) {
                        throw new Exception("No autorizado");
                    }
                    $this->torneo->solicitarUnirse(
                        $post['id_torneo'],
                        $post['id_equipo'],
                        $this->auth->getUserId(),
                        trim($post['mensaje'] ?? '')
                    );
                    $this->success = "Solicitud enviada. El organizador la revisará pronto.";
                    break;

                case 'aceptar_solicitud_torneo':
                    if (!$this->auth->isLoggedIn()) {
                        throw new Exception("No autorizado");
                    }
                    $this->torneo->aceptarSolicitudTorneo($post['id_solicitud'], $this->auth->getUserId());
                    $this->success = "Equipo aceptado e inscrito en el torneo.";
                    break;

                case 'rechazar_solicitud_torneo':
                    if (!$this->auth->isLoggedIn()) {
                        throw new Exception("No autorizado");
                    }
                    $this->torneo->rechazarSolicitudTorneo($post['id_solicitud'], $this->auth->getUserId());
                    $this->success = "Solicitud rechazada.";
                    break;

                case 'cancelar_solicitud_torneo':
                    if (!$this->auth->isLoggedIn()) {
                        throw new Exception("No autorizado");
                    }
                    $this->torneo->cancelarSolicitudTorneo($post['id_solicitud'], $post['id_equipo']);
                    $this->success = "Solicitud cancelada.";
                    break;
                // ─────────────────────────────────────────────────────────────

                case 'generar_ronda':
                    if (!$this->auth->isLoggedIn()) {
                        throw new Exception("No autorizado");
                    }
                    $this->ronda->generar($post['id_torneo'], $post['numero_ronda'], $post['fecha_ronda']);
                    $this->success = "Ronda generada exitosamente";
                    break;

                case 'eliminar_cuenta':
                    if (!$this->auth->isAdmin()) {
                        throw new Exception("Solo administradores");
                    }
                    $this->admin->eliminarCuenta($post['id_cuenta']);
                    $this->success = "Cuenta eliminada exitosamente";
                    break;
                
                default:
                    throw new Exception("Acción no reconocida: " . $action);
            }
            
            return ['success' => $this->success];
            
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return ['error' => $this->error];
        }
    }
    
    private function login($post) {
        if ($this->auth->login($post['usuario'], $post['contrasena'])) {
            return ['redirect' => 'index.php'];
        } else {
            $this->error = "Usuario o contraseña incorrectos";
            return ['error' => $this->error];
        }
    }
    
    private function registro($post) {
        $this->auth->registrarUsuario($post);
        return ['redirect' => 'index.php'];
    }
}