<?php
// Configuración de conexión a la base de datos
$mysqli = new mysqli("localhost", "eurocom_bd", "1d1HBQ(AeBx}", "eurocom_bd_dashboard");
if ($mysqli->connect_error) {
    die("Conexión fallida: " . $mysqli->connect_error);
}

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


foreach ($planteles as $plantel) {
    if ($plantel['estatus'] != 1) continue;

    $id_plantel = $plantel['id'];


    $curl = curl_init();

    
    
    
    curl_setopt_array($curl, array(
  CURLOPT_URL => "https://console.creativesnippet.com/school/api/sendica.php?modulo=edocuenta_adeudos&id_plantel={$id_plantel}",
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
    
    echo "<pre>";
//print_r($data);
echo "</pre>";

    foreach ($data as $matricula => $adeudos) {
        foreach ($adeudos as $item) {
            $stmt = $mysqli->prepare("
                INSERT INTO edocuenta_adeudos (
                    id_campusCreative, matricula, emisor, ciclo, categoria,
                    id_plantel, id_adeudo, id_concepto, concepto, importe, iva, descuentos,
                    recargos, abonado, fecha, estado
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    id_campusCreative = VALUES(id_campusCreative),
                    matricula = VALUES(matricula),
                    emisor = VALUES(emisor),
                    ciclo = VALUES(ciclo),
                    categoria = VALUES(categoria),
                    id_plantel = VALUES(id_plantel),
                    id_concepto = VALUES(id_concepto),
                    concepto = VALUES(concepto),
                    importe = VALUES(importe),
                    iva = VALUES(iva),
                    descuentos = VALUES(descuentos),
                    recargos = VALUES(recargos),
                    abonado = VALUES(abonado),
                    fecha = VALUES(fecha),
                    estado = VALUES(estado),
                    modificado = CURRENT_TIMESTAMP
            ");
            
            $stmt->bind_param("issisiiisdddddsi",
                $item['campus'],
                $matricula,
                $item['emisor'],
                $item['ciclo'],
                $item['categoria'],
                $item['id_plantel'],
                $item['id_adeudo'],
                $item['id_concepto'],
                $item['concepto'],
                $item['importe'],
                $item['iva'],
                $item['descuentos'],
                $item['recargos'],
                $item['abonado'],
                $item['fecha'],
                $item['estado']
            );

            $stmt->execute();
            $stmt->close();
        }
    }
}

$mysqli->close();
