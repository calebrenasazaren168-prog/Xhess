<?php
require_once 'Database.php';

class Auth {
    private $pdo;
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function login($usuario, $contrasena) {
        $stmt = $this->pdo->prepare("SELECT * FROM cuentas WHERE usuario = ? AND activo = 1");
        $stmt->execute([$usuario]);
        $cuenta = $stmt->fetch();
        
        if ($cuenta && password_verify($contrasena, $cuenta['contrasena'])) {
            $this->establecerSesion($cuenta);
            return true;
        }
        return false;
    }
    
    private function establecerSesion($cuenta) {
        $_SESSION['usuario'] = $cuenta;
        $_SESSION['user_id'] = $cuenta['id_cuenta'];
        $_SESSION['username'] = $cuenta['usuario'];
        $_SESSION['user_role'] = $cuenta['id_rol'];
        
        $stmt = $this->pdo->prepare("SELECT id_jugador FROM jugadores WHERE id_cuenta = ?");
        $stmt->execute([$cuenta['id_cuenta']]);
        $jugador = $stmt->fetch();
        if ($jugador) {
            $_SESSION['jugador_id'] = $jugador['id_jugador'];
        }
    }
    
    public function logout() {
        session_destroy();
    }
    
    public function registrarUsuario($datos) {
        $this->pdo->beginTransaction();
        try {

            $stmt = $this->pdo->prepare("SELECT id_cuenta FROM cuentas WHERE usuario = ? OR correo = ?");
            $stmt->execute([$datos['usuario'], $datos['correo']]);
            if ($stmt->fetch()) {
                throw new Exception("El usuario o correo electrónico ya existen");
            }
            
            $hash = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO cuentas (usuario, contrasena, correo, id_rol) VALUES (?, ?, ?, 3)");
            $stmt->execute([$datos['usuario'], $hash, $datos['correo']]);
            $id_cuenta = $this->pdo->lastInsertId();
            
            $nombre = $datos['nombre'] ?? $datos['usuario'];
            $apellido = $datos['apellido'] ?? '';
            $stmt = $this->pdo->prepare("INSERT INTO jugadores (nombre, apellido, rating, id_cuenta, activo) VALUES (?, ?, 1200, ?, 1)");
            $stmt->execute([$nombre, $apellido, $id_cuenta]);
            $id_jugador = $this->pdo->lastInsertId();
            
            $this->pdo->commit();
            
            $_SESSION['user_id'] = $id_cuenta;
            $_SESSION['username'] = $datos['usuario'];
            $_SESSION['user_role'] = 3;
            $_SESSION['jugador_id'] = $id_jugador;
            
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function isAdmin() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1;
    }
    
    public function isJugador() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 3;
    }
    
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public function getJugadorId() {
        return $_SESSION['jugador_id'] ?? null;
    }
}