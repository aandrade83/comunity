<?
require_once($_SERVER['DOCUMENT_ROOT']."/VV/utilities/includes.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluir los archivos de PHPMailer
require $_SERVER['DOCUMENT_ROOT'].'/VV/utilities/includes/PHPMailer/src/Exception.php';
require $_SERVER['DOCUMENT_ROOT'].'/VV/utilities/includes/PHPMailer/src/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'].'/VV/utilities/includes/PHPMailer/src/SMTP.php';



error_reporting(E_ALL); 
ini_set('display_errors', '1');


/*
$id = param('id');
$user = get_user($id);
print_r($user);
//echo "<BR>";

$emailSender = new EmailSender();
$pass = explode('@',$user->vars['correo']);
//echo $user->vars['correo'].' '.$user->vars['nombre'].' '.$user->vars['filial'].' '.$pass[0]."<BR>";
$emailSender->sendWelcomeEmail($user->vars['correo'], $user->vars['nombre'],$user->vars['filial'], $pass[0]);

*/

class EmailSender {
    private $mailer;

    public function __construct() {
        $this->mailer = new PHPMailer(true);

        // Configuración del servidor SMTP de Gmail
        //$mail->SMTPDebug = SMTP:DEBUG_SERVER;
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'alexis.andrade@gmail.com'; // Tu dirección de correo de Gmail
        $this->mailer->Password =  'qbmsmhldmgxpcgip'; //'qbmsmhldmgxpcgip'; alexis   //encubkvpnatlydfp comite   // Tu contraseña de correo de Gmail //'4lExG4rr0B0';//
       //$this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mailer->SMTPSecure = 'tls';
       // $this->mailer->Port = 465;
        $this->mailer->Port = 587;

        // Remitente
        


/*
        $this->mailer->isSMTP();
        $this->mailer->Host       = 'lab.lacallecr.com';
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = 'condominio@lab.lacallecr.com';
        $this->mailer->Password   = 'vb7&d6F79'; // Reemplaza con tu contraseña de correo
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
         // $this->mailer->SMTPSecure = 'tls';
        $this->mailer->Port       = 465;

        $this->mailer->SMTPDebug = 2; // O 3 para más detalles
          *//*
          $this->mailer->isSMTP();
    $this->mailer->Host       = 'lab.lacallecr.com';
    $this->mailer->SMTPAuth   = true;
    $this->mailer->Username   = 'condominio@lab.lacallecr.com';
    $this->mailer->Password   = 'vb7&d6F79'; // Reemplaza con tu contraseña de correo
    $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $this->mailer->Port       = 465;


    // Deshabilitar la verificación del certificado
    $this->mailer->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );


*/



        $this->mailer->setFrom('alexis.andrade@gmail.com', 'Informacion Valle Verde');


    }

