<?php

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

try {
    // Подключение к базе
    $pdo = new PDO('mysql:host=localhost;dbname=Myp', 'root', 'root', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Параметры
    $patientId = isset($_GET['patientId']) ? (int)$_GET['patientId'] : 0;
    $startDate = $_GET['startDate'] ?? date('Y-m-d');
    $endDate = $_GET['endDate'] ?? date('Y-m-d');
    $startTime = $_GET['startTime'] ?? '09:00:00';
    $endTime = $_GET['endTime'] ?? '08:00:00';

    // Если patientId не указан — берём последний
    if ($patientId === 0) {
        $stmt = $pdo->query('SELECT patientId FROM assignments ORDER BY patientId DESC LIMIT 1');
        $patientId = (int)$stmt->fetchColumn();
    }
    if ($patientId <= 0) {
        throw new Exception("Неверный patientId");
    }

    $startDateTime = $startDate . ' ' . $startTime;
    $endDateTime = $endDate . ' ' . $endTime;

    // Получаем назначения
    $query = '
    SELECT a.assignment, a.patientId, a.name, a.dose, a.unit, a.selected_date, a.selected_time,
           p.patientName, p.dateOfBirth, p.dateOfAdmission, p.bloodType, p.rhFactor
    FROM assignments a
    JOIN patients p ON a.patientId = p.patientId
    WHERE a.patientId = :patientId
';
$stmt = $pdo->prepare($query);
$stmt->execute([
    'patientId' => $patientId
]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$data) {
        throw new Exception("Нет данных для patientId {$patientId}");
    }

    // Получаем шаблон
    $stmt = $pdo->prepare('SELECT template FROM templates WHERE id = 1');
    $stmt->execute();
    $templateData = $stmt->fetchColumn();
    if (!$templateData) {
        throw new Exception("Шаблон не найден в базе");
    }

    // Сохраняем шаблон во временный файл
    $tempFilePath = sys_get_temp_dir() . '/template_' . uniqid() . '.xlsx';
    file_put_contents($tempFilePath, $templateData);
    if (!file_exists($tempFilePath) || filesize($tempFilePath) < 100) {
        throw new Exception("Файл шаблона повреждён или пуст");
    }

    // Загружаем шаблон
    $spreadsheet = IOFactory::load($tempFilePath);
    $sheet = $spreadsheet->getActiveSheet();

    // Маппинг времени
    $timeLabels = [
        '09:00:00' => 'F', '10:00:00' => 'G', '11:00:00' => 'H', '12:00:00' => 'I',
        '13:00:00' => 'J', '14:00:00' => 'K', '15:00:00' => 'L', '16:00:00' => 'M',
        '17:00:00' => 'N', '18:00:00' => 'O', '19:00:00' => 'P', '20:00:00' => 'Q',
        '21:00:00' => 'R', '22:00:00' => 'S', '23:00:00' => 'T', '00:00:00' => 'U',
        '01:00:00' => 'V', '02:00:00' => 'W', '03:00:00' => 'X', '04:00:00' => 'Y',
        '05:00:00' => 'Z', '06:00:00' => 'AA', '07:00:00' => 'AB', '08:00:00' => 'AC',
    ];

    // Заполнение
 $assignmentMap = [
    'iv_perfusor' => 'В/в перфузор',
    'tablet'     => 'Таблетка',        // пример
    'injection'  => 'Инъекция',
    'subcutaneous'   => 'п/к', 
    'iv' => 'В/в',
    'intraarterial'     => 'в/a',       // пример
];

$rowNum = 61;
foreach ($data as $row) {
    // Преобразуем assignment, если есть в словаре
    $assignmentDisplay = $assignmentMap[$row['assignment']] ?? $row['assignment'];

    // Заполняем Excel
    $sheet->setCellValue('B' . $rowNum, $assignmentDisplay . ':');
    $sheet->setCellValue('B' . ($rowNum + 1), $row['name'] . ' ' . $row['dose'] . ' ' . $row['unit']);

    if (isset($timeLabels[$row['selected_time']])) {
    $col = $timeLabels[$row['selected_time']];
    $sheet->getStyle($col . ($rowNum + 1))
          ->getFill()->setFillType(Fill::FILL_SOLID)
          ->getStartColor()->setARGB('ABABAB');
}
    $rowNum += 2;
}

    // Данные пациента
    foreach ($data as $row) {
        $sheet->setCellValue('B39', $row['patientName']);
        $sheet->setCellValue('D41', $row['dateOfBirth']);
        $sheet->setCellValue('D37', $row['patientId']);
        $sheet->setCellValue('G38', date('d.m.Y'));
        $sheet->setCellValue('L41', $row['dateOfAdmission']);

        $interval = (new DateTime())->diff(new DateTime($row['dateOfAdmission']));
        $sheet->setCellValue('P38', $interval->days);

        $sheet->setCellValue('T38', $row['rhFactor']);
        $sheet->setCellValue('X38', $row['bloodType']);
        break;
    }

    // Чистим буферы перед выводом
    while (ob_get_level()) {
    ob_end_clean();
}
    // Заголовки
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="updated_file.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');

    unlink($tempFilePath);
    exit;

} catch (Throwable $e) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: text/plain; charset=utf-8');
    echo "Ошибка: " . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine();
}
?>