<?php
require_once 'config/db.php';
$pageTitle = 'Contact Us';
$basePath  = '';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $subject, $message]);
        $success = 'Thank you for contacting us! We will get back to you shortly.';
    }
}

require_once 'includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>Contact Us</h1>
        <p>Have questions? We're here to help.</p>
    </div>
</div>

<section class="section" style="padding-top:0;">
    <div class="container">
        <div class="booking-layout" style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: start;">
            
            <!-- Contact Info -->
            <div>
                <h2 style="margin-bottom: 1.5rem; font-size: 1.8rem;">Get in Touch</h2>
                <p style="color: var(--gray-mid); margin-bottom: 2rem; line-height: 1.6;">
                    Whether you're looking for a specific car model, have questions about our rental policies, or need assistance with your booking, our dedicated support team is ready to assist you.
                </p>
                
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="font-size: 1.5rem; background: #e0e7ff; color: var(--primary-color); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">Location</div>
                        <div>
                            <h4 style="margin-bottom: 0.2rem;">Our Location</h4>
                            <p style="color: var(--gray-mid); font-size: 0.95rem;">Adrar</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="font-size: 1.5rem; background: #e0e7ff; color: var(--primary-color); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">Phone</div>
                        <div>
                            <h4 style="margin-bottom: 0.2rem;">Phone Number</h4>
                            <p style="color: var(--gray-mid); font-size: 0.95rem;">07774778744</p>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="font-size: 1.5rem; background: #e0e7ff; color: var(--primary-color); width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">Email</div>
                        <div>
                            <h4 style="margin-bottom: 0.2rem;">Email Address</h4>
                            <p style="color: var(--gray-mid); font-size: 0.95rem;">contactus@gmail.com</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="form-card" style="background: #fff; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                <h3 style="margin-bottom: 1.5rem;">Send us a Message</h3>

                <?php if ($error): ?>
                    <div class="alert alert-error" style="margin-bottom: 1.5rem;">Error: <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success" style="margin-bottom: 1.5rem; background: #dcfce7; color: #166534; padding: 1rem; border-radius: 6px;">Success: <?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="post" action="contact.php">
                    <div class="form-group" style="margin-bottom: 1.2rem;">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px;" value="<?= isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['user_name']) : '' ?>">
                    </div>

                    <div class="form-group" style="margin-bottom: 1.2rem;">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 1.2rem;">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" required style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px;">
                    </div>

                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" rows="5" required style="width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 6px; resize: vertical;"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; border-radius: 6px; font-size: 1.1rem; border: none; cursor: pointer;">
                        Send Message
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
