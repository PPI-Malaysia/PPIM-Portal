<?php
/**
 * Export Students to CSV
 * Supports filtered and sorted exports based on current view
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
    // Get all students with filters (no pagination limit)
    $data = $studentDB->getAllStudentsForExport($search, $sort, $dir);

    // Set headers for CSV download
    $filename = 'students_export_' . date('Y-m-d_His') . '.csv';
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
        'Student ID',
        'Full Name',
        'Date of Birth',
        'Email',
        'Passport',
        'Phone Number',
        'Zip Code',
        'City',
        'Address',
        'Qualification Level',
        'Degree Programme',
        'Expected Graduate',
        'University',
        'Status',
        'Active'
    ];
    fputcsv($output, $headers, ',', '"', '\\');

    // Write data rows
    foreach ($data as $row) {
        $csvRow = [
            $row['student_id'] ?? '',
            $row['fullname'] ?? '',
            $row['dob'] ?? '',
            $row['email'] ?? '',
            $row['passport'] ?? '',
            $row['phone_number'] ?? '',
            $row['postcode_id'] ?? '',
            $row['city'] ?? '',
            $row['address'] ?? '',
            $row['qualification_level'] ?? '',
            $row['degree'] ?? '',
            $row['expected_graduate'] ?? '',
            $row['university_name'] ?? '',
            $row['status_name'] ?? '',
            $row['is_active'] ? 'Yes' : 'No'
        ];
        fputcsv($output, $csvRow, ',', '"', '\\');
    }

    fclose($output);
    exit();

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    die('Export failed: ' . htmlspecialchars($e->getMessage()));
}
