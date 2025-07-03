<?php
require_once 'config/database.php';
require_once 'includes/session.php';

// Ensure user is logged in
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log the received data for debugging
    error_log("Received POST data in edit_event.php: " . print_r($_POST, true));
    
    try {
        $id = $_POST['id'] ?? '';
        $title = $_POST['title'] ?? '';
        $category = $_POST['category'] ?? '';
        $description = $_POST['description'] ?? '';
        $start = $_POST['start'] ?? '';
        $end = $_POST['end'] ?? '';

        // Validate required fields
        if (empty($id) || empty($title) || empty($start) || empty($end)) {
            echo json_encode(['status' => 'error', 'message' => 'Required fields are missing']);
            exit;
        }

        // First check if event exists
        $checkStmt = $pdo->prepare("SELECT id FROM events WHERE id = ?");
        $checkStmt->execute([$id]);
        
        if (!$checkStmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Event not found']);
            exit;
        }

        // Update the event
        $stmt = $pdo->prepare("
            UPDATE events 
            SET title = :title, 
                category = :category, 
                description = :description, 
                start_datetime = :start, 
                end_datetime = :end 
            WHERE id = :id
        ");

        $result = $stmt->execute([
            ':id' => $id,
            ':title' => $title,
            ':category' => $category,
            ':description' => $description,
            ':start' => $start,
            ':end' => $end
        ]);
            
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Event updated successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update event']);
        }
    } catch(PDOException $e) {
        error_log("Error updating event: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>
