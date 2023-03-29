<?php
//direccion, usuario, contraseña, nombre bd
$conexion = mysqli_connect('localhost','czivdnkr_admin','byQ+[P2{}2iO','czivdnkr_qr');

if(!$conexion){
    echo "error en conexion";
}

$email = $_POST["email"];
$password = $_POST["password"];


//genera un numero aleatorio que servidara la llave para el cifrado
//se codifica en formato hexadecimal o base64 para que sean más legibles y portables
$llave_pass = random_bytes(32);
$llave_pass_hex = bin2hex($llave_pass);
//vector de inicialización ($iv) hace que la encriptación sea determinista y evita ataques a patrones repetidos.
$iv = random_bytes(16);
$iv_hex = bin2hex($iv);
$password_encrypted = openssl_encrypt($password, 'aes-256-cbc',$llave_pass, OPENSSL_RAW_DATA, $iv);

//se guarda en la base de datos la llave cifrada
$sql = "IF EXISTS(SELECT * FROM qr WHERE id_estudiante = (SELECT id_estudiante FROM estudiantes WHERE email = ?)) THEN
        UPDATE qr
        SET password = ?
        WHERE id_estudiante = (SELECT id_estudiante FROM estudiantes WHERE email = ?);
        ELSE
        INSERT INTO qr (id_estudiante, password)
        VALUES ((SELECT id_estudiante FROM estudiantes WHERE email = ?), ?);
        END IF;";

// Preparar la consulta
$resultado = $conexion->prepare($sql);

// Asociar los parámetros
$resultado->bind_param("sssss", $email, $password_encrypted, $email, $email, $password_encrypted);

// Ejecutar la consulta
if ($resultado->execute()) {
//se crea el archivo donde se guarda la llave de cifrado y el vector de inicializacion
//Para evitar posibles problemas de seguridad, se hace un hash del correo utilizando la función md5()
    $archivo_pass = fopen("/home/czivdnkr/pass/" . hash('sha256', $email) . ".txt","w+b");
    if( $archivo_pass == false ) {
        echo "Error al Crear Archivo";
      }
      else
      {
            // Mensaje de confiramcion
            echo "Registrado Correctamente";
            // Se escribe en el archivo los datos con un salto de linea
            fwrite($archivo_pass, $llave_pass_hex . PHP_EOL . $iv_hex);
            // Fuerza a que se escriban los datos pendientes en el buffer:
            fflush($archivo_pass);
      }
      // Cierra el archivo
      fclose($archivo_pass);
      
}else{
    echo "Email Incorrecto";
}


?>