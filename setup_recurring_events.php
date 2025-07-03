<?php
require_once 'includes/db_connection.php';

try {
    // Create recurring_events_meta table
    $sql = "CREATE TABLE IF NOT EXISTS `recurring_events_meta` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `event_id` int(11) NOT NULL,
        `meta_key` varchar(255) NOT NULL,
        `meta_value` varchar(255) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `event_id` (`event_id`),
        CONSTRAINT `recurring_events_meta_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'recurring_events_meta' created successfully!";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?> 