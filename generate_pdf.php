<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set("display_errors", 0);

require_once __DIR__ . '/tcpdf/tcpdf.php';


// Retrieve patientId and protocolName from the URL
$patientId = isset($_GET['id']) ? $_GET['id'] : '';
$protocolName = isset($_GET['protocolName']) ? $_GET['protocolName'] : '';

// Ensure patientId and protocolName are not empty
if (empty($patientId) || empty($protocolName)) {
    echo "Ошибка: Идентификатор пациента или название протокола пуст.";
    ob_end_clean(); // Clean buffer and stop script execution
    exit;
}

// Database connection parameters
$host = "localhost";
$username = "root";
$password = "root";
$database = "Myp";

// Create a database connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set the character set for the database connection
$conn->set_charset("utf8mb4");

// Prepare the SQL statement with placeholders
$query = "
    SELECT p.protocolId, p.protocolName, p.complaintData, p.medicalHistory, p.lifeHistory, p.diagnosis, p.conclusion, p.therapy, p.recommendations,
           p.generalCondition, p.consciousness, p.nutrition, p.additionalInfo, p.skinColor, p.skinMoisture, p.turgor, p.edema, 
           p.respiratoryRhythm, p.respiratoryRate, p.SPO2, p.breathingNature, p.wheezing, p.pleuralFrictionNoise, p.respiratoryAdditionalInfo, 
           p.hemodynamics, p.systolicPressure, p.diastolicPressure, p.heartRate, p.heartRhythm, p.heartTones, p.CardioAdditionalInfo, 
           p.tongueColor, p.bellySize, p.abdomenPalpation, p.abdominalPainPalpation, p.stoolSinceOnset, p.liver, p.protrusionFromRibArc, 
           p.urination, p.natureOfUrination, p.urineColor, p.urineAdditionalInfo, p.resultinfo, p.pp, p.ds, p.statsionar, p.daniy, p.vrach, 
           p.podanom, p.alko, p.perz, p.pert, p.pero, p.rect, p.pro, p.drug,
           d.patientId, d.patientName, d.dateOfAdmission, d.bmi
    FROM protocols p
    JOIN patients d ON p.patientId = d.patientId
    WHERE p.patientId = ? AND p.protocolName = ?";

// Prepare the statement
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

// Bind the parameters
$stmt->bind_param("is", $patientId, $protocolName);

$stmt->execute();

