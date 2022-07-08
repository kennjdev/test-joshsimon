<?php

include(__DIR__ . '/layouts/header.php');
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/helpers.php');

/**
 * @var $pdo
 */
if (isset($_POST['submit'])) {
    $email            = $_GET['email'];
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    try {
        // Check password at least 6 characters
        if (strlen($password) < 6) {
            throw new Exception('Please input your password at least 6 characters');
        }

        // Check both password fields match
        if ($password != $confirm_password) {
            throw new Exception('Password does not match');
        }

        // Store the new password and delete the token from the database
        $salt                = $GLOBALS['HASH_SALT'];
        $hashed_new_password = password_hash($password, PASSWORD_BCRYPT, ['salt', $salt]);
        $query               = $pdo->prepare('UPDATE users SET reset_token = NULL, password = ? WHERE username = ?');
        $query->execute([$hashed_new_password, $email]);

        // Send the user an email stating the password has been changed.
        $to      = $email;
        $subject = 'Reset Password';
        $body    = "<p>Dear $email,</p>
                
                <p>Your password has been changed.</p> 
    
                Best wishes,
                <br>
                <span>" . $GLOBALS['APP_NAME'] . "</span>";
        send_email($to, $subject, $body);

        // Display a success message to the user
        $message = 'Reset your password successfully!';
    } catch (Exception $e) {
        // Display an error message to the user
        $alert = $e->getMessage();
    }
} elseif (isset($_GET['email']) && isset($_GET['reset_code'])) {
    $email      = $_GET['email'];
    $reset_code = base64_decode($_GET['reset_code']);

    try {
        // Check if the token is not valid
        $query = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $query->execute([$email]);
        $user = $query->fetch();
        if (!$user || $reset_code != $user['reset_token']) {
            throw new Exception('Sorry, your link is invalid!');
        }

        // Check if the token has expired
        $key  = $GLOBALS['HASH_SALT'];
        $time = decrypt($reset_code, $key);
        if ($time <= strtotime('-1 hour')) {
            throw new Exception('Sorry, your link has expired!');
        }
    } catch (Exception $e) {
        // Display an error message to the user
        $error = $e->getMessage();
    }
} else {
    // Check if link is not valid
    $error = 'Sorry, your link is invalid';
}
?>

<div class="jumbotron text-center py-4">
    <h1>Reset Password</h1>
</div>

<div class="container">
    <div class="row">
        <div class="col-sm-6 offset-sm-3">

            <?php if (isset($error)): ?>
                <div class="alert alert-danger text-center">
                    <?= $error ?><br>
                    Please <a href="/forgot-password.php">click here</a> to reset password again!
                </div>
            <?php elseif (isset($message)): ?>
                <div class="alert alert-success text-center">
                    <?= $message ?>
                    <br>
                    Please <a href="/login.php">click here</a> to login!
                </div>
            <?php else: ?>
                <?php if (isset($alert)): ?>
                    <div class="alert alert-danger text-center">
                        <?= $alert ?>
                    </div>
                <?php endif ?>
                <form action="/reset-password.php?<?= $_SERVER['QUERY_STRING'] ?>" method="POST">
                    <div class="form-group">
                        <label for="password">New Password:</label>
                        <input type="password" class="form-control" placeholder="Enter New Password" id="password"
                               name="password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" class="form-control" placeholder="Enter Confirm New Password"
                               id="confirm_password" name="confirm_password">
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">Reset Password</button>
                </form>
            <?php endif ?>
        </div>
    </div>
</div>

<?php include('layouts/footer.php') ?>
