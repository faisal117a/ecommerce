<?php
// Email System using PHPMailer

require_once __DIR__ . '/settings.php';
require_once __DIR__ . '/functions.php';

// getBaseUrl is already defined in functions.php

// Check if PHPMailer is available, if not, use basic mail() function
$usePHPMailer = class_exists('PHPMailer\PHPMailer\PHPMailer');

if ($usePHPMailer) {
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
}

/**
 * Send email using SMTP settings or mail() function
 */
function sendEmail(string $to, string $subject, string $body, string $toName = ''): bool {
    $smtpHost = getSetting('smtp_host');
    $smtpPort = getSetting('smtp_port', '587');
    $smtpUsername = getSetting('smtp_username');
    $smtpPassword = getSetting('smtp_password');
    $smtpEncryption = getSetting('smtp_encryption', 'tls');
    $fromEmail = getSetting('smtp_from_email', getSetting('site_email', 'noreply@cur1.com'));
    $fromName = getSetting('smtp_from_name', getSetting('site_name', 'Cur1 Fashion'));
    
    // If SMTP is not configured, use basic mail() function
    if (empty($smtpHost) || empty($smtpUsername)) {
        $headers = "From: $fromName <$fromEmail>\r\n";
        $headers .= "Reply-To: $fromEmail\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        return mail($to, $subject, $body, $headers);
    }
    
    // Use PHPMailer if available
    if ($usePHPMailer) {
        try {
            $mail = new PHPMailer(true);
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUsername;
            $mail->Password = $smtpPassword;
            $mail->SMTPSecure = $smtpEncryption === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = (int)$smtpPort;
            $mail->CharSet = 'UTF-8';
            
            // Sender and recipient
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($to, $toName);
            $mail->addReplyTo($fromEmail, $fromName);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email sending failed: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    // Fallback to mail() function
    $headers = "From: $fromName <$fromEmail>\r\n";
    $headers .= "Reply-To: $fromEmail\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    return mail($to, $subject, $body, $headers);
}

/**
 * Send order confirmation email
 */
function sendOrderConfirmationEmail(int $orderId, array $order, array $orderItems, string $customerEmail, string $customerName): bool {
    $subject = "Order Confirmation #{$orderId} - Cur1 Fashion";
    
    $itemsHtml = '';
    foreach ($orderItems as $item) {
        $itemsHtml .= "<tr>
            <td>{$item['product_name']}</td>
            <td>{$item['quantity']}</td>
            <td>" . formatPrice($item['price']) . "</td>
            <td>" . formatPrice($item['price'] * $item['quantity']) . "</td>
        </tr>";
    }
    
    $discountHtml = '';
    if (!empty($order['discount_amount']) && $order['discount_amount'] > 0) {
        $discountHtml = "<tr>
            <td colspan='3'><strong>Discount ({$order['coupon_code']})</strong></td>
            <td>-" . formatPrice($order['discount_amount']) . "</td>
        </tr>";
    }
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #0d6efd; color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 20px; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #e9ecef; }
            .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Order Confirmation</h1>
            </div>
            <div class='content'>
                <p>Dear {$customerName},</p>
                <p>Thank you for your order! We've received your order and will process it shortly.</p>
                
                <h3>Order Details</h3>
                <p><strong>Order ID:</strong> #{$orderId}</p>
                <p><strong>Order Date:</strong> " . date('F d, Y', strtotime($order['created_at'])) . "</p>
                <p><strong>Status:</strong> " . ucfirst($order['status']) . "</p>
                
                <h3>Order Items</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$itemsHtml}
                        {$discountHtml}
                        <tr>
                            <td colspan='3'><strong>Total</strong></td>
                            <td><strong>" . formatPrice($order['total_amount']) . "</strong></td>
                        </tr>
                    </tbody>
                </table>
                
                <h3>Shipping Address</h3>
                <p>" . nl2br(htmlspecialchars($order['shipping_address'])) . "</p>
                
                <p>You can track your order status at: <a href='" . getBaseUrl() . "/orders/track.php?id={$orderId}'>Track Order</a></p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Cur1 Fashion. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($customerEmail, $subject, $body, $customerName);
}

/**
 * Send order status update email
 */
function sendOrderStatusUpdateEmail(int $orderId, array $order, string $customerEmail, string $customerName, string $newStatus): bool {
    $subject = "Order #{$orderId} Status Update - Cur1 Fashion";
    
    $statusMessages = [
        'pending' => 'Your order is pending and will be processed soon.',
        'processing' => 'Your order is being processed.',
        'shipped' => 'Your order has been shipped!',
        'delivered' => 'Your order has been delivered!',
        'cancelled' => 'Your order has been cancelled.',
    ];
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #0d6efd; color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 20px; }
            .status-badge { display: inline-block; padding: 8px 16px; background: #0d6efd; color: white; border-radius: 4px; font-weight: bold; }
            .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Order Status Update</h1>
            </div>
            <div class='content'>
                <p>Dear {$customerName},</p>
                <p>Your order #{$orderId} status has been updated.</p>
                
                <p><strong>New Status:</strong> <span class='status-badge'>" . ucfirst($newStatus) . "</span></p>
                <p>{$statusMessages[$newStatus] ?? 'Your order status has been updated.'}</p>
                
                <p>You can track your order at: <a href='" . getBaseUrl() . "/orders/track.php?id={$orderId}'>Track Order</a></p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Cur1 Fashion. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($customerEmail, $subject, $body, $customerName);
}

/**
 * Send welcome email after registration
 */
function sendWelcomeEmail(string $email, string $name): bool {
    $subject = "Welcome to Cur1 Fashion!";
    
    $body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #0d6efd; color: white; padding: 20px; text-align: center; }
            .content { background: #f8f9fa; padding: 20px; }
            .button { display: inline-block; padding: 12px 24px; background: #0d6efd; color: white; text-decoration: none; border-radius: 4px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #6c757d; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Welcome to Cur1 Fashion!</h1>
            </div>
            <div class='content'>
                <p>Dear {$name},</p>
                <p>Thank you for joining Cur1 Fashion! We're excited to have you as part of our community.</p>
                <p>Start shopping now and discover our latest collection of premium fashion items.</p>
                <p style='text-align: center;'>
                    <a href='" . getBaseUrl() . "/index.php' class='button'>Start Shopping</a>
                </p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Cur1 Fashion. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";
    
    return sendEmail($email, $subject, $body, $name);
}

