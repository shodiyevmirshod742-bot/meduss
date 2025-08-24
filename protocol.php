<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Example: Set the content type and character set
header('Content-Type: text/html; charset=utf-8');

$patientId = isset($_GET['patientId']) ? $_GET['patientId'] : null;

$host = "localhost";
$username = "root";
$password = "root";
$database = "myp";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($patientId !== null) {
    $patientId = $conn->real_escape_string($patientId);

    // Получаем данные пациента
    $sqlPatient = "SELECT * FROM patients WHERE patientId = $patientId";
    $resultPatient = $conn->query($sqlPatient);

    if ($resultPatient->num_rows > 0) {
        $rowPatient = $resultPatient->fetch_assoc();
    } else {
        die("Пациент не найден.");
    }
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $protocolName = isset($_POST['protocolName']) ? $conn->real_escape_string($_POST['protocolName']) : '';
    $complaintData = isset($_POST['complaintData']) ? $conn->real_escape_string($_POST['complaintData']) : '';

    // Check if the query is not empty before executing
    if (!empty($patientId) && !empty($protocolName) && !empty($complaintData)) {
        // Fetch the correct patientId from the patients table
        $patientIdQuery = "SELECT patientId FROM patients WHERE patientId = '$patientId'";
        $patientIdResult = $conn->query($patientIdQuery);

        if ($patientIdResult->num_rows > 0) {
            // Construct the SQL query
            $sqlInsert = "INSERT INTO protocols (patientId, protocolName, complaintData) VALUES ('$patientId', '$protocolName', '$complaintData')";

            // Execute the query
            $result = $conn->query($sqlInsert);

            if ($result === false) {
                echo "Error: " . $conn->error;
            } else {
                echo "Протокол успешно вставлен!";
            }
        } else {
            echo "Ошибка: Пациент не найден.";
        }
    } else {
        echo "Ошибка: Пустое название протокола или данные жалобы.";
    }
}

