<?php
// This file handles API requests for calendar events
header('Content-Type: application/json');
date_default_timezone_set('Asia/Kuala_Lumpur');


// Use proper path resolution for including files
// This is necessary because this file is in a subdirectory (assets/php/API)
if (!defined('ROOT_PATH')) {
    // Adapt this path as needed for your server structure
    define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
}

// Log the include path for debugging
error_log("Including file from path: " . ROOT_PATH . "assets/php/calendar.php");

require_once(ROOT_PATH . "assets/php/calendar.php");

// Create calendar instance
$calendar = new Calendar();

// Log the current user's info
error_log("API called by user ID: " . $calendar->getUserId() . ", Name: " . $calendar->getUserName());

// Make sure user is logged in
if (!$calendar->isLoggedIn()) {
    error_log("API Error: User not logged in");
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Get request method and log it
$method = $_SERVER['REQUEST_METHOD'];
error_log("API Request Method: " . $method);

// For POST/PUT requests, log the incoming data
if ($method == 'POST' || $method == 'PUT') {
    $raw_data = file_get_contents('php://input');
    error_log("Received " . $method . " data: " . $raw_data);
}

switch ($method) {
    case 'GET':
        // Get all events
        try {
            $events = $calendar->getAllEvents();
            error_log("API: Retrieved " . count($events) . " events");
            echo json_encode($events);
        } catch (Exception $e) {
            error_log("API Error getting events: " . $e->getMessage());
            echo json_encode(['error' => 'Failed to retrieve events: ' . $e->getMessage()]);
        }
        break;
        
    case 'POST':
        // Add a new event
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("API Error: Invalid JSON - " . json_last_error_msg());
                echo json_encode(['error' => 'Invalid JSON data: ' . json_last_error_msg()]);
                exit;
            }
            
            if (!isset($data['title']) || !isset($data['start']) || !isset($data['className'])) {
                error_log("API Error: Missing required fields");
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            
            $title = $data['title'];
            $start = $data['start'];
            $className = $data['className'];
            $end = isset($data['end']) ? $data['end'] : null;
            
            error_log("API: Creating event - Title: $title, Start: $start, Class: $className");
            
            // Updated parameter order to match the fixed method signature
            $id = $calendar->addEvent($title, $start, $className, $end);
            
            if ($id) {
                error_log("API: Event created successfully with ID: $id");
                echo json_encode([
                    'success' => true,
                    'id' => $id,
                    'message' => 'Event added successfully'
                ]);
            } else {
                error_log("API Error: Failed to add event");
                echo json_encode(['error' => 'Failed to add event']);
            }
        } catch (Exception $e) {
            error_log("API Exception adding event: " . $e->getMessage());
            echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
        }
        break;
        
        case 'PUT':
            // Update an event
            try {
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log("API Error: Invalid JSON - " . json_last_error_msg());
                    echo json_encode(['error' => 'Invalid JSON data: ' . json_last_error_msg()]);
                    exit;
                }
                
                if (!isset($data['id'])) {
                    error_log("API Error: Missing event ID");
                    echo json_encode(['error' => 'Missing event ID']);
                    exit;
                }
                
                $id = $data['id'];
                error_log("API: Updating event ID: $id");
                
                // Check what type of update this is
                if (isset($data['title']) && isset($data['className']) && isset($data['start'])) {
                    // This is a full update from the form (both details and dates)
                    $title = $data['title'];
                    $className = $data['className'];
                    $start = $data['start'];
                    $end = isset($data['end']) ? $data['end'] : null;
                    
                    error_log("API: Full event update - Title: $title, Class: $className, Start: $start");
                    
                    // First update the details
                    $success1 = $calendar->updateEvent($id, $title, $className);
                    
                    // Then update the dates
                    $success2 = $calendar->updateEventDates($id, $start, $end);
                    
                    $success = $success1 && $success2;
                    
                    if ($success) {
                        error_log("API: Event fully updated successfully");
                        echo json_encode([
                            'success' => true,
                            'message' => 'Event updated successfully'
                        ]);
                    } else {
                        error_log("API Error: Failed to update event or permission denied");
                        echo json_encode([
                            'error' => 'Failed to update event or you do not have permission'
                        ]);
                    }
                }
                // If updating dates only (drag & drop)
                else if (isset($data['start'])) {
                    $start = $data['start'];
                    $end = isset($data['end']) ? $data['end'] : null;
                    
                    error_log("API: Updating event dates only - Start: $start, End: " . ($end ?? 'null'));
                    
                    $success = $calendar->updateEventDates($id, $start, $end);
                    
                    if ($success) {
                        error_log("API: Event dates updated successfully");
                        echo json_encode([
                            'success' => true,
                            'message' => 'Event dates updated successfully'
                        ]);
                    } else {
                        error_log("API Error: Failed to update event dates or permission denied");
                        echo json_encode([
                            'error' => 'Failed to update event dates or you do not have permission'
                        ]);
                    }
                }
                // If updating event details only
                else if (isset($data['title']) && isset($data['className'])) {
                    $title = $data['title'];
                    $className = $data['className'];
                    
                    error_log("API: Updating event details only - Title: $title, Class: $className");
                    
                    $success = $calendar->updateEvent($id, $title, $className);
                    
                    if ($success) {
                        error_log("API: Event updated successfully");
                        echo json_encode([
                            'success' => true,
                            'message' => 'Event updated successfully'
                        ]);
                    } else {
                        error_log("API Error: Failed to update event or permission denied");
                        echo json_encode([
                            'error' => 'Failed to update event or you do not have permission'
                        ]);
                    }
                } else {
                    error_log("API Error: Missing required fields for update");
                    echo json_encode(['error' => 'Missing required fields for update']);
                }
            } catch (Exception $e) {
                error_log("API Exception updating event: " . $e->getMessage());
                echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
            }
            break;

    case 'DELETE':
        // Delete an event
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("API Error: Invalid JSON - " . json_last_error_msg());
                echo json_encode(['error' => 'Invalid JSON data: ' . json_last_error_msg()]);
                exit;
            }
            
            if (!isset($data['id'])) {
                error_log("API Error: Missing event ID for delete");
                echo json_encode(['error' => 'Missing event ID']);
                exit;
            }
            
            $id = $data['id'];
            error_log("API: Deleting event ID: $id");
            
            $success = $calendar->deleteEvent($id);
            
            if ($success) {
                error_log("API: Event deleted successfully");
                echo json_encode([
                    'success' => true,
                    'message' => 'Event deleted successfully'
                ]);
            } else {
                error_log("API Error: Failed to delete event or permission denied");
                echo json_encode([
                    'error' => 'Failed to delete event or you do not have permission'
                ]);
            }
        } catch (Exception $e) {
            error_log("API Exception deleting event: " . $e->getMessage());
            echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
        }
        break;
        
    default:
        error_log("API Error: Invalid request method: $method");
        echo json_encode(['error' => 'Invalid request method']);
        break;
}
?>