    public function sendWelcomeEmail($toEmail, $toName, $userNumber, $password) {
        try {
            // Destinatario
            $this->mailer->addAddress($toEmail, $toName);

            // Contenido del correo
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Bienvenido a nuestro servicio';
            $this->mailer->Body    = $this->getWelcomeEmailTemplate2($toName, $userNumber, $password);
            $this->mailer->AltBody = 'Bienvenido a nuestro servicio';

            $this->mailer->send();
            echo 'El mensaje ha sido enviado';

/*
    // Guardar el correo en la bandeja de enviados
    $imapPath = '{lab.lacallecr.com:993/imap/ssl}INBOX.Sent'; // Ruta IMAP a la carpeta de enviados
    $mailbox = new Mailbox($imapPath, 'condominio@lab.lacallecr.com', 'vb7&d6F79', __DIR__, 'utf-8');

    // Construir el mensaje a partir de PHPMailer
    $message = $mail->getSentMIMEMessage();

    // Guardar el mensaje en la carpeta de enviados
    $mailbox->saveMessage($message);
    echo 'El mensaje ha sido guardado en la bandeja de enviados';

*/

        } catch (Exception $e) {
            echo "El mensaje no se pudo enviar. Error de Mailer: {$this->mailer->ErrorInfo}";
        }
    }

    
    private function getWelcomeEmailTemplate($name, $userNumber, $password) {
        // Plantilla de correo en HTML
        return "
            <html>
            <head>
                <title>Bienvenido</title>
                <style>
                    .email-container {
                        border: 2px solid #000;
                        background-color: #d4edda;
                        padding: 20px;
                        border-radius: 10px;
                        font-family: Arial, sans-serif;
                    }
                    .email-header {
                        font-size: 24px;
                        font-weight: bold;
                    }
                    .email-body {
                        margin-top: 20px;
                        font-size: 16px;
                    }
                    .email-footer {
                        margin-top: 20px;
                    }
                </style>
            </head>
            <body>
                <div class='email-container'>
                    <div class='email-header'>
                        Hola, $name!
                    </div>
                    <div class='email-body'>
                        <p>Gracias por unirte a nuestro servicio. Esperamos que disfrutes de tu experiencia con nosotros.</p>
                        <p>Puedes accesar al siguiente Link: https://lab.lacallecr.com/VV/</p>
                        <p>Tu usuario es tu número de filial: <strong>$userNumber</strong></p>
                        <p>Tu password es: <strong>$password</strong></p><BR><BR>
                        <p>En este link puedes ver un pequeño tutorial <a href='https://drive.google.com/file/d/1E_esGsbRNkFBPL_AdzmF4TB7ZFGzPCw1/view'>Ver</a></p>
                    </div>
                    <div class='email-footer'>
                        <img style='width: 120px' src='https://lab.lacallecr.com/VV/apps/Forum/images/Logo_VV.png' alt='Valle Verde' />
                        <p>Saludos,<br>Comité Asesor Condominio Valle Verde</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

    private function getWelcomeEmailTemplate2($name, $userNumber, $password) {

        $name_telegram  = str_replace(' ','_', $name);
        // Plantilla de correo en HTML
        $command = "/add ".$name_telegram." ".$userNumber." ".$password; 
        return "
             <html>
            <head>
                <title>Bienvenido</title>
                <style>
                    .email-container {
                        border: 2px solid #000;
                        background-color: #d4edda;
                        padding: 20px;
                        border-radius: 10px;
                        font-family: Arial, sans-serif;
                    }
                    .t-container {
                        border: 2px solid #000;
                        background-color: #d4edea;
                        padding: 20px;
                        border-radius: 10px;
                        font-family: Arial, sans-serif;
                    }
                    .email-header {
                        font-size: 24px;
                        font-weight: bold;
                    }
                    .email-body {
                        margin-top: 20px;
                        font-size: 16px;
                    }
                    .email-footer {
                        margin-top: 20px;
                    }
                </style>
            </head>
            <body>
                <div class='t-container'>
                    <div class='email-header'>
                       ¡Hola!
                    </div>
                    <div class='email-body'>
                        <p>El comité Asesor de Valle Verde ha puesto a dispocisión de los condóminos un sitio web con el fin de mejorar la comunicación entre todos.</p>
                        <p>Este sitio no es Oficial ni Obligatorio, sin embargo es de gran importancia que podamos ser parte del mismo.</p>
                        <p>pueden pertenecer tanto los que viven ya en el condominio y los que aun no han construido.</p>
                    </div>
                    <div class='email-container'>
                        <p> <strong>*** CARACTERISTICAS ***</strong></p>
                        <p> <strong>*</strong> Se va a poder incluir temas de diferentes categorias. Ya sean para organizar actividades o proponer proyectos</p>
                        <p> <strong>*</strong> Se va a poder agregar comentarios o respuestas a los temas o simplemente votar con un 'like' </p>
                        <p> <strong>*</strong> Estas votaciones nos va dar una impresión a todos de lo que realmente queremos, para poder proponer ideas o proyectos en asambleas </p><BR>
                        <p> <strong>*</strong> Se van a ir agregando funcionalidades al sistema tales como Servicios Locales, Servicios Internos , y demás opciones para que tengamos información de fácil acceso de forma ordenada</p><BR>
                        <p>Si quieres ser parte de este sitio web puedes registrarte en el siguiente link <a href='https://lab.lacallecr.com/VV/apps/users/ingreso.php'>ENTRAR</a> (Pueden registrarse varias personas por Filial)</p>
                    </div>
                    <div class='email-footer'>
                        <img style='width: 120px' src='https://lab.lacallecr.com/VV/apps/Forum/images/Logo_VV.png' alt='Valle Verde' />
                        <p>Saludos,<br>Comité Asesor Condominio Valle Verde</p>
                    </div>
                </div>
            </body>
            </html>
        ";
    }

}

// Ejemplo de uso


$emails[0]['email'] = 'comiteasesorvalleverde@gmail.com';
$emails[0]['nombrel'] = 'Comite Asesor';
$emails[0]['filial'] = '00'; /*
$emails[1]['email'] = 'cquesada@condovaluecr.com';
$emails[1]['nombrel'] = 'Carlos Quesada';
$emails[1]['filial'] = '00';
$emails[2]['email'] = 'aleaz05@gmail.com';
$emails[2]['nombrel'] = 'Alejandra Arce';
$emails[2]['filial'] = '83';
//$emails[3]['email'] = 'aandradevrb@gmail.com';
//$emails[3]['nombrel'] = 'Alexis Andrade';
//$emails[3]['filial'] = '83';
*/

foreach ($emails as $email) {
$emailSender = new EmailSender();
$pass = explode('@',$email['email']);
echo $email['email'].' '.$email['nombrel'].' '.$email['filial'].' '.$pass[0]."<BR>";
//$emailSender->sendWelcomeEmail($email['email'], $email['nombrel'],$email['filial'], $pass[0]);
    
}



?>

