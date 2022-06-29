<?php

include(__DIR__ . '/layouts/header.php');
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/helpers.php');

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    try {
        // Check CAPTCHA
        if (!isset($_POST['g-recaptcha-response']) || !$_POST['g-recaptcha-response']) {
            throw new Exception('Please check the the captcha form.');
        }
        $captcha   = $_POST['g-recaptcha-response'];
        $secretKey = getenv('CAPTCHA_SECRET_KEY');

        $url          = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) . '&response=' . urlencode($captcha);
        $response     = file_get_contents($url);
        $responseKeys = json_decode($response, true);
        if (!$responseKeys["success"]) {
            throw new Exception('Please do not spam!');
        }

        /**
         * @var $pdo
         */
        // Retrieve user from database
        $query = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $query->execute([$email]);
        $row = $query->rowCount();

        // If user exists in our system, send email with link
        if ($row) {
            // Delete any existing reset token for the user
            // Create and store a new time-stamped reset token
            $key         = getenv('HASH_SALT');
            $reset_token = encrypt(time(), $key);
            $query       = $pdo->prepare('UPDATE users SET reset_token = ? WHERE username = ?');
            $query->execute([$reset_token, $email]);

            // Email body
            $link = getenv('APP_URL') . "/reset-password?email=$email&reset_code=".base64_encode($reset_token);
            $body = "<p>Dear $email,</p>
                
                <p>Please click on this link to reset your password:</p> 
                <p><a href='$link'>Link</a></p>
    
                Best wishes,
                <br>
                <span>" . getenv('APP_NAME') . "</span>";

            // Displayed a message to the user
            $message = "If $email is associated with an account in our system, we have sent you a password reset link.";
        } // If account does not exists in our system, send an email stating that their account does not exist in our system
        else {
            // Email body
            $body = "<p>Dear $email,</p>
                
                <p>Sorry, you account does not exist in our system.</p> 
    
                Best wishes,
                <br>
                <span>" . getenv('APP_NAME') . "</span>";
        }

        // Send an email to user
        $to      = $email;
        $subject = 'Reset Password';
        send_email($to, $subject, $body);
    } catch (Exception $e) {
        // Displayed an error if exists to the user
        $error = $e->getMessage();
    }
}
?>

<div class="jumbotron text-center py-4">
    <h1>Forgot Password</h1>
</div>

<div class="container">
    <div class="row">
        <div class="col-sm-6 offset-sm-3">
            <?php if (isset($message)): ?>
                <div class="alert alert-success text-center"><?= $message ?></div>
            <?php endif ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger text-center"><?= $error ?></div>
            <?php endif ?>
            <form action="/forgot-password" method="POST">
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" class="form-control" placeholder="Enter Email Address" id="email" name="email"
                           value="<?= isset($email) ? $email : '' ?>">
                </div>
                <div class="g-recaptcha mb-3" data-sitekey="<?= getenv('CAPTCHA_SITE_KEY') ?>"></div>
                <button type="submit" name="submit" class="btn btn-primary">Reset Password</button>
            </form>
        </div>
    </div>
</div>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php include('layouts/footer.php') ?>
