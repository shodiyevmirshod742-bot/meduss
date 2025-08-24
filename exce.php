<?php
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

    // Определяем границы времени
    $startDateTime = date('Y-m-d') . ' 09:00:00'; // Сегодня 09:00
$endDateTime = date('Y-m-d', strtotime('+1 day')) . ' 08:00:00'; // Завтра 08:00

$sqlAssignments = "
    SELECT *
    FROM assignments
    WHERE patientId = $patientId
    AND selected_time >= '$startDateTime'
    AND selected_time < '$endDateTime'
";
    $resultAssignments = $conn->query($sqlAssignments);

    $assignments = [];
    if ($resultAssignments->num_rows > 0) {
        while($row = $resultAssignments->fetch_assoc()) {
            $assignments[] = $row;
        }
    }
} else {
    die("ID пациента не предоставлено.");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Форма назначения</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        .container > div {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px;
            box-sizing: border-box;
        }
        h3 {
            border-bottom: 2px solid #dcdcdc;
            padding-bottom: 10px;
            margin-bottom: 15px;
            color: #333;
            font-size: 16px;
        }
        .form-group {
            margin-bottom: 10px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
            font-size: 14px;
        }
        select, input[type="text"], input[type="time"], button {
            width: 100%;
            padding: 8px;
            border: 1px solid #dcdcdc;
            border-radius: 5px;
            font-size: 13px;
            box-sizing: border-box;
        }
        select {
            background-color: #ffffff;
        }
        button {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 13px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .selected-items li, .confirmed-items li {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 5px;
            margin-bottom: 3px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            font-size: 12px;
        }
        .selected-items li button, .confirmed-items li button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 10px;
            margin-left: 5px;
            padding: 0;
            line-height: 1;
            color: #007bff;
            transition: color 0.3s ease;
        }
        .selected-items li button.remove-btn:hover, .confirmed-items li button.remove-btn:hover {
            color: #dc3545;
        }
        .selected-items li button.confirm-btn:hover, .confirmed-items li button.confirm-btn:hover {
            color: #28a745;
        }
        .calendar-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .calendar-controls button {
            background-color: #007bff;
            color: #ffffff;
            border: none;
            cursor: pointer;
            padding: 2px 5px;
            border-radius: 5px;
            font-size: 10px;
            line-height: 1;
            width: 30px;
            height: 30px;
            text-align: center;
        }
        .calendar-controls button:hover {
            background-color: #0056b3;
        }
        .timeline {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 5px;
            border-top: 1px solid #dcdcdc;
            padding-top: 5px;
        }
        .timeline div {
            background-color: #ffffff;
            border: 1px solid #dcdcdc;
            padding: 4px;
            border-radius: 5px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            font-size: 10px;
            cursor: pointer;
        }
        .timeline div.selected {
            background-color: #e9ecef;
        }
        #content div {
            cursor: pointer;
            padding: 6px;
            border: 1px solid #dcdcdc;
            border-radius: 5px;
            margin-bottom: 5px;
            transition: background-color 0.3s ease;
            font-size: 9px;
        }
        #content div:hover {
            background-color: #e9ecef;
        }
        .calen {
            width: 400px;
            height: 300px;
        }
        .error-message {
            color: #dc3545;
            font-size: 14px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <form id="assignmentForm">
        <input type="hidden" id="selected_date" name="selected_date">
<input type="hidden" id="selected_time" name="selected_time">
        <input type="hidden" id="currentDateField" name="currentDateField" value="<?php echo date('Y-m-d'); ?>">
        <div id="details">
            <div class="form-group">
                <p><strong>ID пациента:</strong> <?php echo htmlspecialchars($rowPatient["patientId"]); ?></p>
                <p><strong>Ф.И.О пациента:</strong> <?php echo htmlspecialchars($rowPatient["patientName"]); ?></p>
                <p><strong>Дата рождения:</strong> <?php echo htmlspecialchars($rowPatient["dateOfBirth"]); ?></p>
            </div>
        </div>
        
        <div class="container">
            <div>
                <h3>Назначение</h3>
                <div class="form-group">
                    <label for="assignment">Выберите назначение:</label>
                    <select id="route" name="route">
  <option value="">Выберите назначение</option>
  <option value="iv_perfusor">в/в перфузор</option>
  <option value="subcutaneous">п/к</option>
  <option value="peros">per os</option>
  <option value="iv">в/в</option>
  <option value="intraarterial">в/a</option>
</select>
                </div>
            </div>

            <div>
                <h3>Содержание</h3>
                <div id="contentContainer"></div>
            </div>

            <div>
                <h3>Детали</h3>
                <div id="details">
                    <div class="form-group">
                        <label for="name">Название:</label>
                        <input type="text" id="name" name="name" >
                    </div>
                    <div class="form-group">
                        <label for="dose">Доза:</label>
                        <input type="text" id="dose" name="dose">
                    </div>
                    <div class="form-group">
                        <label for="unit">Единицы:</label>
                        <select id="unit" name="unit" >
                            <option value="mg">мг</option>
                            <option value="ml">мл</option>
                            <option value="ME">ME</option>
                            <option value="mcg">мкг</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="button" onclick="addItem()">Добавить</button>
                    </div>
                    <ul class="selected-items" id="selected-items"></ul>
                    <ul class="confirmed-items" id="confirmed-items">
        <?php if (!empty($assignments)) : ?>
            <?php foreach ($assignments as $assignment) : ?>
                <li>
                    <?php echo htmlspecialchars($assignment['name'] . ' - ' . $assignment['dose'] . ' ' . $assignment['unit'] . ' (' . $assignment['selected_date'] . ' ' . $assignment['selected_time'] . ')'); ?>
                </li>
            <?php endforeach; ?>
        <?php else : ?>
            <li>Нет назначений на сегодня и завтра.</li>
        <?php endif; ?>
    </ul>
    <div id="errorMessage"></div>

                </div>
            </div>

            <div id="calen" class="calen">
                <h3>Календарь</h3>
                <div class="calendar-controls">
                    <button type="button" id="prevDay">◄</button>
                    <span id="currentDate"></span>
                    <button type="button" id="nextDay">►</button>
                </div>
                <div class="timeline" id="timeline"></div>
                <div class="form-group">
                    <label for="calendar-time">Выберите время:</label>
                    <input type="time" id="calendar-time" name="calendar-time">
                </div>
            </div>
        </div>
        <div id="error-message" class="error-message"></div>
        <br>
        <input type="hidden" id="patientId" name="patientId" value="<?php echo htmlspecialchars($patientId); ?>">
    <button type="button" id="saveBtn">Сохранить</button>
    <br>
    <a id="printBtn" class="btn">Скачать Excel</a>
</form>

    </form>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Параметры / элементы ---
    const contentOptions = {
        iv_perfusor: ['Допамин', 'Добутамин', 'Норадреналин'],
        subcutaneous: ['Инсулин', 'Гепарин'],
        peros: ['Парацетамол', 'Ибупрофен'],
        iv: ['Цефтриаксон', 'Метронидазол'],
        intraarterial: ['Контраст', 'Химиотерапия']
    };

    const assignmentSelect = document.getElementById('route');
    const contentContainer = document.getElementById('contentContainer');
    const selectedDateHidden = document.getElementById('selected_date');
    const selectedTimeHidden = document.getElementById('selected_time');
    const patientId = document.getElementById('patientId').value;
    const calendarTimeInput = document.getElementById('calendar-time');
    const timeline = document.getElementById('timeline');
    const currentDateElement = document.getElementById('currentDate');
    const selectedItemsList = document.getElementById('selected-items');
    const confirmedItemsList = document.getElementById('confirmed-items');
    const errorMessageEl = document.getElementById('errorMessage');
    const assignment = document.getElementById('route').value;


    let currentDate = new Date();

    // --- Рендер содержимого при смене пути введения ---
    assignmentSelect.addEventListener('change', function () {
        const selectedValue = this.value;
        contentContainer.innerHTML = '';
        if (contentOptions[selectedValue]) {
            contentOptions[selectedValue].forEach(function (item) {
                const div = document.createElement('div');
                div.textContent = item;
                div.addEventListener('click', function () {
                    document.getElementById('name').value = item;
                });
                contentContainer.appendChild(div);
            });
        }
    });

    // --- Таймлайн (часы) для выбранной даты ---
    function renderTimelineForDate(baseDate) {
        timeline.innerHTML = '';
        const start = new Date(baseDate);
        start.setHours(9,0,0,0);
        const end = new Date(baseDate);
        end.setDate(end.getDate() + 1);
        end.setHours(8,0,0,0);

        for (let d = new Date(start); d <= end; d.setHours(d.getHours() + 1)) {
            const div = document.createElement('div');
            const timeText = d.getHours().toString().padStart(2, '0') + ':00';
            div.textContent = timeText;
            div.dataset.hour = d.getHours();

            div.addEventListener('click', function () {
                // определяем реальную дату для выбранного часа:
                const hour = parseInt(this.dataset.hour, 10);
                const dateForSlot = new Date(baseDate);
                if (hour < 9) dateForSlot.setDate(dateForSlot.getDate() + 1);
                const dateISO = dateForSlot.toISOString().split('T')[0];

                // Заполняем скрытые поля
                selectedDateHidden.value = dateISO;
                selectedTimeHidden.value = timeText;
                calendarTimeInput.value = timeText;

                // Подсвечиваем выбранный слот
                timeline.querySelectorAll('div').forEach(el => el.classList.remove('selected'));
                this.classList.add('selected');

                // Убираем сообщение об ошибке если было
                errorMessageEl.textContent = '';
            });

            timeline.appendChild(div);
        }
    }

    // --- Обновление календаря (показ диапазона и загрузка назначений) ---
    function updateCalendar() {
    const start = new Date(currentDate);
    start.setHours(9,0,0,0);
    const end = new Date(currentDate);
    end.setDate(end.getDate() + 1);
    end.setHours(8,0,0,0);

    currentDateElement.textContent = `${start.toLocaleDateString()} - ${end.toLocaleDateString()}`;
    renderTimelineForDate(currentDate);

    // Загрузить сохранённые назначения для текущего дня
    const dateISO = start.toISOString().split('T')[0];
    fetch(`get_assignments.php?patientId=${encodeURIComponent(patientId)}&selected_date=${encodeURIComponent(dateISO)}`)
        .then(res => res.json())
        .then(data => {
            // !!! ОЧИСТКА перед отрисовкой !!!
            confirmedItemsList.innerHTML = '';
            displaySavedAssignments(data);
        })
        .catch(err => {
            console.error('Ошибка при получении назначений:', err);
        });
}

    // --- Добавление пункта в выбранные (до подтверждения) ---

window.addItem = function () {
    const name = document.getElementById('name').value.trim();
    const dose = document.getElementById('dose').value.trim();
    const unit = document.getElementById('unit').value;
    const assignment = document.getElementById('route').value; // <-- берём выбранное назначение
    const sd = document.getElementById('selected_date').value;
    const st = document.getElementById('selected_time').value;

    if (!name || !dose || !unit || !assignment || !sd || !st) {
        errorMessageEl.textContent = 'Пожалуйста, заполните все поля и выберите дату/время.';
        return;
    }
    errorMessageEl.textContent = '';

    const li = document.createElement('li');
    li.dataset.name = name;
    li.dataset.dose = dose;
    li.dataset.unit = unit;
    li.dataset.assignment = assignment; // <-- сохраняем в dataset
    li.dataset.date = sd;
    li.dataset.time = st;

    li.innerHTML = `<span>${name} - ${dose} ${unit} (${sd} ${st})</span>`;

    // кнопка подтверждения
    const confirmButton = document.createElement('button');
    confirmButton.type = 'button';
    confirmButton.textContent = '✔';
    confirmButton.className = 'confirm-btn';
    confirmButton.addEventListener('click', function () {
        const confirmedLi = document.createElement('li');
        confirmedLi.classList.add('confirmed', 'unsaved');
        confirmedLi.dataset.name = li.dataset.name;
        confirmedLi.dataset.dose = li.dataset.dose;
        confirmedLi.dataset.unit = li.dataset.unit;
        confirmedLi.dataset.assignment = li.dataset.assignment; // <-- переносим в подтверждённый
        confirmedLi.dataset.date = li.dataset.date;
        confirmedLi.dataset.time = li.dataset.time;
        confirmedLi.innerHTML = `<span>${li.dataset.name} - ${li.dataset.dose} ${li.dataset.unit} (${li.dataset.date} ${li.dataset.time})</span>`;
        confirmedItemsList.appendChild(confirmedLi);
        li.remove();
    });

    // кнопка удаления
    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.textContent = '✘';
    removeButton.className = 'remove-btn';
    removeButton.addEventListener('click', function () {
        li.remove();
    });

    li.appendChild(confirmButton);
    li.appendChild(removeButton);
    selectedItemsList.appendChild(li);

    // очистка полей
    document.getElementById('name').value = '';
    document.getElementById('dose').value = '';
    document.getElementById('unit').value = 'mg';
    document.getElementById('route').value = '';
};

 document.getElementById('saveBtn').addEventListener('click', async function (e) {
    e.preventDefault();

    const assignments = Array.from(document.querySelectorAll('#confirmed-items li.unsaved'));
    if (assignments.length === 0) {
        alert('Нет подтверждённых назначений для сохранения.');
        return;
    }

    for (const item of assignments) {
        const formData = new FormData();
        formData.append('patientId', patientId);
        formData.append('name', item.dataset.name);
        formData.append('dose', item.dataset.dose);
        formData.append('unit', item.dataset.unit);
        formData.append('assignment', item.dataset.assignment); // <-- отправляем в БД
        formData.append('selected_date', item.dataset.date);
        formData.append('selected_time', item.dataset.time);

        await fetch('save_assignment.php', { method: 'POST', body: formData });
        item.classList.remove('unsaved');
    }

    alert('Все назначения успешно сохранены.');
    updateCalendar();
});

    // --- Показ сохранённых назначений (из get_assignments.php или initial PHP) ---
    function displaySavedAssignments(assignments) {
        confirmedItemsList.innerHTML = '';
        if (!Array.isArray(assignments) || assignments.length === 0) {
            const li = document.createElement('li');
            li.textContent = 'Нет назначений на выбранный период.';
            confirmedItemsList.appendChild(li);
            return;
        }

        assignments.forEach(a => {
            const li = document.createElement('li');
            li.dataset.name = a.name;
            li.dataset.dose = a.dose;
            li.dataset.unit = a.unit;
            li.dataset.date = a.selected_date;
            li.dataset.time = a.selected_time;
            li.dataset.route = a.route;
            li.textContent = `${a.name} - ${a.dose} ${a.unit} (${a.selected_date} ${a.selected_time})`;
            confirmedItemsList.appendChild(li);
        });
    }



    updateCalendar();

    // Кнопки переключения дней
    document.getElementById('prevDay').addEventListener('click', function () {
        currentDate.setDate(currentDate.getDate() - 1);
        updateCalendar();
    });
    document.getElementById('nextDay').addEventListener('click', function () {
        currentDate.setDate(currentDate.getDate() + 1);
        updateCalendar();
    });
});

function getUrlParam(name) {
    return new URLSearchParams(window.location.search).get(name);
}

const patientId = getUrlParam('patientId') || 0;
const startDate = '2025-08-12';
const endDate = '2025-08-13';
const startTime = '09:00:00';
const endTime = '08:00:00';

document.getElementById('printBtn').href =
    `excel.php?patientId=${patientId}&startDate=${startDate}&endDate=${endDate}&startTime=${startTime}&endTime=${endTime}`;
    
</script>
</body>
</html>