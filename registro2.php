<?php
// Verificar que se haya pasado el correo por GET
if (!isset($_GET['correo']) || empty($_GET['correo'])) {
    header("Location: registro1.php");
    exit();
}

$correo = $_GET['correo'];
$mensaje = "";

// Procesar el formulario de registro
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["password"]) && isset($_POST["confirmar_password"])) {
    $password = trim($_POST["password"]);
    $confirmar_password = trim($_POST["confirmar_password"]);
    
    if (!empty($password) && !empty($confirmar_password)) {
        if ($password === $confirmar_password) {
            // Conexión a la base de datos
            $servername = "localhost";
            $username = "root";
            $password_bd = "";
            $dbname = "xhess";
            
            try {
                $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password_bd);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Insertar nuevo usuario
                $stmt = $conn->prepare("INSERT INTO cuentas (correo, password) VALUES (:correo, :password)");
                $stmt->bindParam(':correo', $correo);
                $stmt->bindParam(':password', $password);
                
                if ($stmt->execute()) {
                    echo "Cuenta creada exitosamente - Correo: " . htmlspecialchars($correo);
                    exit();
                } else {
                    $mensaje = "Error al crear la cuenta";
                }
                
            } catch(PDOException $e) {
                $mensaje = "Error de conexión: " . $e->getMessage();
            }
        } else {
            $mensaje = "Las contraseñas no coinc