// Fetch and display existing protocols
$protocolSql = "SELECT protocolName FROM protocols WHERE patientId = '$patientId'";
$protocolResult = $conn->query($protocolSql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Протоколы для пациентов</title>
    <style>
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        label {
            display: block;
            margin-bottom: 8px;
            cursor: pointer;
        }

        ..button1-page {
        max-width: 800px;
        margin: auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .left-column,
    .right-column {
        flex: 1;
    }

    .right-column {
        margin-top: 20px;
    }

    .form-container {
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 8px;
    }

    textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 16px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

        .protocol-list {
            list-style-type: none;
            padding: 0;
        }

        .protocol-list-item {
            margin-bottom: 10px;
            background-color: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .protocol-list-item span {
            flex-grow: 1;
            cursor: pointer;
        }

        .protocol-list-item button {
            margin-left: 10px;
            background-color: #4CAF50; /* Green */
            color: white;
            border: none;
            padding: 8px 16px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .protocol-list-item button.delete-button {
            background-color: #f44336; /* Red */
        }

        .protocol-list-item button.delete-button:hover {
            background-color: #d32f2f;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .go-back-btn {
            text-align: center;
            margin-top: 20px;
        }

        .go-back-btn button {
            background-color: #808080;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .go-back-btn button:hover {
            background-color: #555555;
        }
        .toggle-button {
            margin-left: 520px;
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 10px;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .toggle-icon {
        margin-right: 5px;
    }

    .toggle-button:hover {
        background-color: #45a049;
    }

    .form-container {
        display: none; /* Hide the content by default */
    }

    .form-container.open {
        display: block; /* Show the content when it has the 'open' class */
    }

    .objective-studies-container {
        display: flex;
        flex-wrap: wrap;
    }

    .objective-studies-item {
        flex: 0 0 100%; /* Set to 100% width */
        margin-bottom: 10px; /* Add margin to create space between items */
    }
    </style>
</head>
<body>

    <div class="button1-page">
        <div class="left-column">
            <h2>Список протоколов</h2>
            <ul class="protocol-list">
                <?php
                if ($protocolResult === false) {
                    echo "Error: " . $conn->error;
                } else {
                    // Check if there are rows returned
                    if ($protocolResult->num_rows > 0) {
                        echo '<ul class="protocol-list">';
                        while ($protocolRow = $protocolResult->fetch_assoc()) {
                            echo '<li class="protocol-list-item">';
                            echo '<span onclick="viewProtocol(\'' . $protocolRow["protocolName"] . '\')">' . $protocolRow["protocolName"] . '</span>';
                            echo '<button class="delete-button" onclick="deleteProtocol(\'' . $protocolRow["protocolName"] . '\')">Удалить</button>';
                            echo '<button onclick="printProtocol(\'' . $protocolRow["protocolName"] . '\')">Печать</button>';
                            echo '<button onclick="editProtocol(\'' . $protocolRow["protocolName"] . '\')">Изменить</button>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>Протоколы не найдены</p>';
                    }
                }
                ?>
    </ul>
</div>
        <div class="right-column">
            <h2 id="complaintDataTitle">Заполнение протокола</h2><button class="toggle-button" onclick="toggleContent('complaintDataContent')"><span class="toggle-icon">Открыть▼Закрыть</span></button>
            <h3></h3>
            <div class="form-container" id="complaintDataContent">
                <!-- Add your form fields for Complaint, Anamnesis, etc. here -->
                <form action="process_button1.php" method="post">

                    <input type="hidden" name="patientId" value="<?php echo $patientId; ?>">

                    <label for="protocolName">Название протокола:</label>
                    <input type="text" id="protocolName" name="protocolName" required>

                    <label for="complaintData">Жалобы:</label>
                    <textarea type="text" id="complaintData" name="complaintData" rows="20" cols="50"></textarea>

                    <label for="medicalHistory">Анамнез заболевания:</label>
                    <textarea type="text" id="medicalHistory" name="medicalHistory" rows="20" cols="50"></textarea>

                    <label for="lifeHistory">Анамнез жизни:</label>
                    <textarea type="text" id="lifeHistory" name="lifeHistory" rows="20" cols="50"></textarea>

                    <label for="objectiveStudies">Объективный статус:</label>
<div class="objective-studies-container">
    <div class="objective-studies-item">
        <label for="generalCondition">Общее состояние:</label>
        <select id="generalCondition" name="generalCondition">
            <option value="moderate">Удовлетворительное</option>
            <option value="severe">Тяжелое</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="consciousness">Сознание:</label>
        <select id="consciousness" name="consciousness">
            <option value="comatose">Коматозное</option>
            <option value="clear">Ясное</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="nutrition">Питание:</label>
        <select id="nutrition" name="nutrition">
            <option value="not-satisfactory">Неудовлетворительное</option>
            <option value="satisfactory">Удовлетворительное</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="skinColor">Цвет кожи:</label>
        <select id="skinColor" name="skinColor">
            <option value="pale-pink">Бледно-розовый</option>
            <option value="pale">Бледный</option>
            <option value="hyperemic">Гиперемированный</option>
            <option value="cyanosis">Цианоз</option>
            <option value="jaundice">Желтуха</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="skinMoisture">Влажность кожи:</label>
        <select id="skinMoisture" name="skinMoisture">
            <option value="moist">Влажная</option>
            <option value="normal">Нормальная</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="turgor">Тургор:</label>
        <select id="turgor" name="turgor">
            <option value="preserved">Сохранен</option>
            <option value="not-preserved">Не сохранен</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="edema">Отеки:</label>
        <select id="edema" name="edema">
            <option value="face">В лицевой области</option>
            <option value="lower-extremity">В нижних конечностях</option>
            <option value="lower-third-limb">В нижней трети нижней конечности</option>
            <option value="missing">Отсутствуют</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="additionalInfo">Дополнительная информация:</label>
        <textarea id="additionalInfo" name="additionalInfo" rows="4" cols="50"></textarea>
    </div>
</div>

<div class="objective-studies-group">
    <h4>Состояние дыхательной системы</h4>

    <div class="objective-studies-item">
        <label for="respiratoryRhythm">Ритм дыхания:</label>
        <select id="respiratoryRhythm" name="respiratoryRhythm">
            <option value="normal">Нормальный ритм</option>
            <option value="deep-frequent">Глубокий и частый</option>
            <option value="tachypnea">Тахипноэ</option>
            <option value="bradypnea">Брадипноэ</option>
            <option value="apnea">Апноэ</option>
            <option value="cheyne-stokes">Дыхание Чейна-Стокса</option>
            <option value="biota">Биота</option>
            <option value="kussmaul">Дыхание Куссмаула</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="respiratoryRate">ЧДД (дыхательная частота в минуту):</label>
        <input type="number" id="respiratoryRate" name="respiratoryRate" min="0" max="100">
    </div>

    <div class="objective-studies-item">
        <label for="SPO2">SPO2:</label>
        <input type="text" id="SPO2" name="SPO2">
    </div>

    <div class="objective-studies-item">
        <label for="breathingNature">Характер дыхания:</label>
        <select id="breathingNature" name="breathingNature">
            <option value="vesicular">Везикулярное</option>
            <option value="hard">Тяжелое</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="wheezing">Хрипы:</label>
        <select id="wheezing" name="wheezing">
            <option value="yes">Да</option>
            <option value="no">Нет</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="pleuralFrictionNoise">Плевральный трение:</label>
        <select id="pleuralFrictionNoise" name="pleuralFrictionNoise">
            <option value="yes">Да</option>
            <option value="no">Нет</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="respiratoryAdditionalInfo">Дополнительная информация:</label>
        <textarea id="respiratoryAdditionalInfo" name="respiratoryAdditionalInfo" rows="4" cols="50"></textarea>
    </div>
</div>

<div class="objective-studies-group">
    <h4>Состояние сердечно-сосудистой системы</h4>

    <div class="objective-studies-item">
        <label for="hemodynamics">Гемодинамика:</label>
        <select id="hemodynamics" name="hemodynamics">
            <!-- Options for hemodynamics -->
            <option value="stable">Стабильная</option>
            <option value="unstable">Неустойчивая</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="systolicPressure">Систолическое давление:</label>
        <input type="number" id="systolicPressure" name="systolicPressure" min="0">
    </div>

    <div class="objective-studies-item">
        <label for="diastolicPressure">Диастолическое давление:</label>
        <input type="number" id="diastolicPressure" name="diastolicPressure" min="0">
    </div>

    <div class="objective-studies-item">
        <label for="heartRate">Частота сердечных сокращений:</label>
        <input type="number" id="heartRate" name="heartRate" min="0">
    </div>

    <div class="objective-studies-item">
        <label for="heartRhythm">Ритм сердца:</label>
        <select id="heartRhythm" name="heartRhythm">
            <!-- Options for heart rhythm -->
            <option value="not-disturbed">Не нарушен</option>
            <option value="atrial-fibrillation">Мерцание предсердий</option>
            <option value="atrial-flutter">Предсердное мерцание</option>
            <option value="WES">WES</option>
            <option value="WT">WT</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="heartTones">Тоны сердца:</label>
        <select id="heartTones" name="heartTones">
            <!-- Options for heart tones -->
            <option value="muted">Оглушены</option>
            <option value="not-muted">Не оглушены</option>
        </select>
    </div>
    <div class="objective-studies-item">
        <label for="CardioAdditionalInfo">Дополнительная информация:</label>
        <textarea id="CardioAdditionalInfo" name="CardioAdditionalInfo" rows="4" cols="50"></textarea>
    </div>
</div>
<div class="objective-studies-group">
    <h4>Состояние желудочно-кишечного тракта</h4>

    <div class="objective-studies-item">
        <label for="tongueColor">Цвет языка:</label>
        <select id="tongueColor" name="tongueColor">
            <!-- Options for tongue color -->
            <option value="light-pink">Светло-розовый</option>
            <option value="yellow">Желтый</option>
            <option value="white-gray">Белый/серый</option>
            <option value="purple">Фиолетовый</option>
            <option value="bright-red">Ярко-красный</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="bellySize">Размер живота:</label>
        <select id="bellySize" name="bellySize">
            <!-- Options for belly size -->
            <option value="enlarged">Увеличен</option>
            <option value="not-enlarged">Не увеличен</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="abdomenPalpation">Пальпация живота:</label>
        <select id="abdomenPalpation" name="abdomenPalpation">
            <!-- Options for abdomen palpation -->
            <option value="soft">Мягкий</option>
            <option value="dense">Плотный</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="abdominalPainPalpation">Боль при пальпации живота:</label>
        <select id="abdominalPainPalpation" name="abdominalPainPalpation">
            <!-- Options for abdominal pain on palpation -->
            <option value="yes">Да</option>
            <option value="no">Нет</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="stoolSinceOnset">Стул с момента начала заболевания:</label>
        <select id="stoolSinceOnset" name="stoolSinceOnset">
            <!-- Options for stool since onset -->
            <option value="was">Был</option>
            <option value="was-not">Не был</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="liver">Печень:</label>
        <select id="liver" name="liver" onchange="handleLiverSelection()">
            <!-- Options for liver -->
            <option value="protrudes">Выступает из-под края реберной дуги</option>
            <option value="not-protrudes">Не выступает из-под края реберной дуги</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="protrusionFromRibArc">Выпячивание из-под края реберной дуги:</label>
        <select id="protrusionFromRibArc" name="protrusionFromRibArc">
            <!-- Options for protrusion from rib arc -->
            <option value="2-cm">2 см</option>
            <option value="4-cm">4 см</option>
            <option value="6-cm">6 см</option>
        </select>
    </div>
</div>
<div class="objective-studies-group">
    <h3>Состояние мочеполовой системы</h3>

    <div class="objective-studies-item">
        <label for="urination">Мочеиспускание:</label>
        <select id="urination" name="urination">
            <option value="broken">Нарушено</option>
            <option value="not-broken">Не нарушено</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="natureOfUrination">Характер мочеиспускания:</label>
        <select id="natureOfUrination" name="natureOfUrination">
            <option value="painless">Безболезненное</option>
            <option value="painful">Болезненное</option>
        </select>
    </div>

    <div class="objective-studies-item">
        <label for="urineColor">Цвет мочи:</label>
        <select id="urineColor" name="urineColor">
            <option value="light-yellow">Светло-желтый</option>
            <option value="yellow">Желтый</option>
            <option value="dark-yellow">Темно-желтый</option>
            <option value="green-yellow">Зелено-желтый</option>
            <option value="orange">Оранжевый</option>
            <option value="brown">Коричневый</option>
            <option value="red">Красный</option>
        </select>
    </div>
</div>


                    <label for="diagnosis">Диагнозы:</label>
                    <textarea type="text" id="diagnosis" name="diagnosis" rows="20" cols="50"></textarea>

                    <label for="conclusion">Заключение:</label>
                    <textarea type="text" id="conclusion" name="conclusion" rows="20" cols="50"></textarea>

                    <label for="therapy">Лечение:</label>
                    <textarea type="text" id="therapy" name="therapy" rows="20" cols="50"></textarea>

                    <label for="recommendations">Рекомендации:</label>
                    <textarea type="text" id="recommendations" name="recommendations" rows="20" cols="50"></textarea>

                    <input type="submit" name="submit" value="Добавить">
                </form>
            </div>
        </div>
    </div>

    <div class="go-back-btn">
        <button onclick="goBackToPatientDetails()">Вернуться к сведениям о пациенте</button>
    </div>

<script>

    function toggleContent(contentId) {
        var content = document.getElementById(contentId);
        content.classList.toggle('open');
    }

    function goBackToPatientDetails() {
        // Modify the URL to go back to the patient details page
        window.location.href = 'view_patient.php?id=<?php echo $patientId; ?>';
    }

    function viewProtocol(protocolName) {
        // Navigate to a new page to view the protocol
        window.location.href = 'view_protocol.php?protocolName=' + protocolName + '&id=<?php echo $patientId; ?>';
    }

    function printProtocol(protocolName) {
    // Open a new window for printing the protocol in A4 format
    window.open('generate_pdf.php?protocolName=' + protocolName + '&id=<?php echo $patientId; ?>', '_blank');
}

    function editProtocol(protocolName) {
        // Navigate to a new page to edit the protocol
        window.location.href = 'edit_protocol.php?protocolName=' + protocolName + '&id=<?php echo $patientId; ?>';
    }

    function deleteProtocol(protocolName) {
        var confirmationCode = prompt("Пожалуйста, введите код подтверждения, чтобы удалить протокол:");

        // Check if the confirmation code is correct (customize this code)
        if (confirmationCode === "2002") {
            // Perform deletion logic here

            // Remove the protocol from the list
            var protocolList = document.querySelector('.protocol-list');
            var protocolElements = protocolList.querySelectorAll('.protocol-list-item span');

            for (var i = 0; i < protocolElements.length; i++) {
                if (protocolElements[i].textContent === protocolName) {
                    protocolElements[i].parentNode.remove();
                    break;
                }
            }

            // Implement deletion logic from the database
            deleteProtocolFromDatabase(protocolName);
        } else {
            alert("Неверный код подтверждения. Удаление отменено.");
        }
    }

    function deleteProtocolFromDatabase(protocolName) {
        // Use AJAX to send a request to a server-side script for database deletion
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_protocol.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = xhr.responseText;
                if (response === "success") {
                    alert("Протокол '" + protocolName + "'Успешно удален из базы данных.");
                } else {
                    alert("Не удалось удалить протокол из базы данных.");
                }
            }
        };

        // Send the protocolName as a parameter to the server-side script
        var params = "protocolName=" + encodeURIComponent(protocolName);
        xhr.send(params);
    }

    // Custom jQuery function to filter elements containing specific text
    jQuery.expr[':'].contains = function(a, i, m) {
        return jQuery(a).text().toUpperCase()
            .indexOf(m[3].toUpperCase()) >= 0;
    };

     function handleLiverSelection() {
        var liverSelect = document.getElementById('liver');
        var protrusionInput = document.getElementById('protrusionFromRibArc');

        // Check if the selected value for liver is 'protrudes'
        if (liverSelect.value === 'protrudes') {
            // Enable the protrusion input
            protrusionInput.disabled = false;
        } else {
            // Disable and clear the protrusion input
            protrusionInput.disabled = true;
            protrusionInput.value = '';
        }
    }
</script>

</body>
</html>