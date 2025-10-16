<?php

/**
 * Ticket Management API
 * Handles all ticket-related API operations including:
 * - Creating tickets
 * - Retrieving tickets
 * - Updating tickets
 * - Deleting tickets
 */

// Include database connection
require_once(dirname(__FILE__) . '/../main.php');

// Initialize database connection
$main = new ppim();
$db = $main->getDB();

// Set headers for JSON response
header('Content-Type: application/json');

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get action from request
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Process request based on method and action
switch ($method) {
    case 'GET':
        if ($action == 'get_all') {
            // Get all tickets
            getAllTickets($db);
        } elseif ($action == 'get_by_id' && isset($_GET['id'])) {
            // Get ticket by ID
            getTicketById($db, $_GET['id']);
        } elseif ($action == 'get_stats') {
            // Get ticket statistics
            getTicketStats($db);
        } else {
            // Invalid action
            sendResponse(400, 'Invalid action');
        }
        break;

    case 'POST':
        // Get POST data
        $data = json_decode(file_get_contents('php://input'), true);

        if ($action == 'create') {
            // Create new ticket
            createTicket($db, $data);
        } else {
            // Invalid action
            sendResponse(400, 'Invalid action');
        }
        break;

    case 'PUT':
        // Get PUT data
        $data = json_decode(file_get_contents('php://input'), true);

        if ($action == 'update' && isset($_GET['id'])) {
            // Update ticket
            updateTicket($db, $_GET['id'], $data);
        } else {
            // Invalid action
            sendResponse(400, 'Invalid action');
        }
        break;

    case 'DELETE':
        if ($action == 'delete' && isset($_GET['id'])) {
            // Delete ticket
            deleteTicket($db, $_GET['id']);
        } else {
            // Invalid action
            sendResponse(400, 'Invalid action');
        }
        break;

    default:
        // Method not allowed
        sendResponse(405, 'Method not allowed');
        break;
}

/**
 * Create a new ticket event with ticket types
 * @param PDO $db Database connection
 * @param array $data Request data
 */
