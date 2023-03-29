<?php
//direccion del host, usuario, contraseña, nombre_bd
$conexion = mysqli_connect('localhost','czivdnkr_admin','byQ+[P2{}2iO','czivdnkr_qr');

if(!$conexion){
    echo "error en conexion";
}

//Recibe los datos email y password con el metodo POST y los almacena en una variable
$email = $_POST["email"];
$password = $_POST["password"];

$nombre_arch = "/home/czivdnkr/pass/" . hash('sha256', $email) . ".txt";
$open_key = fopen($nombre_arch,"rb");

if ($open_key) {
    // Lee la primera linea del archivo
    $llave_pass_hex = trim(fgets($open_key));
    // Lee la segunda linea del archivo
    $iv_hex = trim(fgets($open_key));

    fclose($open_key);
    rewind($open_key);

} else {
    echo "Error";
}

$llave_pass = hex2bin($llave_pass_hex);
$iv = hex2bin($iv_hex);

//Hace la consulta de los atributos email y password de la tabla usuario
//donde el atributo email coincida con la variable ingresada
$get_password = "SELECT e.email, q.password 
                 FROM estudiantes e 
                 INNER JOIN qr q ON e.id_estudiante = q.id_estudiante 
                 WHERE e.email='$email'";
$resultado = mysqli_query($conexion, $get_password);
mysqli_close($conexion);

//El resultado obtenido es en formato de tabla, y para obtener el resultado en una variable
//se recorre cada fila del resultado de la consulta y asigna el valor a las variables
while ($fila = mysqli_fetch_assoc($resultado)) {
    $email_bd = $fila['email'];
    $password_bd = $fila['password'];
}

//Desencripta el password almacenado en la base de datos
$password_decrypt = openssl_decrypt($password_bd, 'aes-256-cbc', $llave_pass, OPENSSL_RAW_DATA, $iv);


//Se comprueba que la variable email enviado desde la aplicacion sea igual al email de la base de datos
//Y que el password enviado desde la aplicacion es igual al password almacendo en la base de datos
if($email==$email_bd && $password==$password_decrypt){
    echo "Ingreso Exitoso";
}else{
echo " Credenciales Incorrectas";
}

?>