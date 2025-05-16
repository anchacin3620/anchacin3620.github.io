<?php
require_once('tcpdf/tcpdf.php');

$id = $_GET['id'] ?? 0;

$conn = new mysqli('localhost', 'tu_usuario', 'tu_contraseña', 'balistica');
$result = $conn->query("SELECT * FROM simulaciones WHERE id = $id");
$data = $result->fetch_assoc();

$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Reporte de Simulación Balística', 0, 1, 'C');

$pdf->SetFont('helvetica', '', 12);
$html = <<<EOD
<h2>Parámetros de la Simulación</h2>
<p><b>Fecha:</b> {$data['fecha']}</p>
<p><b>Ángulo:</b> {$data['angulo']}°</p>
<p><b>Velocidad inicial:</b> {$data['velocidad']} m/s</p>
<p><b>Masa del proyectil:</b> {$data['masa']} kg</p>

<h2>Resultados</h2>
<p><b>Alcance:</b> {$data['alcance']} m</p>
<p><b>Altura máxima:</b> {$data['altura_maxima']} m</p>
<p><b>Tiempo de vuelo:</b> {$data['tiempo_vuelo']} s</p>
EOD;

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output("reporte_balistica_$id.pdf", 'D');
?>
