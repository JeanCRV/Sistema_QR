package qr_scanner;

import com.github.sarxos.webcam.Webcam;
import com.github.sarxos.webcam.WebcamPanel;
import com.github.sarxos.webcam.WebcamResolution;
import com.google.zxing.BinaryBitmap;
import com.google.zxing.LuminanceSource;
import com.google.zxing.MultiFormatReader;
import com.google.zxing.NotFoundException;
import com.google.zxing.Result;
import com.google.zxing.client.j2se.BufferedImageLuminanceSource;
import com.google.zxing.common.HybridBinarizer;
import java.awt.Dimension;
import java.awt.Font;
import java.awt.image.BufferedImage;
import java.util.concurrent.Executor;
import java.util.concurrent.Executors;
import java.util.concurrent.ThreadFactory;
import java.util.logging.Level;
import java.util.logging.Logger;
import java.io.IOException;


/**
 *
 * @author JC
 */
public class camera extends javax.swing.JFrame implements Runnable,ThreadFactory{
    
    
    Result result = null;//almacena el resultado de la decodificación 
    BufferedImage image = null;// alamcena el fotograma actual de la cámara web
    static String mensajeEviar = null;
    
    private WebcamPanel panel = null;
    private Webcam webcam = null;
    private final Executor executor = Executors.newSingleThreadExecutor(this);


    public camera() {
        initComponents();
        initWebcam();
        
    }

    @SuppressWarnings("unchecked")
    // <editor-fold defaultstate="collapsed" desc="Generated Code">//GEN-BEGIN:initComponents
    private void initComponents() {

        jPanel1 = new javax.swing.JPanel();
        jPanel2 = new javax.swing.JPanel();
        result_field = new javax.swing.JTextField();

        setDefaultCloseOperation(javax.swing.WindowConstants.EXIT_ON_CLOSE);

        jPanel1.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());

        jPanel2.setLayout(new org.netbeans.lib.awtextra.AbsoluteLayout());
        jPanel1.add(jPanel2, new org.netbeans.lib.awtextra.AbsoluteConstraints(70, 40, 700, 400));
        jPanel1.add(result_field, new org.netbeans.lib.awtextra.AbsoluteConstraints(220, 460, 430, 70));

        javax.swing.GroupLayout layout = new javax.swing.GroupLayout(getContentPane());
        getContentPane().setLayout(layout);
        layout.setHorizontalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, 840, Short.MAX_VALUE)
        );
        layout.setVerticalGroup(
            layout.createParallelGroup(javax.swing.GroupLayout.Alignment.LEADING)
            .addComponent(jPanel1, javax.swing.GroupLayout.DEFAULT_SIZE, javax.swing.GroupLayout.DEFAULT_SIZE, Short.MAX_VALUE)
        );

        pack();
    }// </editor-fold>//GEN-END:initComponents

    
    private void initWebcam(){
        Dimension size = WebcamResolution.QVGA.getSize();
        webcam = Webcam.getWebcams().get(0);
        webcam.setViewSize(size);
        
        panel =new WebcamPanel(webcam);
        panel.setPreferredSize(size);
        //panel.setFPSDisplayed(true);
        
        jPanel2.add(panel, new org.netbeans.lib.awtextra.AbsoluteConstraints(0,0,700,400));
        
        executor.execute(this);
    }
        
        
