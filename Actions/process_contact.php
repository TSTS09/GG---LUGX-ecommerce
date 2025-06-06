<?php
// Include core file for session management and authentication functions
require_once("../Setting/core.php");

// Create directory for logs if it doesn't exist
if (!file_exists('../Error')) {
    mkdir('../Error', 0755, true);
}

// Log errors to a file
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../Error/contact_form_errors.log');

// Include PHPMailer classes
require '../PHPMailer/PHPMailer-master/src/Exception.php';
require '../PHPMailer/PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to clean input data
function clean_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get form data and clean it
    $name = isset($_POST["name"]) ? clean_input($_POST["name"]) : "";
    $surname = isset($_POST["surname"]) ? clean_input($_POST["surname"]) : "";
    $email = isset($_POST["email"]) ? clean_input($_POST["email"]) : "";
    $subject = isset($_POST["subject"]) ? clean_input($_POST["subject"]) : "";
    $message = isset($_POST["message"]) ? clean_input($_POST["message"]) : "";

    // Validate form data
    $errors = [];

    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($surname)) {
        $errors[] = "Surname is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($subject)) {
        $errors[] = "Subject is required";
    }

    if (empty($message)) {
        $errors[] = "Message is required";
    }

    // If no errors, save to backup file and try to send email
    if (empty($errors)) {
        // Always save to backup file first (reliable method)
        $date_time = date("Y-m-d H:i:s");
        $backup_file = '../Error/contact_submissions.txt';
        $backup_content = "==========\nDate: $date_time\nFrom: $name $surname <$email>\nSubject: $subject\nMessage: $message\n==========\n\n";

        // Ensure directory exists before writing
        $backup_dir = dirname($backup_file);
        if (!file_exists($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }

        // Write to backup file
        if (file_put_contents($backup_file, $backup_content, FILE_APPEND)) {
            // Record the submission in the log for verification
            error_log("Contact form submitted by $email on $date_time: SAVED TO BACKUP FILE");

            // Try to send email with PHPMailer
            try {
                // Create a new PHPMailer instance
                $mail = new PHPMailer(true); // Passing `true` enables exceptions

                // Server settings - MODIFY THESE WITH YOUR ACTUAL SMTP SETTINGS
                $mail->isSMTP();
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ]
                ];
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'sekaletchio@gmail.com';
                $mail->Password   = 'npiobjolfmfnjxgy ';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Recipients
                $mail->setFrom('noreply@gg-lugx.com', 'GG-LUGX Contact Form');
                $mail->addAddress('sekaletchio@gmail.com', 'GG-LUGX Admin'); 
                $mail->addReplyTo($email, "$name $surname");

                // Content
                $mail->isHTML(true);
                $mail->Subject = "Contact Form: $subject - $date_time";

                // Build email message in HTML
                $mail->Body = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; line-height: 1.6; }
                            .container { width: 100%; max-width: 600px; margin: 0 auto; }
                            .header { background-color: #f8f8f8; padding: 20px; border-bottom: 3px solid #ee626b; }
                            .content { padding: 20px; }
                            .footer { background-color: #f8f8f8; padding: 20px; font-size: 12px; text-align: center; }
                            h1 { color: #ee626b; }
                            .label { font-weight: bold; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1>New Contact Form Message</h1>
                            </div>
                            <div class='content'>
                                <p><span class='label'>Name:</span> $name $surname</p>
                                <p><span class='label'>Email:</span> $email</p>
                                <p><span class='label'>Subject:</span> $subject</p>
                                <p><span class='label'>Message:</span></p>
                                <p>" . nl2br($message) . "</p>
                            </div>
                            <div class='footer'>
                                This message was sent from the GG-LUGX contact form on $date_time
                            </div>
                        </div>
                    </body>
                    </html>
                ";

                // Plain text alternative
                $mail->AltBody = "Name: $name $surname\nEmail: $email\nSubject: $subject\nMessage: $message";

                // Log attempt
                error_log("Attempting to send email for contact form from $email");

                // Try to send the email
                $mail->send();

                // Log successful email sending
                error_log("Contact form email sent successfully for $email");

                // Set success message
                $_SESSION['contact_success'] = "Thank you for your message! We've received it and will get back to you soon.";
            } catch (Exception $e) {
                // Log email sending failure
                error_log("Failed to send email for contact form from $email: " . $mail->ErrorInfo);

                // Still show success since the backup file was created
                $_SESSION['contact_success'] = "Thank you for your message! We've received it and will get back to you soon.";
            }

            // Redirect to contact page with success status
            header("Location: ../View/contact.php?status=success");
            exit();
        } else {
            // Failed to write to backup file
            error_log("Failed to write contact form submission to backup file for $email");

            // Set error message
            $_SESSION['contact_error'] = "There was an issue processing your request. Please try again later.";

            // Redirect back to contact page with error
            header("Location: ../View/contact.php?status=error");
            exit();
        }
    } else {
        // Store validation errors in session
        $_SESSION['contact_errors'] = $errors;

        // Log validation errors
        error_log("Contact form validation errors for $email: " . implode(", ", $errors));

        // Redirect back to contact page with validation error status
        header("Location: ../View/contact.php?status=validation_error");
        exit();
    }
} else {
    // If not POST request, redirect to contact page
    header("Location: ../View/contact.php");
    exit();
}
