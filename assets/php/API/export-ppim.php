<?php
/**
 * Export PPIM Members to CSV
 * Supports filtered exports based on current view
 */

// Suppress warnings/notices for clean CSV output
error_reporting(0);
ini_set('display_errors', 0);

require_once(__DIR__ . "/../student-database.php");

// Initialize database connection
$studentDB = new StudentDatabase();

// Check if export parameter is set
if (!isset($_GET['export']) || $_GET['export'] !== 'csv') {
    header('HTTP/1.1 400 Bad Request');
    die('Invalid export request');
}

// Get filter parameters from URL (same as the main page)
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : null;
$dir = isset($_GET['dir']) ? strtolower($_GET['dir']) : 'asc';
$dir = $dir === 'desc' ? 'desc' : 'asc';

try {
    // Get all PPIM members with filters (no pagination)
    $data = $studentDB->getAllPPIMForExport($search, $sort, $dir);

    // Set headers for CSV download
    $filename = 'ppim_members_export_' . date('Y-m-d_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Add BOM for proper UTF-8 encoding in Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Write CSV header
    $headers = [
        'PPIM ID',
        'Student Name',
        'Start Year',
        'End Year',
        'Department',
        'Position',
        'Description',
        'Status'
    ];
    fputcsv($output, $headers, ',', '"', '\\');

    // Write data rows
    foreach ($data as $row) {
        // Determine status
        $status = 'Unknown';
        if ($row['is_active'] == 0) {
            $status = 'Unverified';
        } elseif ($row['is_active'] == 1) {
            $status = 'Active';
        } elseif ($row['is_active'] == 2) {
            $status = 'Ended';
        }

        $csvRow = [
            $row['ppim_id'] ?? '',
            $row['fullname'] ?? '',
            $row['start_year'] ?? '',
            $row['end_year'] ?? '',
            $row['department'] ?? '',
            $row['position'] ?? '',
            $row['description'] ?? '',
            $status
        ];
        fputcsv($output, $csvRow, ',', '"', '\\');
    }

    fclose($output);
    exit();

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    die('Export failed: ' . htmlspecialchars($e->getMessage()));
}
