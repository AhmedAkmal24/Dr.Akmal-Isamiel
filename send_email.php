<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Extract booking data
$to = $data['to'] ?? '';
$clientName = $data['name'] ?? '';
$clientEmail = $data['email'] ?? '';
$clientPhone = $data['phone'] ?? '';
$meetingType = $data['meetingType'] ?? '';
$preferredDate = $data['date'] ?? '';
$preferredTime = $data['time'] ?? '';
$legalMatter = $data['description'] ?? '';

// Validate required fields
if (empty($to) || empty($clientName) || empty($clientEmail)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Email configuration
$subject = "New Legal Consultation Booking - " . $clientName;
$fromEmail = "akmalahmed060@gmail.com"; // Email that sends the notifications
$fromName = "Dr. Akmal Law Website";

// Create email content
$emailContent = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>New Consultation Booking</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .booking-details { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        .urgent { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🔔 New Legal Consultation Booking</h1>
        </div>
        
        <div class='content'>
            <p class='urgent'>A new consultation has been booked through your website!</p>
            
            <div class='booking-details'>
                <h3>Client Information:</h3>
                <p><strong>Name:</strong> {$clientName}</p>
                <p><strong>Email:</strong> {$clientEmail}</p>
                <p><strong>Phone:</strong> {$clientPhone}</p>
            </div>
            
            <div class='booking-details'>
                <h3>Meeting Details:</h3>
                <p><strong>Type:</strong> " . ucfirst($meetingType) . "</p>
                <p><strong>Preferred Date:</strong> {$preferredDate}</p>
                <p><strong>Preferred Time:</strong> {$preferredTime}</p>
            </div>
            
            <div class='booking-details'>
                <h3>Legal Matter:</h3>
                <p>{$legalMatter}</p>
            </div>
            
            <div class='booking-details'>
                <h3>Next Steps:</h3>
                <ul>
                    <li>Review the client's legal matter description</li>
                    <li>Contact the client to confirm the appointment</li>
                    <li>Send calendar invitation if meeting is confirmed</li>
                    <li>Prepare relevant documents or questions</li>
                </ul>
            </div>
        </div>
        
        <div class='footer'>
            <p>This email was automatically generated from your website booking system.</p>
            <p>Booking received at: " . date('Y-m-d H:i:s') . "</p>
        </div>
    </div>
</body>
</html>
";

// Email headers
$headers = array(
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=UTF-8',
    'From: ' . $fromName . ' <' . $fromEmail . '>',
    'Reply-To: ' . $clientEmail,
    'X-Mailer: PHP/' . phpversion()
);

// Send email
$success = mail($to, $subject, $emailContent, implode("\r\n", $headers));

if ($success) {
    // Log successful email (optional)
    error_log("Booking email sent successfully to: " . $to . " for client: " . $clientName);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Email sent successfully',
        'client' => $clientName,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    // Log failed email (optional)
    error_log("Failed to send booking email to: " . $to . " for client: " . $clientName);
    
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to send email',
        'error' => error_get_last()
    ]);
}
?>
