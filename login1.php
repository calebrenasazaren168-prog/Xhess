<?php
// Conexión y verificación de correo
$correo = "";
$correoExiste = false;
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["correo"])) {

    $correo = trim($_POST["correo"]);

    if (!empty($correo)) {

        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "xhess";

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare("SELECT correo FROM cuentas WHERE correo = :correo LIMIT 1");
            $stmt->bindParam(':correo', $correo);
            $stmt->execute();

            $correoExiste = $stmt->rowCount() > 0;

        } catch(PDOException $e) {
            $mensaje = "Error en la conexión: " . $e->getMessage();
        }
    }

    // Si el correo existe, redirigir
    if ($correoExiste) {
        header("Location: login2.php?correo=" . urlencode($correo));
        exit();
    } else {
        $mensaje = "Este correo no está registrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceder a tu cuenta</title>
</head>
<body>

    <h1>Acceder a tu cuenta</h1>

    <?php if(!empty($mensaje)): ?>
        <p style="color:red;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <div>
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
