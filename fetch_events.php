<?php
require_once 'config/database.php';
require_once 'includes/session.php';
require_once 'includes/db_connection.php';
require_once 'includes/functions.php';

// Ensure user is logged in
requireLogin();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the start and end dates from the calendar
$start = isset($_GET['start']) ? $_GET['start'] : date('Y-m-d');
$end = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d', strtotime('+1 month'));

try {
    $events = [];
    
    // Get regular events
    $stmt = $pdo->prepare("SELECT e.*, c.color 
                          FROM events e 
                          LEFT JOIN categories c ON e.category = c.name 
                          WHERE e.is_recurring = 0 
                          AND e.start_datetime BETWEEN ? AND ?");
    
    $stmt->execute([$start, $end]);
    $regular_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format regular events
    foreach ($regular_events as $event) {
        $events[] = [
            'id' => $event['id'],
            'title' => $event['title'],
            'description' => $event['description'],
            'start' => $event['start_datetime'],
            'end' => $event['end_datetime'],
            'color' => $event['color'] ?: '#3788d8',
            'category' => $event['category'],
            'is_recurring' => false
        ];
    }
    
    // Get recurring events for each day in the range
    $current = new DateTime($start);
    $last = new DateTime($end);
    
    while ($current <= $last) {
        $date = $current->format('Y-m-d');
        $recurring_events = getRecurringEvents($date);
        
        foreach ($recurring_events as $event) {
            // Get the category color
            $colorStmt = $pdo->prepare("SELECT color FROM categories WHERE name = ?");
            $colorStmt->execute([$event['category']]);
            $category = $colorStmt->fetch(PDO::FETCH_ASSOC);
            
            $events[] = [
                'id' => $event['id'],
                'title' => $event['title'],
                'description' => $event['description'],
                'start' => $event['start_datetime'],
                'end' => $event['end_datetime'],
                'color' => $category['color'] ?: '#3788d8',
                'category' => $event['category'],
                'is_recurring' => true,
                'recurrence_pattern' => $event['recurrence_pattern'],
                'weekdays' => $event['weekdays'],
                'monthly_day' => $event['monthly_day'],
                'recurrence_end_date' => $event['recurrence_end_date']
            ];
        }
        
        $current->modify('+1 day');
    }
    
    // Send the events as JSON
    header('Content-Type: application/json');
    echo json_encode($events);
    
} catch (PDOException $e) {
    error_log("Error in fetch_events.php: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