function createTicket($db, $data)
{
    try {
        // Validate required fields
        if (
            !isset($data['eventName']) || !isset($data['eventDate']) || !isset($data['eventTime']) ||
            !isset($data['eventLocation']) || !isset($data['ticketTypes']) || empty($data['ticketTypes'])
        ) {
            sendResponse(400, 'Missing required fields');
            return;
        }

        // Start transaction
        $db->beginTransaction();

        // Insert event data
        $stmt = $db->prepare("INSERT INTO events (event_name, event_date, event_time, event_location, event_description, created_at) 
                              VALUES (:event_name, :event_date, :event_time, :event_location, :event_description, NOW())");

        $stmt->bindParam(':event_name', $data['eventName']);
        $stmt->bindParam(':event_date', $data['eventDate']);
        $stmt->bindParam(':event_time', $data['eventTime']);
        $stmt->bindParam(':event_location', $data['eventLocation']);
        $stmt->bindParam(':event_description', $data['eventDescription'] ?? '');
        $stmt->execute();

        $eventId = $db->lastInsertId();

        // Insert ticket types
        foreach ($data['ticketTypes'] as $ticket) {
            $stmt = $db->prepare("INSERT INTO tickets (event_id, ticket_type, price, quantity, sold, end_date, created_at) 
                                  VALUES (:event_id, :ticket_type, :price, :quantity, 0, :end_date, NOW())");

            $stmt->bindParam(':event_id', $eventId);
            $stmt->bindParam(':ticket_type', $ticket['type']);
            $stmt->bindParam(':price', $ticket['price']);
            $stmt->bindParam(':quantity', $ticket['quantity']);
            $stmt->bindParam(':end_date', $ticket['endDate']);
            $stmt->execute();
        }

        // Commit transaction
        $db->commit();

        sendResponse(201, 'Ticket created successfully', ['event_id' => $eventId]);
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        sendResponse(500, 'Failed to create ticket: ' . $e->getMessage());
    }
}

/**
 * Get all tickets with event details
 * @param PDO $db Database connection
 */
function getAllTickets($db)
{
    try {
        $stmt = $db->prepare("SELECT t.id, t.event_id, e.event_name, t.ticket_type, t.price, t.quantity, t.sold, 
                              e.event_date, t.end_date, 
                              CASE 
                                  WHEN t.end_date < CURDATE() THEN 'Expired'
                                  WHEN t.sold >= t.quantity THEN 'Sold Out'
                                  ELSE 'Available'
                              END as status
                            FROM tickets t
                            JOIN events e ON t.event_id = e.id
                            ORDER BY e.event_date DESC");
        $stmt->execute();

        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        sendResponse(200, 'Tickets retrieved successfully', ['tickets' => $tickets]);
    } catch (Exception $e) {
        sendResponse(500, 'Failed to retrieve tickets: ' . $e->getMessage());
    }
}

/**
 * Get ticket details by ID
 * @param PDO $db Database connection
 * @param int $ticketId Ticket ID
 */
function getTicketById($db, $ticketId)
{
    try {
        $stmt = $db->prepare("SELECT t.*, e.event_name, e.event_date, e.event_time, e.event_location, e.event_description
                              FROM tickets t
                              JOIN events e ON t.event_id = e.id
                              WHERE t.id = :ticket_id");
        $stmt->bindParam(':ticket_id', $ticketId);
        $stmt->execute();

        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ticket) {
            sendResponse(200, 'Ticket retrieved successfully', ['ticket' => $ticket]);
        } else {
            sendResponse(404, 'Ticket not found');
        }
    } catch (Exception $e) {
        sendResponse(500, 'Failed to retrieve ticket: ' . $e->getMessage());
    }
}

/**
 * Update ticket details
 * @param PDO $db Database connection
 * @param int $ticketId Ticket ID
 * @param array $data Updated ticket data
 */
function updateTicket($db, $ticketId, $data)
{
    try {
        // Validate required fields
        if (!isset($data['ticketType']) || !isset($data['price']) || !isset($data['quantity']) || !isset($data['endDate'])) {
            sendResponse(400, 'Missing required fields');
            return;
        }

        $stmt = $db->prepare("UPDATE tickets 
                              SET ticket_type = :ticket_type, price = :price, quantity = :quantity, end_date = :end_date, updated_at = NOW()
                              WHERE id = :ticket_id");

        $stmt->bindParam(':ticket_id', $ticketId);
        $stmt->bindParam(':ticket_type', $data['ticketType']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':quantity', $data['quantity']);
        $stmt->bindParam(':end_date', $data['endDate']);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            sendResponse(200, 'Ticket updated successfully');
        } else {
            sendResponse(404, 'Ticket not found');
        }
    } catch (Exception $e) {
        sendResponse(500, 'Failed to update ticket: ' . $e->getMessage());
    }
}

/**
 * Delete a ticket
 * @param PDO $db Database connection
 * @param int $ticketId Ticket ID
 */
function deleteTicket($db, $ticketId)
{
    try {
        $stmt = $db->prepare("DELETE FROM tickets WHERE id = :ticket_id");
        $stmt->bindParam(':ticket_id', $ticketId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            sendResponse(200, 'Ticket deleted successfully');
        } else {
            sendResponse(404, 'Ticket not found');
        }
    } catch (Exception $e) {
        sendResponse(500, 'Failed to delete ticket: ' . $e->getMessage());
    }
}

/**
 * Get ticket statistics
 * @param PDO $db Database connection
 */
function getTicketStats($db)
{
    try {
        // Total tickets
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM tickets");
        $stmt->execute();
        $totalTickets = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Active events
        $stmt = $db->prepare("SELECT COUNT(DISTINCT event_id) as total FROM tickets 
                              WHERE end_date >= CURDATE()");
        $stmt->execute();
        $activeEvents = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Tickets sold
        $stmt = $db->prepare("SELECT SUM(sold) as total FROM tickets");
        $stmt->execute();
        $ticketsSold = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;

        // Revenue
        $stmt = $db->prepare("SELECT SUM(price * sold) as total FROM tickets");
        $stmt->execute();
        $revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;

        $stats = [
            'total_tickets' => $totalTickets,
            'active_events' => $activeEvents,
            'tickets_sold' => $ticketsSold,
            'revenue' => $revenue
        ];

        sendResponse(200, 'Statistics retrieved successfully', ['stats' => $stats]);
    } catch (Exception $e) {
        sendResponse(500, 'Failed to retrieve statistics: ' . $e->getMessage());
    }
}

/**
 * Send JSON response
 * @param int $status HTTP status code
 * @param string $message Response message
 * @param array $data Additional data
 */
function sendResponse($status, $message, $data = [])
{
    http_response_code($status);

    $response = [
        'status' => $status < 400 ? 'success' : 'error',
        'message' => $message
    ];

    if (!empty($data)) {
        $response = array_merge($response, $data);
    }

    echo json_encode($response);
    exit;
}