<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/db_connection.php';

try {
    // Drop existing tables if they exist
    $pdo->exec("DROP TABLE IF EXISTS recurring_events_meta");
    $pdo->exec("DROP TABLE IF EXISTS events");
    
    // Create events table with all necessary fields
    $sql = "CREATE TABLE events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        start_datetime DATETIME NOT NULL,
        end_datetime DATETIME NOT NULL,
        category VARCHAR(50),
        is_recurring TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "Events table created successfully<br>";

    // Create recurring_events_meta table
    $sql = "CREATE TABLE recurring_events_meta (
        id INT AUTO_INCREMENT PRIMARY KEY,
        event_id INT NOT NULL,
        repeat_type ENUM('daily', 'weekly', 'monthly', 'yearly') NOT NULL,
        repeat_interval INT NOT NULL DEFAULT 1,
        repeat_until DATE,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "Recurring events meta table created successfully<br>";

    // Insert a test recurring event
    $pdo->beginTransaction();

    // Insert main event
    $stmt = $pdo->prepare("INSERT INTO events (title, description, start_datetime, end_datetime, category, is_recurring) 
                          VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->execute([
        'Test Recurring Event',
        'This is a test recurring event',
        '2024-03-20 10:00:00',
        '2024-03-20 11:00:00',
        'Test'
    ]);
    $eventId = $pdo->lastInsertId();
    echo "Test event inserted with ID: " . $eventId . "<br>";

    // Insert recurring meta data
    $stmt = $pdo->prepare("INSERT INTO recurring_events_meta (event_id, repeat_type, repeat_interval, repeat_until) 
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $eventId,
        'weekly',
        1,
        '2024-04-20'
    ]);
    echo "Recurring meta data inserted successfully<br>";

    $pdo->commit();

    // Verify the data
    $stmt = $pdo->query("SELECT e.*, rem.* FROM events e 
                         LEFT JOIN recurring_events_meta rem ON e.id = rem.event_id 
                         WHERE e.id = " . $eventId);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>Inserted event data:\n";
    print_r($event);
    echo "</pre>";

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "<br>";
}
?> 