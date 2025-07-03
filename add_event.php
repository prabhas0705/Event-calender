<?php
require_once 'config/database.php';
require_once 'includes/session.php';

// Ensure user is logged in
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log the received data for debugging
    error_log("Received POST data: " . print_r($_POST, true));
    
    $title = $_POST['title'] ?? '';
    $category = $_POST['category'] ?? '';
    $description = $_POST['description'] ?? '';
    $start = $_POST['start'] ?? '';
    $end = $_POST['end'] ?? '';

    // Validate required fields
    if (empty($title) || empty($start) || empty($end)) {
        echo json_encode(['status' => 'error', 'message' => 'Required fields are missing']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO events (
                title, category, description, start_datetime, end_datetime
            ) VALUES (
                :title, :category, :description, :start, :end
            )
        ");

        $result = $stmt->execute([
            ':title' => $title,
            ':category' => $category,
            ':description' => $description,
            ':start' => $start,
            ':end' => $end
        ]);

        if ($result) {
            $eventId = $pdo->lastInsertId();
            echo json_encode([
                'status' => 'success',
                'message' => 'Event added successfully',
                'event_id' => $eventId
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to add event'
            ]);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
