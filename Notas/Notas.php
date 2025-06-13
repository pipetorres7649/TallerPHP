<?php
// Parámetros de conexión (ajusta a tu servidor)
$host = "localhost";
$user = "root";
$pass = ""; // Tu contraseña
$db = "colegio";

// Crear conexión
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$parcial1 = $_POST['parcial1'];
$parcial2 = $_POST['parcial2'];
$parcial3 = $_POST['parcial3'];
$examenFinal = $_POST['examenFinal'];
$trabajoFinal = $_POST['trabajoFinal'];

$promParciales = ($parcial1 + $parcial2 + $parcial3) / 3;
$notaFinal = ($promParciales * 0.35) + ($examenFinal * 0.35) + ($trabajoFinal * 0.30);
$aprobado = $notaFinal >= 3;


// ... (Código de conexión y cálculo de nota)

$sql = "INSERT INTO notas (parcial1, parcial2, parcial3, examenFinal, trabajoFinal, notaFinal)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("dddddd", $parcial1, $parcial2, $parcial3, $examenFinal, $trabajoFinal, $notaFinal);
$stmt->execute();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado - Nota Final</title>
    <link rel="stylesheet" href="Estilosnotas.css">
    <style>
        body {
            background: #e8f0fe;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .resultado-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px #b5d0fa33;
            padding: 36px 34px 30px 34px;
            width: 340px;
            text-align: center;
        }
        .resultado-card h2 {
            color: #1858a8;
            margin-bottom: 16px;
            font-size: 1.25em;
        }
        .nota-final {
            font-size: 2.4em;
            font-weight: bold;
            color: #1a73e8;
            margin-bottom: 10px;
            letter-spacing: 1px;
        }
        .estado {
            font-size: 1.15em;
            font-weight: 500;
            margin-bottom: 18px;
            color: <?php echo $aprobado ? "#1bb954" : "#e13a3a"; ?>;
        }
        .detalle {
            background: #f4f9ff;
            border-radius: 8px;
            margin: 16px 0 0 0;
            padding: 12px 14px;
            text-align: left;
            font-size: 1em;
            color: #2c466f;
        }
        .volver-btn {
            display: inline-block;
            margin-top: 22px;
            background: #1a73e8;
            color: #fff;
            padding: 11px 25px;
            border: none;
            border-radius: 6px;
            font-size: 1em;
            font-weight: bold;
            text-decoration: none;
            transition: background 0.18s;
        }
        .volver-btn:hover {
            background: #155ab6;
            color: #fff;
        }
    </style>
</head>
<body>
    
    <div class="resultado-card">
        <h2>Resultado de la Nota Final</h2>
        <div class="nota-final"><?php echo number_format($notaFinal, 2); ?></div>
        <div class="estado">
            <?php echo $aprobado ? "¡Aprobó! 🎉" : "No aprobó 😔"; ?>
        </div>
        <div class="detalle">
            <b>Promedio parciales:</b> <?php echo number_format($promParciales, 2); ?><br>
            <b>Examen final:</b> <?php echo number_format($examenFinal, 2); ?><br>
            <b>Trabajo final:</b> <?php echo number_format($trabajoFinal, 2); ?><br>
        </div>
        <a href="Formularionotas.html" class="volver-btn">&#8592; Volver</a>
    </div>
</body>
</html>
    

    


