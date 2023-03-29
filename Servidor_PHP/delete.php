<?php
//direccion del host, usuario, contraseña, nombre_bd
$conexion = mysqli_connect('localhost','czivdnkr_admin','byQ+[P2{}2iO','czivdnkr_qr');

if(!$conexion){
    echo "error en conexion";
}

$dir_archivo = "/home/czivdnkr/llaves/";
if ($dir = opendir($dir_archivo)) {

    //Recorre todos los archivos de la carpeta
    while (false !== ($archivo = readdir($dir))) {
      
      //Ignora los archivos . (directorio actual) y .. (directorio raiz)
      if ($archivo != '.' && $archivo != '..') {
        
        //Borra el archivo
        unlink("$dir_archivo/$archivo");
      }
    }
  
    //Cierra la carpeta
    closedir($dir);
  }

$delete = "UPDATE biblioteca SET llave=NULL";
$resultado = mysqli_query($conexion, $delete);
mysqli_close($conexion);

?>