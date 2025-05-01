<?php

$campus = [
    107 => 1,
    108 => 1,
    109 => 1,
    110 => 1,
    111 => 1,
    112 => 1,
    113 => 1,
    104 => 1,
    124 => 1,
];



$id_carga = date('YmdHis');

// Conexión a la base de datos con PDO
$pdo = new PDO('mysql:host=localhost;dbname=eurocom_bd_dashboard;charset=utf8mb4', 'eurocom_bd', '1d1HBQ(AeBx}');



foreach ($campus as $id_campus => $activo) {
    if ($activo != 1) continue;

// Llamada a la API
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://console.creativesnippet.com/school/api/sendica.php?modulo=alumnos&ciclo=2024&id_plantel={$id_campus}",
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

    if (!is_array($data)) {
        echo "�7�4 Error con campus $id_campus. Respuesta inv��lida:\n";
        echo $response . "\n\n";
        continue;
    }

    foreach ($data as $alumno) {
        $stmt = $pdo->prepare("
            INSERT INTO alumnado (
                id_carga, id_campusCreative, matricula, nombre_hijo, apellido_paterno, 
                apellido_materno, sexo, cicloescolar, grupo, matricula_sep, 
                foto, ultima_actualizacion, activo,seccion,grado,fecha_ingreso,fecha_baja
            ) VALUES (
                :id_carga, :id_campus, :matricula, :nombre_hijo, :apellido_paterno, 
                :apellido_materno, :sexo, :cicloescolar, :grupo, :matricula_sep, 
                :foto, :ultima_actualizacion, :activo,:seccion,:grado,:fecha_ingreso,:fecha_baja
            )
            ON DUPLICATE KEY UPDATE
                nombre_hijo = VALUES(nombre_hijo),
                apellido_paterno = VALUES(apellido_paterno),
                apellido_materno = VALUES(apellido_materno),
                sexo = VALUES(sexo),
                cicloescolar = VALUES(cicloescolar),
                grupo = VALUES(grupo),
                matricula_sep = VALUES(matricula_sep),
                foto = VALUES(foto),
                ultima_actualizacion = VALUES(ultima_actualizacion),
                activo = VALUES(activo),
                seccion = VALUES(seccion),
                grado = VALUES(grado),
                fecha_ingreso = VALUES(fecha_ingreso),
                fecha_baja = VALUES(fecha_baja)
        ");

        $stmt->execute([
            ':id_carga' => $id_carga,
            ':id_campus' => $id_campus,
            ':matricula' => $alumno['matricula'],
            ':nombre_hijo' => $alumno['nombre_hijo'],
            ':apellido_paterno' => $alumno['apellido_paterno'],
            ':apellido_materno' => $alumno['apellido_materno'],
            ':sexo' => $alumno['sexo'],
            ':cicloescolar' => $alumno['cicloescolar'],
            ':grupo' => $alumno['grupo'],
            ':matricula_sep' => $alumno['matricula_sep'],
            ':foto' => $alumno['foto'],
            ':ultima_actualizacion' => $alumno['ultima_actualizacion'],
            ':activo' => $alumno['activo'] ? 1 : 0,
            ':seccion' => $alumno['seccion'] ,
            ':grado' => $alumno['grado'] ,
            ':fecha_ingreso' => $alumno['fecha_ingreso'] ,
            ':fecha_baja' => $alumno['fecha_baja'] ,
        ]);
    }

    echo "�7�3 Campus $id_campus procesado\n";
}

echo "�9�5 Importaci��n completa para todos los campus activos.";