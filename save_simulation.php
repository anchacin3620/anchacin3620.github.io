<?php
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$conn = new mysqli('localhost', 'usuario_balistica', '123ac321', 'balistica');

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => $conn->connect_error]));
}

$stmt = $conn->prepare("INSERT INTO simulaciones (
    angulo, velocidad, resistencia_aire, masa, densidad_aire, 
    coeficiente_arrastre, area_transversal, alcance, altura_maxima, tiempo_vuelo
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param(
    "dddddddddd", 
    $data['angle'], 
    $data['velocity'], 
    $data['airResistance'], 
    $data['mass'], 
    $data['density'], 
    $data['dragCoefficient'], 
    $data['crossSectionalArea'], 
    $data['range'], 
    $data['max_height'], 
    $data['time_of_flight']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
