<?php
// Указываем путь к tcpdf.php
require_once __DIR__ . '/tcpdf/tcpdf.php';

// Создаем PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 12);
$pdf->Write(0, 'Hello, world!');
$pdf->Output('example.pdf', 'I');
?>