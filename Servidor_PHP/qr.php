<?php
//direccion, usuario, contraseña, nombre bd
$conexion = mysqli_connect('localhost','czivdnkr_admin','byQ+[P2{}2iO','czivdnkr_qr');

if(!$conexion){
    echo "error en conexion";
}

$email = $_POST["email"];


$llave_qr = random_bytes(16);
$llave_qr_hex = bin2hex($llave_qr);
$llave_llave_qr = random_bytes(32);
$llave_llave_qr_hex = bin2hex($llave_llave_qr);
$iv = random_bytes(16);
$iv_hex = bin2hex($iv);


$llave_encrypt = openssl_encrypt($llave_qr, 'aes-256-cbc',$llave_llave_qr, OPENSSL_RAW_DATA, $iv);

$guardar = "UPDATE qr 
            SET llave = ?
            WHERE id_estudiante = (SELECT id_estudiante FROM estudiantes WHERE email = ?)";
$resultado = $conexion->prepare($guardar);
$resultado->bind_param('ss',$llave_encrypt,$email);
$resultado->execute();
mysqli_close($conexion);


if($resultado->affected_rows > 0) {
    
    echo $llave_qr_hex . ' ' . $llave_llave_qr_hex . ' ' . $iv_hex;
    $ruta_archivo = "/home/czivdnkr/llaves/" . hash('sha256', $email) . ".txt";

    $archivo_llave = fopen($ruta_archivo,"w+b");

if( $archivo_llave == false ) {
    echo "Error al crear el archivo";
  }
  else
  {
      // Escribir en el archivo:
       fwrite($archivo_llave, $llave_llave_qr_hex . PHP_EOL . $iv_hex);
      // Fuerza a que se escriban los datos pendientes en el buffer:
       fflush($archivo_llave);
       fclose($archivo_llave);    
  }

 
}else{
    echo "Error al Generar";}
?>