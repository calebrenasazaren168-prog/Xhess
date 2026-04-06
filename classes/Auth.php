<?php
require_once 'Database.php';
require_once __DIR__ . '/EmailService.php';

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

            // Bloquear acceso si no ha verificado su correo
            if (empty($cuenta['email_verificado']) || $cuenta['email_verificado'] == 0) {
                throw new Exception(
                    "Debes verificar tu correo electrónico antes de iniciar sesión. " .
                    "Revisa tu bandeja de entrada y la carpeta de spam."
                );
            }

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
            // Verificar que no exista el usuario o correo
            $stmt = $this->pdo->prepare("SELECT id_cuenta FROM cuentas WHERE usuario = ? OR correo = ?");
            $stmt->execute([$datos['usuario'], $datos['correo']]);
            if ($stmt->fetch()) {
                throw new Exception("El usuario o correo electrónico ya existen");
            }

            // Generar token de verificación seguro (64 caracteres hex)
            $token      = bin2hex(random_bytes(32));
            $expiracion = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // Insertar cuenta con email_verificado = 0
            $hash = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("
                INSERT INTO cuentas 
                    (usuario, contrasena, correo, id_rol, email_verificado, token_verificacion, token_expiracion) 
                VALUES (?, ?, ?, 3, 0, ?, ?)
            ");
            $stmt->execute([$datos['usuario'], $hash, $datos['correo'], $token, $expiracion]);
            $id_cuenta = $this->pdo->lastInsertId();
            
            // Insertar jugador asociado
            $nombre   = $datos['nombre']   ?? $datos['usuario'];
            $apellido = $datos['apellido'] ?? '';
            $stmt = $this->pdo->prepare("
                INSERT INTO jugadores (nombre, apellido, rating, id_cuenta, activo) 
                VALUES (?, ?, 1200, ?, 1)
            ");
            $stmt->execute([$nombre, $apellido, $id_cuenta]);
            
            $this->pdo->commit();

            // Enviar correo de verificación DESPUÉS de guardar en BD
            $emailService = new EmailService();
            $emailService->enviarVerificacion($datos['correo'], $datos['usuario'], $token);

            // NO iniciar sesión — el usuario debe verificar su correo primero
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