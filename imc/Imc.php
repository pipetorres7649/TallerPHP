<?php
// Parámetros de conexión (se debe ajustar a tu servidor)
$host = "localhost";
$user = "root";
$pass = ""; 
$db = "imc";

// Cogido de conexión se crea conexión
$conn = new mysqli($host, $user, $pass, $db);

// Se verifica la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
function calcular_categoria_imc($imc) {
    if ($imc <= 18.5) {
        return "Por debajo del peso";
    } elseif ($imc <= 24.9) {
        return "Saludable";
    } elseif ($imc <= 29.9) {
        return "Con sobrepeso";
    } elseif ($imc <= 39.9) {
        return "Obeso";
    } else {
        return "Obesidad mórbida";
    }
}

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$peso = isset($_POST['peso']) ? floatval($_POST['peso']) : 0;
$estatura = isset($_POST['estatura']) ? floatval($_POST['estatura']) : 0;
$imc = '';
$categoria = '';
$error = '';

if ($nombre === '' || $peso <= 0 || $estatura <= 0) {
    $error = 'Por favor, ingrese valores válidos.';
} else {
    $imc = $peso / ($estatura * $estatura);
    $imc = round($imc, 2);
    $categoria = calcular_categoria_imc($imc);
    
    // Aquí se insertan los datos y resultados en la base de datos
    $sql = "INSERT INTO resultados_imc (nombre, peso, estatura, imc, categoria)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddds", $nombre, $peso, $estatura, $imc, $categoria);
    $stmt->execute();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado IMC</title>
    <link rel="stylesheet" href="estilosimc.css">
</head>
<body>
    <div class="imc-container">
        <?php if ($error): ?>
            <div class="imc-resultado" style="color:#a00;"><?php echo $error; ?></div>
            <a href="formularioimc.html" style="display:block;margin-top:18px;">Volver al formulario</a>
        <?php else: ?>
            <div class="imc-resultado">
                <p><strong>Nombre:</strong> <?php echo htmlspecialchars($nombre); ?></p>
                <p><strong>IMC:</strong> <?php echo $imc; ?></p>
                <p><strong>Categoría:</strong> <?php echo $categoria; ?></p>
            </div>
            <a href="formularioimc.html" style="display:block;margin-top:18px;">Calcular otro IMC</a>
        <?php endif; ?>
    </div>
</body>
</html> 

