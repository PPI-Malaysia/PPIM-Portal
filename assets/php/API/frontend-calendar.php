<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Include database configuration
require_once '../conf.php';

try {
    // Check if connection exists
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    // Get unique dates that have events
    $datesQuery = "SELECT DISTINCT DATE(start) as event_date FROM calendar_events WHERE start IS NOT NULL ORDER BY event_date ASC";
    $datesResult = $conn->query($datesQuery);
    
    if (!$datesResult) {
        throw new Exception("Error fetching event dates: " . $conn->error);
    }
    
    $eventDates = [];
    while ($row = $datesResult->fetch_assoc()) {
        $eventDates[] = $row['event_date'];
    }

    // Get all events with full details
    $eventsQuery = "SELECT calendar_events.id, user.name, title, start, end, class_name, created_at, updated_at FROM calendar_events JOIN user ON calendar_events.user_id = user.id ORDER BY start ASC";
    $eventsResult = $conn->query($eventsQuery);
    
    if (!$eventsResult) {
        throw new Exception("Error fetching events: " . $conn->error);
    }
    
    $events = [];
    while ($row = $eventsResult->fetch_assoc()) {
        $events[] = $row;
    }

    // Get active events (currently happening)
    $activeEventsQuery = "SELECT calendar_events.id, calendar_events.user_id, user.name, title, start, end, class_name, created_at, updated_at 
                         FROM calendar_events 
                         JOIN user ON calendar_events.user_id = user.id
                         WHERE start <= NOW() AND end >= NOW() 
                         ORDER BY start ASC";
    $activeEventsResult = $conn->query($activeEventsQuery);
    
    if (!$activeEventsResult) {
        throw new Exception("Error fetching active events: " . $conn->error);
    }
    
    $activeEvents = [];
    while ($row = $activeEventsResult->fetch_assoc()) {
        $activeEvents[] = $row;
    }

    // Create response
    $response = [
        'success' => true,
        'data' => [
            'event_dates' => $eventDates,
            'events' => $events,
            'active_events' => $activeEvents,
            'summary' => [
                'total_events' => count($events),
                'total_event_dates' => count($eventDates),
                'total_active_events' => count($activeEvents)
            ]
        ]
    ];

    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    $errorResponse = [
        'success' => false,
        'error' => [
            'message' => $e->getMessage()
        ]
    ];
    
    http_response_code(500);
    echo json_encode($errorResponse, JSON_PRETTY_PRINT);
} finally {
    // Close connection if it exists
    if (isset($conn)) {
        $conn->close();
    }
}
?>