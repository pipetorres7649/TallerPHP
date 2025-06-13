<?php
// Parámetros de conexión (ajusta a tu servidor)
$host = "localhost";
$user = "root";
$pass = ""; // Tu contraseña
$db = "prestamo";

// Crear conexión
$conn = new mysqli($host, $user, $pass, $db);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}    
    
function calcularCuotaFija($monto, $tasa, $plazo) {
    $i = $tasa / 100;
    $n = $plazo;
    if ($i == 0) return $monto / $n;
    return $monto * ($i * pow(1 + $i, $n)) / (pow(1 + $i, $n) - 1);
}

function amortizacionFrances($monto, $tasa, $plazo) {
    $tabla = [];
    $cuota = calcularCuotaFija($monto, $tasa, $plazo);
    $saldo = $monto;
    $i = $tasa / 100;

    for ($mes = 1; $mes <= $plazo; $mes++) {
        $interes = $saldo * $i;
        $abonoCapital = $cuota - $interes;
        $nuevoSaldo = $saldo - $abonoCapital;
        if ($nuevoSaldo < 0) $nuevoSaldo = 0;
        $tabla[] = [
            'no' => $mes,
            'saldo_anterior' => $saldo,
            'cuota' => $cuota,
            'interes' => $interes,
            'abono_capital' => $abonoCapital,
            'nuevo_saldo' => $nuevoSaldo
        ];
        $saldo = $nuevoSaldo;
    }
    return [$cuota, $tabla];
}

$mensaje = "";
$isPost = $_SERVER['REQUEST_METHOD'] === "POST";
$cedula = $_POST['cedula'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$monto = isset($_POST['monto']) ? floatval($_POST['monto']) : 0;
$tasa = isset($_POST['tasa']) ? floatval($_POST['tasa']) : 0;
$plazo = isset($_POST['plazo']) ? intval($_POST['plazo']) : 0;

$errores = [];
if ($isPost) {
    if ($cedula == '' || $nombre == '' || $monto <= 0 || $tasa < 0 || $plazo <= 0) {
        $errores[] = "Todos los campos son obligatorios y deben ser válidos.";
    }else {
        // Guardar los datos en la tabla 'calculadora'
        $sql = "INSERT INTO calculadora (cedula, nombre, monto, tasa, plazo) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssddi", $cedula, $nombre, $monto, $tasa, $plazo);
        $stmt->execute();
         $calculadora_id = $stmt->insert_id; // Guarda el id del crédito
        $stmt->close();
        
         // Calcular la tabla de cuotas
        list($cuota, $tabla) = amortizacionFrances($monto, $tasa, $plazo);

        // Guardar las cuotas en la tabla 'cuotas_calculadora'
        $sql_cuota = "INSERT INTO cuotas_calculadora 
            (calculadora_id, numero_cuota, saldo_anterior, cuota, interes, abono_capital, nuevo_saldo)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_cuota = $conn->prepare($sql_cuota);

        foreach ($tabla as $fila) {
            $stmt_cuota->bind_param(
                "iiddddd",
                $calculadora_id,
                $fila['no'],
                $fila['saldo_anterior'],
                $fila['cuota'],
                $fila['interes'],
                $fila['abono_capital'],
                $fila['nuevo_saldo']
            );
            $stmt_cuota->execute();
        }
        $stmt_cuota->close();
    }
        
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tabla de Amortización</title>
    <link rel="stylesheet" href="estilosAmortizacion.css">
</head>
<body>
    <div class="amortizacion-form-container">
        <form action="amortizacion.php" method="post" class="amortizacion-form">
            <h2>Calculadora de Amortización</h2>
            <label for="cedula">Cédula del cliente:</label>
            <input type="text" id="cedula" name="cedula" required value="<?php echo htmlspecialchars($cedula); ?>">

            <label for="nombre">Nombre del cliente:</label>
            <input type="text" id="nombre" name="nombre" required value="<?php echo htmlspecialchars($nombre); ?>">

            <label for="monto">Monto del crédito ($):</label>
            <input type="number" id="monto" name="monto" min="1" required value="<?php echo htmlspecialchars($monto); ?>">

            <label for="tasa">Tasa de interés mensual (%):</label>
            <input type="number" id="tasa" name="tasa" min="0" step="0.01" required value="<?php echo htmlspecialchars($tasa); ?>">

            <label for="plazo">Plazo (meses):</label>
            <input type="number" id="plazo" name="plazo" min="1" required value="<?php echo htmlspecialchars($plazo); ?>">

            <button type="submit">Generar tabla</button>
        </form>
    </div>

    <?php if ($isPost && !$errores): ?>
    <div class="amortizacion-resultado">
        <div class="datos-cliente">
            <span class="label"><b>Cédula:</b></span> <span class="value"><?php echo htmlspecialchars($cedula); ?></span><br>
            <span class="label"><b>Cliente:</b></span> <span class="value"><?php echo htmlspecialchars($nombre); ?></span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>No. Cuota</th>
                    <th>Saldo Anterior</th>
                    <th>Valor Cuota Fija</th>
                    <th>
                        Abono Interés
                        <span class="subtitulo">(Saldo anterior * tasa de interés / 100)</span>
                    </th>
                    <th>
                        Abono Capital
                        <span class="subtitulo">(cuota fija – abono interés)</span>
                    </th>
                    <th>
                        Nuevo Saldo
                        <span class="subtitulo">(saldo anterior – abono capital)</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                list($cuota, $tabla) = amortizacionFrances($monto, $tasa, $plazo);
                foreach ($tabla as $fila): ?>
                <tr>
                    <td><?php echo $fila['no']; ?></td>
                    <td><?php echo number_format($fila['saldo_anterior'], 2, ',', '.'); ?></td>
                    <td><?php echo number_format($fila['cuota'], 2, ',', '.'); ?></td>
                    <td><?php echo number_format($fila['interes'], 2, ',', '.'); ?></td>
                    <td><?php echo number_format($fila['abono_capital'], 2, ',', '.'); ?></td>
                    <td><?php echo number_format($fila['nuevo_saldo'], 2, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</body>
</html>
