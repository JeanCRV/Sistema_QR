package qr_scanner;

import java.io.BufferedReader;
import java.io.DataOutputStream;
import java.io.IOException;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;


public class conexion {
    
    public String url = "https://lkrmhrcz.lucusvirtual.es/acceso.php";
    static StringBuilder response;
    static String mensajeqr;
    

    public conexion(String mensajeEnviar) throws IOException{
        
        try{
          String postData = mensajeEnviar;

        //Establece la conexión HTTP
        URL obj = new URL(url);
        HttpURLConnection con = (HttpURLConnection) obj.openConnection();
        con.setRequestMethod("POST");
            
        //Escribe los datos en la conexión HTTP
        con.setDoOutput(true);
    
        DataOutputStream wr = new DataOutputStream(con.getOutputStream());
        wr.writeBytes(postData);
        wr.flush();
    

        //Lee la respuesta del servidor
        BufferedReader in = new BufferedReader(new InputStreamReader(con.getInputStream()));
        String inputLine;
        response = new StringBuilder();
        while ((inputLine = in.readLine()) != null) {
            response.append(inputLine);
            conexion.mensajeqr = response.toString();
        }

        //Imprime la respuesta del servidor
        //System.out.println(response.toString());

        } catch (IOException e) {
        System.err.println("Error reading file: " + e.getMessage());
    }
    }
    
        public String getMiVariable() {
            //Método de acceso (getter) que devuelve el valor de la variable de instancia
            return conexion.mensajeqr; 
   }

}
