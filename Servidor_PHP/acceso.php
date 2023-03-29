<?php

$conexion = mysqli_connect('localhost','czivdnkr_admin','byQ+[P2{}2iO','czivdnkr_qr');

//Se recupera la informacion enviado por el metodo POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recuperar el valor enviado desde Java
    //Al enviarse una cadena de bytes se recepta toda la informacion con el metodo php://input
    $mensaje = file_get_contents('php://input');

    //El mensaje recuperado es separado y decodificado de hexadecimal
    $partes = explode("g", $mensaje);
    $email_hex = $partes[0];
    $llave_qr_hex = $partes[1];

    $email = hex2bin($email_hex);
    $llave_qr = hex2bin($llave_qr_hex);
    //echo $llave_qr;
    
} else {
    echo "No se pudo recibir el mensaje desde el Escaner - ";
}


$nombre_arch = "/home/czivdnkr/llaves/" . hash('sha256', $email) . ".txt";
$open_key = fopen($nombre_arch,"rb");


if ($open_key) {
    // Lee la primera linea del archivo
    $llave_llave_qr_hex = trim(fgets($open_key));
    // Lee la segunda linea del archivo
    $iv_hex = trim(fgets($open_key));

    fclose($open_key);
    rewind($open_key);

} else {
    echo "Error - ";
}

//echo $llave_llave_qr_hex;
//echo "   xxx  xxx   ";
//echo $iv_hex;

$llave_llave_qr = hex2bin($llave_llave_qr_hex);
$iv = hex2bin($iv_hex);

//Hace la consulta de los atributos email y llave de la tabla usuario
//donde el atributo email coincida con la variable ingresada
$get_info = "SELECT e.email, q.llave 
             FROM estudiantes e 
             INNER JOIN qr q ON e.id_estudiante = q.id_estudiante 
             WHERE e.email='$email'";
$resultado = mysqli_query($conexion, $get_info);


//El resultado obtenido es en formato de tabla, y para obtener el resultado en una variable
//se recorre cada fila del resultado de la consulta y asigna el valor a las variables
while ($fila = mysqli_fetch_assoc($resultado)) {
    $email_bd = $fila['email'];
    $llave_bd = $fila['llave'];
}



//Se desencripta la llave almacenada en la base de datos y se guarda el restultado en la variable $llave_decrypt
$llave_bd_decrypt = openssl_decrypt($llave_bd, 'aes-256-cbc', $llave_llave_qr, OPENSSL_RAW_DATA, $iv);
$llave_qr_decrypt = openssl_decrypt($llave_qr, 'aes-256-cbc', $llave_llave_qr, OPENSSL_RAW_DATA, $iv);

//Codifica las llaves desencriptadas a hexadecimal para una comprobacion mas facil
$llave_qr_comprobar = bin2hex($llave_qr_decrypt);
$llave_bd_comprobar = bin2hex($llave_bd_decrypt);

//Se comprueba que la variable email enviada desde el escaner sea igual al email de la base de datos
//Y que la llave enviada desde el escaner es igual a la llave desencriptada almacenda en la base de datos
if($email==$email_bd && $llave_qr_comprobar==$llave_bd_comprobar && $open_key){


 // Obtener el nombre y el id del estudiante
 $get_estudiante = "SELECT id_estudiante, nombre FROM estudiantes WHERE email=?";
 $stmt = mysqli_prepare($conexion, $get_estudiante);
 mysqli_stmt_bind_param($stmt, "s", $email);
 mysqli_stmt_execute($stmt);
 $resultado = mysqli_stmt_get_result($stmt);
 $fila = mysqli_fetch_assoc($resultado);
 $id_estudiante = $fila['id_estudiante'];
 $nombre_estudiante = $fila['nombre'];
 mysqli_stmt_close($stmt);

 // Obtener la fecha y hora actual
 $fecha_hora_actual = date('Y-m-d H:i:s');

 // Insertar registro en la tabla biblioteca
 $insert_biblioteca = "INSERT INTO biblioteca (estudiante, fecha, id_estudiante) VALUES (?, ?, ?)";
 $stmt = mysqli_prepare($conexion, $insert_biblioteca);
 mysqli_stmt_bind_param($stmt, "sss", $nombre_estudiante, $fecha_hora_actual, $id_estudiante);
 mysqli_stmt_execute($stmt);
 mysqli_stmt_close($stmt);

 // Cerrar conexión con la base de datos
 mysqli_close($conexion);

 echo "Acceso Permitido";

}else{
echo "Acceso Denegado";
}


?>