@Override
    public void run(){
        int i = 0;
        do{
            try {
                //detiene el proceso durante 100 milisegundos
                Thread.sleep(100);
            } catch (InterruptedException ex) {
                Logger.getLogger(camera.class.getName()).log(Level.SEVERE, null, ex);
            }
            
            //comprueba si la camara web esta activa
            if(webcam.isOpen()){
                //obtiene el fotograma actual de la camara y comprueba si es nulo
                if((image = webcam.getImage()) == null){
                    continue;
                }
            }
            //crea una fuente de luminancia a partir del fotograma actual
            LuminanceSource source = new BufferedImageLuminanceSource(image);
            //convierte la fuente de luminancia en un mapa de bits binario
            BinaryBitmap bitmap = new BinaryBitmap(new HybridBinarizer(source));
            
            try {
                //decodifica el mapa de bits binario
                result = new MultiFormatReader().decode(bitmap);
            } catch (NotFoundException ex) {
                Logger.getLogger(camera.class.getName()).log(Level.SEVERE, null, ex);
            }
            
            if(result != null){
                try {
                    //El resultado obtenido en la lectura del QR se almacena en el String qr
                    String qr = result.getText();
                    //result_field.setText(qr);
                    mensajeEviar = qr;
                    //Se crea un objeto y se envia al constructor la variable "mensajeEviar"
                    //que es el mensaje capturado por la camara
                    conexion miConexion = new conexion(mensajeEviar);
                    
                    //Se llama al metodo getMiVariable(), que devuelve la respuesta a la peticion http
                    //esta respuesta se alamacena en el String mensaje
                    String mensaje = miConexion.getMiVariable();
                    System.out.println(mensaje);
                    if (mensaje.equals("Acceso Permitido")) {
                        
                        result_field.setText(mensaje);
                        result_field.setFont(new Font("Arial", Font.PLAIN,50));
                        try {
                        Thread.sleep(3000); // El hilo se detiene durante 5 segundos
                        result_field.setText(""); // Limpia el campo de texto
                        result = null;
                        } catch (InterruptedException e) {
                    }
                        } else {
                        result_field.setText("Error en el Acceso");
                        result_field.setFont(new Font("Arial", Font.PLAIN,50));
                        Thread.sleep(3000); // El hilo se detiene durante 5 segundos
                        result_field.setText(""); // Limpia el campo de texto
                        result = null;
                    //i=0;
                    //result = null;
                    }
                } catch (IOException ex) {
                    Logger.getLogger(camera.class.getName()).log(Level.SEVERE, null, ex);
                } catch (InterruptedException ex) {
                    Logger.getLogger(camera.class.getName()).log(Level.SEVERE, null, ex);
                }
            }
        }while(i==0);
    }
     
    //No evitará que el programa se cierre si el hilo todavía está en ejecución.
    @Override
    public Thread newThread(Runnable r){
        Thread t = new Thread(r,"My Thread");
        t.setDaemon(true);
        return t;
    }
    
    
    /**
     * @param args the command line arguments
     */
    public static void main(String args[]) {
        /* Set the Nimbus look and feel */
        //<editor-fold defaultstate="collapsed" desc=" Look and feel setting code (optional) ">
        /* If Nimbus (introduced in Java SE 6) is not available, stay with the default look and feel.
         * For details see http://download.oracle.com/javase/tutorial/uiswing/lookandfeel/plaf.html 
         */
        try {
            for (javax.swing.UIManager.LookAndFeelInfo info : javax.swing.UIManager.getInstalledLookAndFeels()) {
                if ("Nimbus".equals(info.getName())) {
                    javax.swing.UIManager.setLookAndFeel(info.getClassName());
                    break;
                }
            }
        } catch (ClassNotFoundException | InstantiationException | IllegalAccessException | javax.swing.UnsupportedLookAndFeelException ex) {
            java.util.logging.Logger.getLogger(camera.class.getName()).log(java.util.logging.Level.SEVERE, null, ex);
        }
        //</editor-fold>
        
        //</editor-fold>

        /* Create and display the form */
        java.awt.EventQueue.invokeLater(() -> {
            new camera().setVisible(true);
        });
    }

    // Variables declaration - do not modify//GEN-BEGIN:variables
    private javax.swing.JPanel jPanel1;
    private javax.swing.JPanel jPanel2;
    private javax.swing.JTextField result_field;
    // End of variables declaration//GEN-END:variables
}
