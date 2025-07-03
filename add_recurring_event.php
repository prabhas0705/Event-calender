<?php
require_once 'includes/db_connection.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';


error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
     
        error_log("Received POST data in add_recurring_event.php: " . print_r($_POST, true));

$pdo->beginTransaction();

function get_post_value($key, $default = '') {
    return trim($_POST[$key] ?? $default);
}

$title               = get_post_value('title');
$description         = get_post_value('description');
$start_datetime      = get_post_value('start_datetime');
$end_datetime        = get_post_value('end_datetime');
$category            = get_post_value('category', 'General');
$recurrence_pattern  = get_post_value('recurrence_pattern');
$recurrence_end_date = get_post_value('recurrence_end_date');
$weekdays            = isset($_POST['weekdays']) ? implode(',', $_POST['weekdays']) : '';
$monthly_day         = isset($_POST['monthly_day']) ? (int)$_POST['monthly_day'] : null;

$required_fields = [
    'title'               => $title,
    'start date/time'     => $start_datetime,
    'end date/time'       => $end_datetime,
    'recurrence pattern'  => $recurrence_pattern,
    'recurrence end date' => $recurrence_end_date
];

$errors = [];
foreach ($required_fields as $label => $value) {
    if (empty($value)) {
        $errors[] = ucfirst($label) . " is required";
    }
}

if (!empty($errors)) {
    throw new Exception("Validation errors: " . implode(", ", $errors));
}

       
$debug_data = [
    'Title'     => $title,
    'Start'     => $start_datetime,
    'End'       => $end_datetime,
    'Pattern'   => $recurrence_pattern,
    'End date'  => $recurrence_end_date
];
foreach ($debug_data as $key => $value) {
    error_log("$key: $value");
}


$insertEventQuery = "
    INSERT INTO events (
        title, description, start_datetime, end_datetime, category,
        is_recurring, recurrence_pattern, weekdays, monthly_day, recurrence_end_date
    ) VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?, ?)
";

$stmt = $pdo->prepare($insertEventQuery);
$eventData = [
    $title, $description, $start_datetime, $end_datetime,
    $category, $recurrence_pattern, $weekdays, $monthly_day, $recurrence_end_date
];

if (!$stmt->execute($eventData)) {
    throw new Exception("Failed to insert event: " . implode(", ", $stmt->errorInfo()));
}

$event_id = $pdo->lastInsertId();
error_log("Event inserted with ID: " . $event_id);

$start_timestamp = strtotime($start_datetime);
if ($start_timestamp === false) {
    throw new Exception("Invalid start datetime format");
}

