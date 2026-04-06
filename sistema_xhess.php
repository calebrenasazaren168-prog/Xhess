<?php
session_start();

require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/JugadorManager.php';
require_once __DIR__ . '/classes/EquipoManager.php';
require_once __DIR__ . '/classes/TorneoManager.php';
require_once __DIR__ . '/classes/RondaManager.php';
require_once __DIR__ . '/classes/RatingCalculator.php';
require_once __DIR__ . '/classes/AdminManager.php';
require_once __DIR__ . '/handlers/ActionHandler.php';

$error = null;
$success = null;

class XhessSystem {
    private $auth;
    private $jugador;
    private $equipo;
    private $torneo;
    private $ronda;
    private $admin;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->jugador = new JugadorManager();
        $this->equipo = new EquipoManager();
        $this->torneo = new TorneoManager();
        $this->ronda = new RondaManager();
        $this->admin = new AdminManager();
    }
    
    public function login($usuario, $contrasena) { 
        return $this->auth->login($usuario, $contrasena); 
    }
    
    public function logout() { 
        $this->auth->logout(); 
    }
    
    public function registrarUsuario($datos) { 
        return $this->auth->registrarUsuario($datos); 
    }
    
    public function isLoggedIn() { 
        return $this->auth->isLoggedIn(); 
    }
    
    public function isAdmin() { 
        return $this->auth->isAdmin(); 
    }
    
    public function isJugador() { 
        return $this->auth->isJugador(); 
    }
    
    public function unirseAEquipo($id_jugador, $id_equipo) { 
        return $this->jugador->unirseAEquipo($id_jugador, $id_equipo); 
    }
    
    public function salirseDeEquipo($id_jugador) { 
        return $this->jugador->salirseDeEquipo($id_jugador); 
    }
    
    public function getJugadoresPublicos() { 
        return $this->jugador->obtenerPublicos(); 
    }
    
    public function getJugadoresRegistrados() { 
        return $this->jugador->obtenerRegistrados(); 
    }
    
    public function getEquipoActualJugador($id_jugador) {
        return $this->jugador->obtenerJugador($id_jugador);
    }
    
    public function crearEquipo($datos) { 
        if (!$this->isAdmin() && !$this->isJugador()) {
            throw new Exception("No tienes permisos para crear equipos");
        }
        
        $datos['id_creador'] = $_SESSION['user_id'];
        $result = $this->equipo->crear($datos);
        
        // Si es jugador, asignarlo automáticamente
        if ($result && $this->isJugador() && isset($_SESSION['jugador_id'])) {
            $id_equipo = Database::getInstance()->getConnection()->lastInsertId();
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("UPDATE jugadores SET id_equipo = ? WHERE id_jugador = ?");
            $stmt->execute([$id_equipo, $_SESSION['jugador_id']]);
        }
        
        return $result;
    }
    
    public function eliminarEquipo($id_equipo) { 
        if (!$this->isAdmin()) {
            throw new Exception("Solo administradores pueden eliminar equipos");
        }
        return $this->equipo->eliminar($id_equipo); 
    }
    
    public function getEquiposDisponibles() { 
        return $this->equipo->obtenerTodos(); 
    }
    
    public function getEquiposDisponiblesParaUnirse() {
        return $this->equipo->obtenerTodos();
    }
    
    public function getEquiposPorClub($id_club) { 
        return $this->equipo->obtenerPorClub($id_club); 
    }
    
    public function getJugadoresPorEquipo($id_equipo) { 
        return $this->equipo->obtenerJugadores($id_equipo); 
    }
    
    public function existeEquipoEnClub($nombre, $id_club, $excluir_id = null) { 
        return $this->equipo->existeEnClub($nombre, $id_club, $excluir_id); 
    }
    
    public function crearTorneo($datos) { 
        return $this->torneo->crear($datos); 
    }
    
    public function eliminarTorneo($id_torneo) { 
        if (!$this->isAdmin()) {
            throw new Exception("Solo administradores pueden eliminar torneos");
        }
        return $this->torneo->eliminar($id_torneo); 
    }
    
    public function getTorneosPublicos() { 
        return $this->torneo->obtenerPublicos(); 
    }
    
    public function getMisTorneos($id_cuenta) { 
        return $this->torneo->obtenerMisTorneos($id_cuenta); 
    }
    
    public function getTorneoCompleto($id_torneo) { 
        return $this->torneo->obtenerCompleto($id_torneo); 
    }
    
    public function agregarEquipoATorneo($id_torneo, $id_equipo) {
        if (!$this->verificarPropiedadTorneo($id_torneo, $_SESSION['user_id'])) {
            return false;
        }
        return $this->torneo->agregarEquipo($id_torneo, $id_equipo); 
    }
    
    public function agregarEquiposMultiplesATorneo($id_torneo, $id_equipos) { 
        if (!$this->verificarPropiedadTorneo($id_torneo, $_SESSION['user_id'])) {
            return false;
        }
        return $this->torneo->agregarEquiposMultiples($id_torneo, $id_equipos); 
    }
    
    public function retirarEquipoDeTorneo($id_torneo, $id_equipo) { 
        if (!$this->verificarPropiedadTorneo($id_torneo, $_SESSION['user_id'])) {
            return false;
        }
        return $this->torneo->retirarEquipo($id_torneo, $id_equipo); 
    }
    
    public function getEquiposTorneo($id_torneo) { 
        return $this->torneo->obtenerEquipos($id_torneo); 
    }
    
    public function getClasificacionEquipos($id_torneo) { 
        return $this->torneo->obtenerClasificacionEquipos($id_torneo); 
    }
    
    public function getClasificacionJugadores($id_torneo) { 
        return $this->torneo->obtenerClasificacionJugadores($id_torneo); 
    }
    
    public function actualizarResultadoPartida($id_partida, $resultado) { 
        return $this->torneo->actualizarResultado($id_partida, $resultado); 
    }
    
    public function actualizarMultiplesResultados($resultados) { 
        return $this->torneo->actualizarMultiplesResultados($resultados); 
    }
    
    public function tieneRondasGeneradas($id_torneo) { 
        return $this->torneo->tieneRondas($id_torneo); 
    }
    
    public function actualizarEstadoTorneo($id_torneo) { 
        return $this->torneo->actualizarEstado($id_torneo); 
    }
    
    public function verificarPropiedadTorneo($id_torneo, $id_cuenta) {
        return $this->torneo->verificarPropiedad($id_torneo, $id_cuenta, $this->isAdmin());
    }
    
    public function generarRonda($id_torneo, $numero_ronda, $fecha_ronda) { 
        return $this->ronda->generar($id_torneo, $numero_ronda, $fecha_ronda); 
    }
    
    public function getRondasTorneo($id_torneo) { 
        return $this->ronda->obtenerRondas($id_torneo); 
    }
    
    public function getPartidasRonda($id_ronda) { 
        return $this->ronda->obtenerPartidas($id_ronda); 
    }
    
    public function getProximaRonda($id_torneo) { 
        return $this->ronda->obtenerProximaRonda($id_torneo); 
    }
    
    public function getEmparejamientosTorneo($id_torneo) {
        return $this->ronda->obtenerEmparejamientos($id_torneo);
    }
    
    public function getTodosTorneos() { 
        if (!$this->isAdmin()) return [];
        return $this->admin->obtenerTodosTorneos(); 
    }
    
    public function getTodasCuentas() { 
        if (!$this->isAdmin()) return [];
        return $this->admin->obtenerTodasCuentas(); 
    }
    
    public function eliminarCuenta($id_cuenta) { 
        if (!$this->isAdmin()) {
            throw new Exception("Solo administradores pueden eliminar cuentas");
        }
        return $this->admin->eliminarCuenta($id_cuenta); 
    }
    
    public function adminAsignarJugadorAEquipo($id_jugador, $id_equipo) { 
        if (!$this->isAdmin()) {
            throw new Exception("Solo administradores pueden asignar jugadores");
        }
        return $this->admin->asignarJugadorAEquipo($id_jugador, $id_equipo); 
    }
    
    public function adminQuitarJugadorDeEquipo($id_jugador) { 
        if (!$this->isAdmin()) {
            throw new Exception("Solo administradores pueden quitar jugadores");
        }
        return $this->admin->quitarJugadorDeEquipo($id_jugador); 
    }
    
    public function getClubes() {
        $pdo = Database::getInstance()->getConnection();
        return $pdo->query("SELECT * FROM clubes ORDER BY nombre")->fetchAll();
    }
    
    public function actualizarRatingJugador($id_jugador, $resultado, $rating_oponente) {
        $calc = new RatingCalculator();
        return $calc->actualizar($id_jugador, $resultado, $rating_oponente);
    }

    public function getSolicitudesJugador($id_jugador) {
        return $this->equipo->obtenerSolicitudesJugador($id_jugador);
    }

    public function getSolicitudesEquipo($id_equipo) {
        return $this->equipo->obtenerTodasSolicitudes($id_equipo);
    }

    public function getEquiposCapitan($id_cuenta) {
        return $this->equipo->obtenerEquiposCapitan($id_cuenta);
    }

    public function contarSolicitudesPendientes($id_cuenta) {
        return $this->equipo->contarSolicitudesPendientesCapitan($id_cuenta);
    }
    
        public function solicitarTorneo($id_torneo, $id_cuenta, $id_equipo, $mensaje = '') {
        return $this->torneo->solicitarUnirse((int)$id_torneo, (int)$id_cuenta, $id_equipo ? (int)$id_equipo : null, $mensaje);
    }
 
    public function aceptarSolicitudTorneo($id_solicitud) {
        return $this->torneo->aceptarSolicitudTorneo((int)$id_solicitud, (int)$_SESSION['user_id'], $this->isAdmin());
    }
 
    public function rechazarSolicitudTorneo($id_solicitud) {
        return $this->torneo->rechazarSolicitudTorneo((int)$id_solicitud, (int)$_SESSION['user_id'], $this->isAdmin());
    }
 
    public function cancelarSolicitudTorneo($id_solicitud) {
        return $this->torneo->cancelarSolicitudTorneo((int)$id_solicitud, (int)$_SESSION['user_id']);
    }
 
    public function getSolicitudesTorneo($id_torneo) {
        return $this->torneo->obtenerSolicitudesTorneo((int)$id_torneo);
    }
 
    public function getMisSolicitudesTorneos($id_cuenta) {
        return $this->torneo->obtenerMisSolicitudesTorneos((int)$id_cuenta);
    }
 
    public function contarSolicitudesPendientesTorneos($id_cuenta) {
        return $this->torneo->contarSolicitudesPendientesTorneos((int)$id_cuenta);
    }

}

$sistema = new XhessSystem();

if ($_POST) {
    $auth = new Auth();
    $jugador = new JugadorManager();
    $equipo = new EquipoManager();
    $torneo = new TorneoManager();
    
    $handler = new ActionHandler($auth, $jugador, $equipo, $torneo);
    $result = $handler->process($_POST);
    
    // Manejar redirecciones
    if (isset($result['redirect'])) {
        header("Location: " . $result['redirect']);
        exit;
    }
    
    // Manejar mensajes
    if (isset($result['error'])) {
        $error = $result['error'];
    }
    if (isset($result['success'])) {
        $success = $result['success'];
    }
}
?>