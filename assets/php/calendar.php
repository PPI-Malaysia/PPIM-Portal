<?php
// calendar.php - Calendar functionality

// Define ROOT_PATH if not already defined (relative to this file: assets/php/calendar.php)
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/../..') . '/');
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
            
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->handleRequest();
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
                'start' => $event['start'],
                'end' => $event['end'],
                'className' => $event['className'],
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