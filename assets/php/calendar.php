<?php
// calendar.php - Calendar functionality

// Define ROOT_PATH if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
}

require_once(ROOT_PATH . "assets/php/main.php");

class Calendar extends ppim {
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
                'start' => $row['start'],
                'end' => $row['end'] ? $row['end'] : $row['start'],
                'className' => $row['class_name'],
                'extendedProps' => [
                    'user_id' => $row['user_id'],
                    'creator_name' => $row['creator_name']
                ]
            ];
        }
        
        return $events;
    }
    
    /**
     * Add a new calendar event
     * @param string $title
     * @param string $start
     * @param string $className
     * @param string $end
     * @return int|bool
     */
    public function addEvent($title, $start, $className, $end = null) {
        // Format the datetime values for MySQL
        $formattedStart = $this->formatDatetime($start);
        $formattedEnd = $this->formatDatetime($end);
        
        // Log the original and formatted values for debugging
        error_log("Original start: $start, Formatted: $formattedStart");
        error_log("Original end: $end, Formatted: $formattedEnd");
        
        $stmt = $this->conn->prepare("INSERT INTO calendar_events (user_id, title, start, end, class_name) 
                                      VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $this->user_id, $title, $formattedStart, $formattedEnd, $className);
        
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
    public function updateEvent($id, $title, $className) {
        // First check if user owns this event
        if (!$this->isEventOwner($id)) {
            return false;
        }
        
        $stmt = $this->conn->prepare("UPDATE calendar_events 
                                      SET title = ?, class_name = ? 
                                      WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $title, $className, $id, $this->user_id);
        
        return $stmt->execute();
    }
    
    /**
     * Delete a calendar event
     * @param int $id
     * @return bool
     */
    public function deleteEvent($id) {
        // First check if user owns this event
        if (!$this->isEventOwner($id)) {
            return false;
        }
        
        $stmt = $this->conn->prepare("DELETE FROM calendar_events 
                                      WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $this->user_id);
        
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
        // First check if user owns this event
        if (!$this->isEventOwner($id)) {
            return false;
        }
        
        // Format the datetime values for MySQL
        $formattedStart = $this->formatDatetime($start);
        $formattedEnd = $this->formatDatetime($end);
        
        // Log the original and formatted values for debugging
        error_log("Update dates - Original start: $start, Formatted: $formattedStart");
        error_log("Update dates - Original end: $end, Formatted: $formattedEnd");
        
        $stmt = $this->conn->prepare("UPDATE calendar_events 
                                      SET start = ?, end = ? 
                                      WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssii", $formattedStart, $formattedEnd, $id, $this->user_id);
        
        return $stmt->execute();
    }
}
?>