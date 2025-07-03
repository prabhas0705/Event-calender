<?php
require_once 'config/database.php';
require_once 'includes/session.php';

// Ensure user is logged in
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Log the received data for debugging
    error_log("Received POST data in delete_event.php: " . print_r($_POST, true));
    
    try {
        $id = $_POST['id'];
        
        // First check if event exists
        $checkStmt = $pdo->prepare("SELECT id FROM events WHERE id = ?");
        $checkStmt->execute([$id]);
        
        if (!$checkStmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Event not found']);
            exit;
        }
        
        // Delete the event
        $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
        $result = $stmt->execute([$id]);
        
        if ($result && $stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Event deleted successfully']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete event']);
        }
    } catch(PDOException $e) {
        error_log("Error deleting event: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
