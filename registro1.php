<?php
// Verificar si el correo ya existe
$correo = "";
$correoExiste = false;
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["correo"])) {
    $correo = trim($_POST["correo"]);
    
    if (!empty($correo)) {
        // Conexión a la base de datos
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "xhess";
        
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $stmt = $conn->prepare("SELECT correo FROM cuentas WHERE correo = :correo");
            $stmt->bindParam(':correo', $correo);
            $stmt->execute();
            
            $correoExiste = $stmt->rowCount() > 0;
            
            if ($correoExiste) {
                $mensaje = "Este correo ya está registrado";
            } else {
                // Redirigir al segundo paso del registro
                header("Location: registro2.php?correo=" . urlencode($correo));
                exit();
            }
            
        } catch(PDOException $e) {
            $mensaje = "Error de conexión: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear tu cuenta</title>
</head>
<body>
    <h1>Crear tu cuenta</h1>
    
    <div>
        <?php if (!empty($mensaje)): ?>
            <p style="color: red;"><?php echo $mensaje; ?></p>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="email" 
                   name="correo" 
                   id="correo" 
                   placeholder="Correo electrónico" 
                   value="<?php echo htmlspecialchars($correo); ?>" 
                   required>
            
            <button type="submit" id="btnContinuar" disabled>
                Continuar
            </button>
        </form>
    </div>

    <script>
        document.getElementById('correo').addEventListener('input', function() {
            var correo = this.value.trim();
            var boton = document.getElementById('btnContinuar');
            
            boton.disabled = correo.length === 0;
        });
    </script>
</body>
</html>