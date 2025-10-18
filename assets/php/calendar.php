<?php
// calendar.php - Calendar functionality

// Define ROOT_PATH if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
}

require_once(ROOT_PATH . "assets/php/main.php");

class Calendar extends ppim {

    /**
     * Constructor - Initialize with access control
     */
    public function __construct() {
        try {
            parent::__construct();
            
            if (!$this->conn) {
                throw new Exception("Database connection not established");
            }
            
            if (!$this->hasAccess()) {
                header('Location: /access-denied.php');
                exit();
            }
            
        } catch (Exception $e) {
            $this->showAlert('Constructor Error: ' . htmlspecialchars($e->getMessage()), 'danger');
        }
    }

    /**
     * Format datetime for MySQL
     * Convert from ISO 8601 format to MySQL datetime format
     * @param string $datetime
     * @return string
     */
    private function formatDatetime($datetime) {
        if (empty($datetime)) {
            return null;
        }
        
        try {
            // Parse the ISO 8601 datetime (which is in UTC)
            $date = new DateTime($datetime, new DateTimeZone('UTC'));
            // Convert to Malaysia time
            $date->setTimezone(new DateTimeZone('Asia/Kuala_Lumpur'));
            return $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            error_log("Error formatting date: " . $e->getMessage() . " for value: " . $datetime);
            return null;
        }
    }
    