// Check for errors
if ($stmt->error) {
    echo "Error during execution: " . $stmt->error;
    ob_end_clean(); // Clean buffer and stop script execution
    exit;
} else {
    // Get result
    $result = $stmt->get_result();

    // Check if data is found
    if ($result) {
        // Fetch data
        $row = $result->fetch_assoc();

        if ($row) {
            // Create a TCPDF object
            $pdf = new TCPDF();

            // Set document properties
            $pdf->SetCreator('Your Name');
            $pdf->SetAuthor('Your Name');
            $pdf->SetHeaderData('', 0, 'Universal patient management system', '');

            // Add a page
            $pdf->AddPage();

            // Set font and size
            $pdf->SetFont('freeserif', '', 12, '', true);

            // Output protocolName
            $pdf->Ln(3);
            $pdf->SetFont('freeserif', 'B', 12, '', true);
            $pdf->Write(0, $row['protocolName'], '', 0, 'C', true, 0, false, false, 0);

            // Define sections and their content
            $sections = [
                'Жалобы:' => $row['complaintData'],
                'Результаты исследования:' => $row['resultinfo'],
                'Диагноз:' => $row['ds'],
                'Заключение:' => $row['conclusion'],
                'Лечение:' => $row['therapy'],
                'Рекомендации:' => $row['recommendations'],
                'Анамнез заболевания:' => "Доставлен в стационар: {$row['statsionar']}; Со слов: {$row['medicalHistory']}; Данное заболевание: {$row['daniy']}; К врачу: {$row['vrach']}; По данному заболеванию проходил стационарное лечение в текущем году: {$row['podanom']}; Алкогольное опьянение: {$row['alko']}",
                'Анамнез жизни:' => "Хронические заболевания: {$row['lifeHistory']}; Перенесенные заболевания: {$row['perz']}; Перенесенные травмы: {$row['pert']}; Перенесенные операции: {$row['pero']}; Реакция на: {$row['rect']}; Проявление: {$row['pro']}; Наименование препарата: {$row['drug']}",
                'Объективный статус:' => "Причина поступления: {$row['pp']}; Общее состояние: {$row['generalCondition']}; Сознание: {$row['consciousness']}; Питание: {$row['nutrition']}; И.М.Т: {$row['bmi']} кг/м²",
                'Состояние кожных покровов, видимых слизистых, лимфатических узлов:' => "Цвет кожи: {$row['skinColor']}; Влажность кожи: {$row['skinMoisture']}; Тургор: {$row['turgor']}; Отеки: {$row['edema']}; Дополнительная информация: {$row['additionalInfo']}",
                'Состояние дыхательной системы:' => "Ритм дыхания: {$row['respiratoryRhythm']}; Частота дыхания: {$row['respiratoryRate']}; SPO2: {$row['SPO2']}%; Характер дыхания: {$row['breathingNature']}; Хрипы: {$row['wheezing']}; Шум трения плевры: {$row['pleuralFrictionNoise']}; Дополнительная информация по дыхательной системе: {$row['respiratoryAdditionalInfo']}",
                'Состояние сердечно-сосудистой системы:' => "Гемодинамика: {$row['hemodynamics']}; Систолическое давление: {$row['systolicPressure']} мм.рт.ст; Диастолическое давление: {$row['diastolicPressure']} мм.рт.ст; Частота сердечных сокращений: {$row['heartRate']} удар/мин; Ритм сердца: {$row['heartRhythm']}; Тоны сердца: {$row['heartTones']}; Дополнительная информация по сердечно-сосудистой системе: {$row['CardioAdditionalInfo']}",
                'Состояние желудочно-кишечного тракта:' => "Цвет языка: {$row['tongueColor']}; Размер живота: {$row['bellySize']}; Пальпация живота: {$row['abdomenPalpation']}; Болевые ощущения при пальпации живота: {$row['abdominalPainPalpation']}; Стул с момента начала заболевания: {$row['stoolSinceOnset']}; Печень: {$row['liver']}; Выпячивание из-под края реберной дуги: {$row['protrusionFromRibArc']} см",
                'Состояние мочеполовой системы:' => "Мочеиспускание: {$row['urination']}; Характер мочеиспускания: {$row['natureOfUrination']}; Цвет мочи: {$row['urineColor']}; Дополнительная информация по мочеполовой системы: {$row['urineAdditionalInfo']}",
            ];

            // Display only non-empty sections
            foreach ($sections as $sectionTitle => $sectionContent) {
                // Check if sectionContent is not NULL and not an empty string
                if (isset($sectionContent) && trim($sectionContent) !== '') {
                    $pdf->Ln(3); // Adjust line height as needed
                    $pdf->SetFont('freeserif', 'B', 13, '', true);
                    $pdf->Write(0, $sectionTitle, '', 0, 'L', true, 0, false, false, 0);
                    $pdf->SetFont('freeserif', '', 12, '', true);
                    $pdf->Write(0, $sectionContent, '', 0, 'L', true, 0, false, false, 0);
                }
            }

            $pdf->Ln(6);
            $pdf->SetFont('freeserif', 'B', 12, '', true);
            $pdf->SetXY(108, $pdf->GetY());
            $pdf->Cell(0, 5, 'Ф.И.О. доктора: Шодиев М.М', 0, 1);
            $pdf->Ln(1);
            $pdf->SetXY(103, $pdf->GetY());
            $pdf->Cell(0, 5, 'Подпись доктора: ___________________________', 0, 1);

            // Output PDF
            $pdf->Output('protocol.pdf', 'I');
        } else {
            echo "Данные не найдены.";
        }
    } else {
        echo "Ошибка при выполнении запроса: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
}

$conn->close();

?>