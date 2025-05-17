<?php
// Set appropriate headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// In a real application, you would fetch this data from a database
// This is just a sample structure that matches what the FullCalendar component expects

$events = [
    [
        'title' => 'Interview - Backend Engineer',
        'start' => date('Y-m-d'),
        'end' => date('Y-m-d'),
        'className' => 'bg-primary'
    ],
    [
        'title' => 'Meeting with CT Team',
        'start' => date('Y-m-d', strtotime('+1 day')),
        'end' => date('Y-m-d', strtotime('+1 day')),
        'className' => 'bg-warning'
    ],
    [
        'title' => 'Meeting with Mr. Admin',
        'start' => date('Y-m-d', strtotime('+3 days')),
        'end' => date('Y-m-d', strtotime('+3 days')),
        'className' => 'bg-info'
    ],
    [
        'title' => 'Interview - Frontensd Engineer',
        'start' => date('Y-m-d', strtotime('+7 days')),
        'end' => date('Y-m-d', strtotime('+7 days')),
        'className' => 'bg-secondary'
    ],
    [
        'title' => 'Phone Screen - Frontend Engineer',
        'start' => date('Y-m-d', strtotime('+2 days')),
        'className' => 'bg-success'
    ],
    [
        'title' => 'Buy Design Assets',
        'start' => date('Y-m-d', strtotime('+4 days')),
        'end' => date('Y-m-d', strtotime('+4 days')),
        'className' => 'bg-primary'
    ],
    [
        'title' => 'Setup Github Repository',
        'start' => date('Y-m-d', strtotime('+12 days')),
        'end' => date('Y-m-d', strtotime('+12 days')),
        'className' => 'bg-danger'
    ],
    [
        'title' => 'Meeting with Mr. Shreyu',
        'start' => date('Y-m-d', strtotime('+29 days')),
        'end' => date('Y-m-d', strtotime('+29 days')),
        'className' => 'bg-dark'
    ]
];

// Return the events as JSON
echo json_encode($events);