    /**
     * @param mixed $data
     * @param int $status
     */
    private function sendJson($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Handle incoming API requests
     */
    public function handleRequest() {
        try {
            // Read raw input once
            $raw = file_get_contents('php://input');
            $input = null;
            if (!empty($raw)) {
                $maybeJson = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($maybeJson)) {
                    $input = $maybeJson;
                }
            }

            // Prefer explicit action from JSON or form data
            $action = null;
            if (is_array($input) && isset($input['action'])) {
                $action = $input['action'];
                foreach ($input as $k => $v) { $_POST[$k] = $v; }
            } else {
                $action = isset($_POST['action']) ? $_POST['action'] : null;
            }

            // If POST JSON contains event fields but no action, treat as add
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($action) && is_array($input)) {
                if (isset($input['title']) && isset($input['start'])) {
                    $action = 'add';
                    // ensure $_POST keys for existing case handlers
                    foreach ($input as $k => $v) { $_POST[$k] = $v; }
                }
            }

            switch ($action) {
                case 'list':
                    $events = $this->getAllEvents();
                    $this->sendJson(['success' => true, 'events' => $events]);
                    break;

                case 'upcoming':
                    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
                    $events = $this->getUpcomingEventsForDisplay($limit);
                    $this->sendJson(['success' => true, 'events' => $events]);
                    break;

                case 'add':
                    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
                    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
                    $start = isset($_POST['start']) ? $_POST['start'] : null;
                    $end = isset($_POST['end']) ? $_POST['end'] : null;
                    $className = isset($_POST['className']) ? trim($_POST['className']) : '';

                    if (empty($title) || empty($start)) {
                        $this->sendJson(['success' => false, 'message' => 'Missing required fields (title or start).'], 400);
                    }

                    $newId = $this->addEvent($title, $description, $start, $className, $end);
                    if ($newId !== false) {
                        $this->sendJson(['success' => true, 'id' => $newId]);
                    } else {
                        $this->sendJson(['success' => false, 'message' => 'Insert failed.'], 500);
                    }
                    break;

                case 'update':
                    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
                    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
                    $className = isset($_POST['className']) ? trim($_POST['className']) : '';

                    if ($id <= 0 || empty($title)) {
                        $this->sendJson(['success' => false, 'message' => 'Missing required fields (id or title).'], 400);
                    }

                    if ($this->updateEvent($id, $title, $description, $className)) {
                        $this->sendJson(['success' => true]);
                    } else {
                        $this->sendJson(['success' => false, 'message' => 'Update failed or not owner.'], 403);
                    }
                    break;

                case 'delete':
                    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                    if ($id <= 0) {
                        $this->sendJson(['success' => false, 'message' => 'Missing id.'], 400);
                    }

                    if ($this->deleteEvent($id)) {
                        $this->sendJson(['success' => true]);
                    } else {
                        $this->sendJson(['success' => false, 'message' => 'Delete failed or not owner.'], 403);
                    }
                    break;

                case 'move': // drag & drop update dates
                    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
                    $start = isset($_POST['start']) ? $_POST['start'] : null;
                    $end = isset($_POST['end']) ? $_POST['end'] : null;

                    if ($id <= 0 || empty($start)) {
                        $this->sendJson(['success' => false, 'message' => 'Missing id or start.'], 400);
                    }

                    if ($this->updateEventDates($id, $start, $end)) {
                        $this->sendJson(['success' => true]);
                    } else {
                        $this->sendJson(['success' => false, 'message' => 'Update dates failed or not owner.'], 403);
                    }
                    break;

                default:
                    $this->sendJson(['success' => false, 'message' => 'Unknown action.'], 400);
                    break;
            }
        } catch (Exception $e) {
            error_log("handleRequest error: " . $e->getMessage());
            $this->sendJson(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    
    /**
     * Check if user has access to student database
     * @return boolean
     */
    private function hasAccess() {
        return $this->hasPermission("calendar_access");
    }

    /**
     * Get all calendar events
     * @return array
     */
    public function getAllEvents() {
        // Fixed the column name from u.user_name to u.name
        $stmt = $this->conn->prepare("SELECT e.*, u.name as creator_name 
                                      FROM calendar_events e 
                                      LEFT JOIN user u ON e.user_id = u.id 
                                      ORDER BY e.start ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'start' => $row['start'],
                'end' => $row['end'] ? $row['end'] : $row['start'],
                'category' => $row['event_type'],
                'extendedProps' => [
                    'user_id' => $row['user_id'],
                    'creator_name' => $row['creator_name']
                ]
            ];
        }
        
        return $events;
    }
    /**
     * Get ongoing and future calendar events
     * Returns events that are currently happening or will happen in the future
     * @param int $limit Maximum number of events to return (default: 10)
     * @return array
     */
    public function getOngoingAndFutureEvents($limit = 10) {
        // Get current time in Malaysia timezone to match stored dates
        $now = new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur'));
        $currentTime = $now->format('Y-m-d H:i:s');
        
        $stmt = $this->conn->prepare("SELECT e.*, u.name as creator_name 
                                    FROM calendar_events e 
                                    LEFT JOIN user u ON e.user_id = u.id 
                                    WHERE (e.start >= ? OR (e.start < ? AND (e.end IS NULL OR e.end >= ?)))
                                    ORDER BY e.start ASC 
                                    LIMIT ?");
        $stmt->bind_param("sssi", $currentTime, $currentTime, $currentTime, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'start' => $row['start'],
                'end' => $row['end'] ? $row['end'] : $row['start'],
                'category' => $row['event_type'],
                'extendedProps' => [
                    'user_id' => $row['user_id'],
                    'creator_name' => $row['creator_name']
                ]
            ];
        }
        
        return $events;
    }

    /**
     * Calculate time until event starts
     * @param string $eventDate
     * @return string
     */
    public function getTimeUntilEvent($eventDate) {
        $now = new DateTime('now', new DateTimeZone('Asia/Kuala_Lumpur'));
        $event = new DateTime($eventDate, new DateTimeZone('Asia/Kuala_Lumpur'));
        
        $diff = $now->diff($event);
        
        if ($event < $now) {
            return "Ongoing";
        }
        
        if ($diff->days == 0) {
            if ($diff->h > 0) {
                return "in " . $diff->h . " hour" . ($diff->h > 1 ? "s" : "");
            } else {
                return "in " . $diff->i . " minute" . ($diff->i > 1 ? "s" : "");
            }
        } elseif ($diff->days == 1) {
            return "Tomorrow";
        } else {
            return "in " . $diff->days . " day" . ($diff->days > 1 ? "s" : "");
        }
    }

    /**
     * Format event date range for display
     * @param string $startDate
     * @param string $endDate
     * @return string
     */
    public function formatEventDate($startDate, $endDate = null) {
        $start = new DateTime($startDate, new DateTimeZone('Asia/Kuala_Lumpur'));
        
        if ($endDate && $endDate !== $startDate) {
            $end = new DateTime($endDate, new DateTimeZone('Asia/Kuala_Lumpur'));
            
            // Same day event
            if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
                return $start->format('j M Y, g:i A') . ' - ' . $end->format('g:i A');
            } else {
                // Multi-day event
                return $start->format('j M Y') . ' - ' . $end->format('j M Y');
            }
        } else {
            return $start->format('j M Y, g:i A');
        }
    }

    /**
     * Get upcoming events with formatted data ready for display
     * @param int $limit
     * @return array
     */
    public function getUpcomingEventsForDisplay($limit = 10) {
        $events = $this->getOngoingAndFutureEvents($limit);
        $formattedEvents = [];
        
        foreach ($events as $event) {
            $formattedEvents[] = [
                'id' => $event['id'],
                'title' => $event['title'],
                'description' => $event['description'],
                'start' => $event['start'],
                'end' => $event['end'],
                'creator_name' => $event['extendedProps']['creator_name'],
                'user_id' => $event['extendedProps']['user_id'],
                'formatted_date' => $this->formatEventDate($event['start'], $event['end']),
                'time_until' => $this->getTimeUntilEvent($event['start']),
                'is_ongoing' => ($this->getTimeUntilEvent($event['start']) === "Ongoing")
            ];
        }
        
        return $formattedEvents;
    }

