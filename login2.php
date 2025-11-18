<?php
// Verificar que se haya pasado el correo por GET
if (!isset($_GET['correo']) || empty($_GET['correo'])) {
    header("Location: login1.php");
    exit();
}

$correo = $_GET['correo'];
$error = "";

// Procesar el formulario de contraseña
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["password"])) {
    $password = trim($_POST["password"]);
    
    if (!empty($password)) {
        // Conexión a la base de datos
        $servername = "localhost";
        $username = "root";
        $password_bd = "";
        $dbname = "xhess";
        
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password_bd);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Buscar el correo y contraseña correspondiente
            $stmt = $conn->prepare("SELECT password FROM cuentas WHERE correo = :correo");
            $stmt->bindParam(':correo', $correo);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $fila = $stmt->fetch(PDO::FETCH_ASSOC);
                $password_bd = $fila['password'];
                
                // Verificar contraseña (asumiendo que está en texto plano)
                // Si está hasheada, usar password_verify()
                if ($password === $password_bd) {
                    // Contraseña correcta - aquí puedes iniciar sesión o redirigir
                    echo "Login exitoso - Correo: " . htmlspecialchars($correo);
                    exit();
                } else {
                    $error = "Contraseña incorrecta";
                }
            } else {
                $error = "Correo no encontrado";
            }
            
        } catch(PDOException $e) {
            $error = "Error de conexión: " . $e->getMessage();
        }
    } else {
        $error = "Ingresa tu contraseña";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ingresa tu contraseña</title>
</head>
<body>
    <h1>Ingresa tu contraseña</h1>
    
    <div>
        <p>Correo: <?php echo htmlspecialchars($correo); ?></p>
        
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="password" 
                   name="password" 
                   id="password" 
                   placeholder="Contraseña" 
                   required>
            
            <button type="submit" id="btnAcceder">
                Acceder
            </button>
        </form>
    </div>

    <script>
        document.getElementById('password').addEventListener('input', function() {
            var password = this.value.trim();
            var boton = document.getElementById('btnAcceder');
            
            // Habilitar botón solo si hay contraseña
            boton.disabled = password.length === 0;
        });
    </script>
</body>
</html>