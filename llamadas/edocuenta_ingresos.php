

<?php
// Configuración de conexión a la base de datos
$mysqli = new mysqli("localhost", "eurocom_bd", "1d1HBQ(AeBx}", "eurocom_bd_dashboard");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

// Rango de fechas a consultar
$fecha_inicio = '2024-08-01';
$fecha_final = date('Y-m-d');

// Lista de planteles con su estatus
$planteles = [
    ['id' => 107, 'estatus' => 1],
    ['id' => 108, 'estatus' => 1],
    ['id' => 109, 'estatus' => 1],
    ['id' => 110, 'estatus' => 1],
    ['id' => 111, 'estatus' => 1],
    ['id' => 112, 'estatus' => 1],
    ['id' => 113, 'estatus' => 1],
    ['id' => 104, 'estatus' => 1],
    ['id' => 124, 'estatus' => 1],
    // agrega más planteles aquí
];
$contar=1; 
foreach ($planteles as $plantel) {
    if ($plantel['estatus'] != 1) continue;

    $id_plantel = $plantel['id'];
    $url = "https://console.creativesnippet.com/school/api/sendica.php?modulo=edocuenta_ingresos&id_plantel={$id_plantel}&fecha_inicio={$fecha_inicio}&fecha_final={$fecha_final}";

    $curl = curl_init();
    curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7InVzZXJfaWQiOjEsInVzZXJuYW1lIjoic2VuZGljYSJ9fQ.U_XjKcYO4XiXV_iLW6yFnB4NNj0TqzTZ-lRFKqOQd1g'
          ),
        ));

    $response = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($response, true);

   
    if (!$data || !is_array($data)) continue;

    foreach ($data as $matricula => $ingresos) {
        //echo "\n Plantel: $id_plantel - Matricula: $matricula - Total ingresos: " . count($ingresos) . "\n";

        if (!is_array($ingresos)) continue;

        foreach ($ingresos as $item) {
            echo "registro: ".$contar." Matricula: ".$matricula."- Insertando id_unico: " . $item['id_unico'] . "<br>";

            if (empty($item['id_unico'])) continue;
                
                $stmt = $mysqli->prepare("
                    INSERT INTO edocuenta_ingresos (
                        campus, matricula, emisor, ciclo, categoria,
                        id_plantel, id_adeudo, id_concepto, id_pago, nombre_cajero,
                        tipo_factura, tipo_pago, uuid_pue, uuid_ppd, uuid_nc,
                        errores_factura, concepto, importe, iva, descuentos,
                        recargos, fecha, estado, id_unico
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        campus = VALUES(campus),
                        matricula = VALUES(matricula),
                        emisor = VALUES(emisor),
                        ciclo = VALUES(ciclo),
                        categoria = VALUES(categoria),
                        id_plantel = VALUES(id_plantel),
                        id_adeudo = VALUES(id_adeudo),
                        id_concepto = VALUES(id_concepto),
                        id_pago = VALUES(id_pago),
                        nombre_cajero = VALUES(nombre_cajero),
                        tipo_factura = VALUES(tipo_factura),
                        tipo_pago = VALUES(tipo_pago),
                        uuid_pue = VALUES(uuid_pue),
                        uuid_ppd = VALUES(uuid_ppd),
                        uuid_nc = VALUES(uuid_nc),
                        errores_factura = VALUES(errores_factura),
                        concepto = VALUES(concepto),
                        importe = VALUES(importe),
                        iva = VALUES(iva),
                        descuentos = VALUES(descuentos),
                        recargos = VALUES(recargos),
                        fecha = VALUES(fecha),
                        estado = VALUES(estado)
                ");
                
                $stmt->bind_param("issssiiiissssssisddddsis",
                    $item['campus'],
                    $matricula,
                    $item['emisor'],
                    $item['ciclo'],
                    $item['categoria'],
                    $item['id_plantel'],
                    $item['id_adeudo'],
                    $item['id_concepto'],
                    $item['id_pago'],
                    $item['nombre_cajero'],
                    $item['tipo_factura'],
                    $item['tipo_pago'],
                    $item['uuid_pue'],
                    $item['uuid_ppd'],
                    $item['uuid_nc'],
                    $item['errores_factura'],
                    $item['concepto'],
                    $item['importe'],
                    $item['iva'],
                    $item['descuentos'],
                    $item['recargos'],
                    $item['fecha'],
                    $item['estado'],
                    $item['id_unico']
                );
                
                $stmt->execute();
                $afectadas = $stmt->affected_rows;
                if ($afectadas === 1) {
                    //echo "✅ Insertado nuevo (id_unico: {$item['id_unico']})<br>";
                } elseif ($afectadas === 2) {
                    echo "🔁 Actualizado (id_unico: {$item['id_unico']})<br>";
                } elseif ($afectadas === 0) {
                    echo "🟡 Dato duplicado sin cambios (ignorado): {$item['id_unico']}<br>";
                }
                
                
                $stmt->close();
                ++$contar;
        }
    }
}

$mysqli->close();






