    /**
     * Add a new calendar event
     * @param string $title
     * @param string $description
     * @param string $start
     * @param string $className
     * @param string $end
     * @return int|bool
     */
    public function addEvent($title, $description, $start, $className, $end = null) {
        // Format the datetime values for MySQL
        $formattedStart = $this->formatDatetime($start);
        $formattedEnd = $this->formatDatetime($end);
        
        // Log the original and formatted values for debugging
        error_log("Original start: $start, Formatted: $formattedStart");
        error_log("Original end: $end, Formatted: $formattedEnd");
        
        $stmt = $this->conn->prepare("INSERT INTO calendar_events (user_id, title, description, start, end, event_type) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $this->user_id, $title, $description, $formattedStart, $formattedEnd, $className);
        
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }
    
    /**
     * Update an existing calendar event
     * @param int $id
     * @param string $title
     * @param string $className
     * @return bool
     */
    public function updateEvent($id, $title, $description, $className) {
        // Check if user owns the event or has permission to edit others' events
        if (!$this->isEventOwner($id) && !$this->hasPermission('calendar_edit_others')) {
            error_log("updateEvent: permission denied for user {$this->user_id} on event {$id}");
            return false;
        }

        if ($this->hasPermission('calendar_edit_others')) {
            // User can edit any event
            $stmt = $this->conn->prepare("UPDATE calendar_events 
                                          SET title = ?, description = ?, event_type = ? 
                                          WHERE id = ?");
            if (!$stmt) {
                error_log("updateEvent prepare failed (admin): " . $this->conn->error);
                return false;
            }
            $stmt->bind_param("sssi", $title, $description, $className, $id);
        } else {
            // User can only edit their own event
            $stmt = $this->conn->prepare("UPDATE calendar_events 
                                          SET title = ?, description = ?, event_type = ? 
                                          WHERE id = ? AND user_id = ?");
            if (!$stmt) {
                error_log("updateEvent prepare failed: " . $this->conn->error);
                return false;
            }
            $stmt->bind_param("sssii", $title, $description, $className, $id, $this->user_id);
        }

        if (!$stmt->execute()) {
            error_log("updateEvent execute failed: " . $stmt->error);
            return false;
        }

        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected === 0) {
            error_log("updateEvent: no rows affected for event {$id} (maybe no changes).");
        }

        return $affected >= 0; // true if query ran (0 = no change, >0 = changed)
    }
    
    /**
     * Delete a calendar event
     * @param int $id
     * @return bool
     */
    public function deleteEvent($id) {
        // First check if user owns this event or has permission to delete others' events
        if (!$this->isEventOwner($id) && !$this->hasPermission('calendar_delete_others')) {
            return false;
        }

        if ($this->hasPermission('calendar_delete_others')) {
            $stmt = $this->conn->prepare("DELETE FROM calendar_events WHERE id = ?");
            $stmt->bind_param("i", $id);
        } else {
            $stmt = $this->conn->prepare("DELETE FROM calendar_events WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $id, $this->user_id);
        }

        return $stmt->execute();
    }
    
    /**
     * Check if the current user is the owner of an event
     * @param int $eventId
     * @return bool
     */
    public function isEventOwner($eventId) {
        $stmt = $this->conn->prepare("SELECT id FROM calendar_events 
                                      WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $eventId, $this->user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0;
    }
    
    /**
     * Update event date/time (for drag & drop functionality)
     * @param int $id
     * @param string $start
     * @param string $end
     * @return bool
     */
    public function updateEventDates($id, $start, $end = null) {
        if (!$this->isEventOwner($id) && !$this->hasPermission('calendar_edit_others')) {
            error_log("updateEventDates: permission denied for user {$this->user_id} on event {$id}");
            return false;
        }

        $formattedStart = $this->formatDatetime($start);
        $formattedEnd = $this->formatDatetime($end);

        if ($this->hasPermission('calendar_edit_others')) {
            $stmt = $this->conn->prepare("UPDATE calendar_events 
                                          SET start = ?, end = ? 
                                          WHERE id = ?");
            if (!$stmt) {
                error_log("updateEventDates prepare failed (admin): " . $this->conn->error);
                return false;
            }
            $stmt->bind_param("ssi", $formattedStart, $formattedEnd, $id);
        } else {
            $stmt = $this->conn->prepare("UPDATE calendar_events 
                                          SET start = ?, end = ? 
                                          WHERE id = ? AND user_id = ?");
            if (!$stmt) {
                error_log("updateEventDates prepare failed: " . $this->conn->error);
                return false;
            }
            $stmt->bind_param("ssii", $formattedStart, $formattedEnd, $id, $this->user_id);
        }

        if (!$stmt->execute()) {
            error_log("updateEventDates execute failed: " . $stmt->error);
            return false;
        }

        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected === 0) {
            error_log("updateEventDates: no rows affected for event {$id} (maybe same dates).");
        }

        return $affected >= 0;
    }


    public function getEventById($id) {
        $stmt = $this->conn->prepare("SELECT e.*, u.name as creator_name FROM calendar_events e LEFT JOIN user u ON e.user_id = u.id WHERE e.id = ? LIMIT 1");
        if (!$stmt) {
            error_log("getEventById prepare failed: " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            error_log("getEventById execute failed: " . $stmt->error);
            return null;
        }
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        return $row ? $row : null;
    }

}
?>