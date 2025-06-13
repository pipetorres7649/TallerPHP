<?php
// Parámetros de conexión (se debe ajustar a tu servidor)
$host = "localhost";
$user = "root";
$pass = ""; 
$db = "pago";

// Crear conexión
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $salario_base = 737000;

    // Validación básica
    $nombre = $_POST['nombre'] ?? '';
    $cedula = $_POST['cedula'] ?? '';
    $autos = isset($_POST['autosVendidos']) ? intval($_POST['autosVendidos']) : 0;
    $total_ventas = isset($_POST['valorVenta']) ? floatval($_POST['valorVenta']) : 0;

    $comision = $autos * 50000;
    $porcentaje = $total_ventas * 0.05;
    $salario_total = $salario_base + $comision + $porcentaje;
    
     // Insertar en la base de datos
    $sql = "INSERT INTO pagos_vendedores 
        (nombre, cedula, autos_vendidos, total_ventas, salario_base, comision_autos, comision_ventas, salario_total)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssiddddd",
        $nombre,
        $cedula,
        $autos,
        $total_ventas,
        $salario_base,
        $comision,
        $porcentaje,
        $salario_total
    );
    $stmt->execute();
    $stmt->close();
    ?>

    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Resultado Salario</title>
        <link rel="stylesheet" href="estilosSalario.css">
    </head>
    <body>
        <div class="salario-form-container">
            <div class="salario-form" style="max-width:500px">
                <h2>Resumen de Salario</h2>
                <p><b>Nombre:</b> <?php echo htmlspecialchars($nombre); ?></p>
                <p><b>Cédula:</b> <?php echo htmlspecialchars($cedula); ?></p>
                <hr>
                <p><b>Salario básico:</b> $<?php echo number_format($salario_base, 2, ',', '.'); ?></p>
                <p><b>Comisión por autos vendidos (<?php echo $autos; ?> x $50.000):</b> $<?php echo number_format($comision, 2, ',', '.'); ?></p>
                <p><b>Comisión 5% de ventas ($<?php echo number_format($total_ventas, 2, ',', '.'); ?>):</b> $<?php echo number_format($porcentaje, 2, ',', '.'); ?></p>
                <hr>
                <h3 style="color:#155185">Salario total: $<?php echo number_format($salario_total, 2, ',', '.'); ?></h3>
                <a href="formularioSalario.html" style="display:inline-block;margin-top:18px;">&#8592; Volver al formulario</a>
            </div>
        </div>
    </body>
    </html>
    <?php
} else {
    // Si se abre directamente, redirige al formulario
    header("Location: formularioSalario.html");
    exit();
}
?>