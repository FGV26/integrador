<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Dirección de correo de destino
    $to = 'vargascondork@gmail.com';

    // Asunto del correo
    $email_subject = "Nuevo mensaje de contacto: $subject";

    // Cuerpo del correo
    $email_body = "Has recibido un nuevo mensaje de contacto.\n\n".
                  "Nombre: $name\n".
                  "Correo: $email\n\n".
                  "Mensaje:\n$message";

    // Encabezados 
    $headers = "From: $email\n";
    $headers .= "Reply-To: $email\n";

    // Enviar el correo
    if (mail($to, $email_subject, $email_body, $headers)) {
        // Redirigir a una página de agradecimiento
        header('Location: gracias.php');
        exit();
    } else {
        echo "El mensaje no pudo ser enviado.";
    }
}
?>
