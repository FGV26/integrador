<?php
$target_dir = __DIR__ . "/../src/img/"; // Carpeta donde se almacenarán las imágenes
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true); // Crear la carpeta si no existe
}

$target_file = $target_dir . basename($_FILES["imagen"]["name"]);
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Comprobar si el archivo es una imagen real
$check = getimagesize($_FILES["imagen"]["tmp_name"]);
if($check === false) {
    echo json_encode(["success" => false, "message" => "File is not an image."]);
    exit();
}

// Subir la imagen al servidor
if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
    echo json_encode(["success" => true, "message" => "Image uploaded successfully.", "filename" => basename($_FILES["imagen"]["name"])]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to upload image."]);
}
?>