$metaStmt = $pdo->prepare("
    INSERT INTO recurring_events_meta (event_id, meta_key, meta_value)
    VALUES (?, ?, ?)
");

if (!$metaStmt->execute([$event_id, 'repeat_start', $start_timestamp])) {
    throw new Exception("Failed to insert repeat_start meta: " . implode(", ", $metaStmt->errorInfo()));
}

$meta_id = $pdo->lastInsertId();
error_log("Added repeat_start meta with ID: " . $meta_id);


        $recurrence_intervals = [
            'daily' => 86400,
            'weekly' => 604800,
            'monthly' => 2592000,
        ];
        
        if (!isset($recurrence_intervals[$recurrence_pattern])) {
            throw new Exception("Invalid recurrence pattern: " . $recurrence_pattern);
        }
        
        $interval_seconds = $recurrence_intervals[$recurrence_pattern];
        

        if ($interval_seconds > 0) {
            $metaResult = $metaStmt->execute([$event_id, 'repeat_interval_' . $meta_id, $interval_seconds]);
            if (!$metaResult) {
                throw new Exception("Failed to insert interval meta: " . implode(", ", $metaStmt->errorInfo()));
            }
            error_log("Added repeat_interval meta with value: " . $interval_seconds);
        }

        $pdo->commit();
        error_log("Transaction committed successfully");

    
        $verifyEvent = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $verifyEvent->execute([$event_id]);
        $eventData = $verifyEvent->fetch(PDO::FETCH_ASSOC);
        error_log("Verified event data: " . print_r($eventData, true));

        $verifyMeta = $pdo->prepare("SELECT * FROM recurring_events_meta WHERE event_id = ?");
        $verifyMeta->execute([$event_id]);
        $metaData = $verifyMeta->fetchAll(PDO::FETCH_ASSOC);
        error_log("Verified meta data: " . print_r($metaData, true));

        echo json_encode([
            'success' => true, 
            'message' => 'Event added successfully',
            'event_id' => $event_id,
            'event_data' => $eventData,
            'meta_data' => $metaData
        ]);

    } catch (Exception $e) {
        // Roll back transaction on error
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log("Error in add_recurring_event.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Recurring Event</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <?php include 'templates/header.php'; ?>
    <?php include 'templates/navbar.php'; ?>

    <div class="container mt-4">
        <h2>Add Recurring Event</h2>
        
        <div id="errorMessages" class="alert alert-danger" style="display: none;"></div>
        <div id="successMessage" class="alert alert-success" style="display: none;"></div>

        <form id="addRecurringEventForm">
            <div class="form-group">
                <label for="title">Event Title:</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>

            <div class="form-group">
    <label for="description">Description</label>
    <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter description..."></textarea>
</div>

<div class="form-group">
    <label for="start_datetime">Start Date & Time</label>
    <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" required>
</div>

<div class="form-group">
    <label for="end_datetime">End Date & Time</label>
    <input type="datetime-local" class="form-control" id="end_datetime" name="end_datetime" required>
</div>


            <div class="form-group">
                <label for="category">Category:</label>
                <select class="form-control" id="category" name="category">
                    <?php
                    $categories = getCategories();
                    foreach ($categories as $cat) {
                        echo "<option value='{$cat['name']}'>{$cat['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="recurrence_pattern">Recurrence Pattern:</label>
                <select class="form-control" id="recurrence_pattern" name="recurrence_pattern" required>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>

            <div class="form-group" id="weekdaysGroup" style="display: none;">
                <label>Select Weekdays:</label><br>
            <div class="btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-outline-primary">
                <input type="checkbox" name="weekdays[]" value="1"> Monday
                </label>
                 <label class="btn btn-outline-primary">
                    <input type="checkbox" name="weekdays[]" value="2"> Tuesday
                    </label>
                <label class="btn btn-outline-primary">
                <input type="checkbox" name="weekdays[]" value="3"> Wednesday
                    </label>
                    <label class="btn btn-outline-primary">
                     <input type="checkbox" name="weekdays[]" value="4"> Thursday
                    </label>
                <label class="btn btn-outline-primary">
                    <input type="checkbox" name="weekdays[]" value="5"> Friday
                    </label>
                <label class="btn btn-outline-primary">
                    <input type="checkbox" name="weekdays[]" value="6"> Saturday
                </label>
                <label class="btn btn-outline-primary">
                    <input type="checkbox" name="weekdays[]" value="0"> Sunday
                </label>
            </div>
            </div>

            <div class="form-group" id="monthlyDayGroup" style="display: none;">
                <label for="monthly_day">Day of Month:</label>
            <select class="form-control" id="monthly_day" name="monthly_day">
                    <?php for($i = 1; $i <= 31; $i++): ?>
                       <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
            </select>
            </div>

            <div class="form-group">
            <label for="recurrence_end_date">End Date:</label>
            <input type="date" class="form-control" id="recurrence_end_date" name="recurrence_end_date" required>
            </div>

            <button type="submit" class="btn btn-primary">Add Recurring Event</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
       $(document).ready(function () {

// Toggle visibility based on recurrence pattern
$('#recurrence_pattern').change(function () {
    const pattern = $(this).val();
    $('#weekdaysGroup').toggle(pattern === 'weekly');
    $('#monthlyDayGroup').toggle(pattern === 'monthly');
});

// Handle form submission
$('#addRecurringEventForm').on('submit', function (e) {
    e.preventDefault();

    // Reset messages
    $('#errorMessages, #successMessage').hide().empty();

    // Prepare form data
    const formData = new FormData(this);
    formData.append('is_recurring', '1');

    // AJAX call
    $.ajax({
        url: 'add_recurring_event.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            console.log('Server response:', response);

            try {
                const result = typeof response === 'string' ? JSON.parse(response) : response;

                if (result.success) {
                    $('#successMessage')
                        .html(`Event added successfully! Event ID: ${result.event_id}`)
                        .show();

                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 2000);
                } else {
                    $('#errorMessages')
                        .html(`Error: ${result.message || 'Unknown error occurred'}`)
                        .show();
                }

            } catch (parseError) {
                console.error('Error parsing response:', parseError);
                $('#errorMessages')
                    .html('Error processing server response')
                    .show();
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', xhr.responseText);
            $('#errorMessages')
                .html(`Error adding event: ${error}`)
                .show();
        }
    });
});
});

    </script>
</body>
</html> 