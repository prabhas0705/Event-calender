<?php
// Include the database connection
require 'db.php';

// Make sure we have access to the database connection
global $pdo;

// Function to fetch all events from the database
function fetchEvents($conn) {
    // Prepare and execute the SQL statement to select all events
    $stmt = $conn->prepare("SELECT * FROM events");
    $stmt->execute();

    // Return the fetched events as an associative array
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategories() {
    global $conn;
    try {
        $stmt = $conn->query("SELECT * FROM categories ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        return array();
    }
}

function getCategoryColor($categoryName) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT color FROM categories WHERE name = ?");
        $stmt->execute([$categoryName]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['color'] : '#3788d8';
    } catch(PDOException $e) {
        error_log("Error fetching category color: " . $e->getMessage());
        return '#3788d8';
    }
}

/**
 * Sanitize user input
 * @param string $input The input to sanitize
 * @return string The sanitized input
 */
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Check if user is logged in
 * @return bool True if user is logged in, false otherwise
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect to a specific page
 * @param string $page The page to redirect to
 */
function redirect($page) {
    header("Location: $page");
    exit;
}

/**
 * Display a flash message
 * @param string $message The message to display
 * @param string $type The type of message (success, error, etc.)
 */
function set_flash_message($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get and clear flash message
 * @return array|null The flash message and type, or null if none exists
 */
function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'];
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

function getEvents($date) {
    global $pdo;
    
    if (!$pdo) {
        error_log("Database connection not available in getEvents()");
        return [];
    }
    
    try {
        // Get regular (non-recurring) events for the given date
        $query = "SELECT * FROM events 
                 WHERE DATE(start_datetime) = DATE(?) 
                 AND is_recurring = 0";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$date]);
        $regular_events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get recurring events
        $recurring_events = getRecurringEvents($date);
        
        // Merge both types of events
        return array_merge($regular_events, $recurring_events);
        
    } catch (PDOException $e) {
        error_log("Error in getEvents: " . $e->getMessage());
        return [];
    }
}

function getRecurringEvents($date) {
    global $pdo;
    
    if (!$pdo) {
        error_log("Database connection not available in getRecurringEvents()");
        return [];
    }
    
    $timestamp = strtotime($date);
    $weekday = date('w', $timestamp); // 0 (Sunday) to 6 (Saturday)
    $day_of_month = date('j', $timestamp);
    
    error_log("Fetching recurring events for date: " . $date . " (weekday: " . $weekday . ", day of month: " . $day_of_month . ")");
    
    try {
        $query = "SELECT * FROM events 
                 WHERE is_recurring = 1 
                 AND (
                     (recurrence_pattern = 'daily')
                     OR (recurrence_pattern = 'weekly' AND FIND_IN_SET(?, weekdays))
                     OR (recurrence_pattern = 'monthly' AND monthly_day = ?)
                 )
                 AND DATE(start_datetime) <= ?
                 AND (recurrence_end_date IS NULL OR DATE(recurrence_end_date) >= ?)";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$weekday, $day_of_month, $date, $date]);
        $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Found " . count($events) . " potential recurring events");
        
        $recurring_events = [];
        foreach ($events as $event) {
            // Get the time components from the original event
            $event_time = date('H:i:s', strtotime($event['start_datetime']));
            $event_duration = strtotime($event['end_datetime']) - strtotime($event['start_datetime']);
            
            // Create the event instance for this date
            $start_datetime = date('Y-m-d', $timestamp) . ' ' . $event_time;
            $end_datetime = date('Y-m-d H:i:s', strtotime($start_datetime) + $event_duration);
            
            $event_instance = array_merge($event, [
                'start_datetime' => $start_datetime,
                'end_datetime' => $end_datetime,
                'is_recurring_instance' => true
            ]);
            
            error_log("Adding recurring event: " . json_encode($event_instance));
            $recurring_events[] = $event_instance;
        }
        
        return $recurring_events;
        
    } catch (PDOException $e) {
        error_log("Error in getRecurringEvents: " . $e->getMessage());
        return [];
    }
}
?>
