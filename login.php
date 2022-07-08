<?php
include(__DIR__.'/layouts/header.php');
require_once(__DIR__.'/config.php');

// Redirect to home page if user logged in
if(isset($_SESSION['user'])){
    header('Location: '.$GLOBALS['APP_URL']);
}

if(isset($_POST['username']) && isset($_POST['password'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    /**
     * @var $pdo
     */
    try{
        // Retrieve user from database
        $query = $pdo->prepare('SELECT * FROM users WHERE username = ?');
        $query->execute([$username]);
        $user = $query->fetch();

        // Check email and password
        if(!$user || !password_verify($password, $user['password'])){
            throw new Exception('Incorrect email or password!');
        }

        // Save logged in session
        $_SESSION['user'] = $user;

        // Redirect to homepage
        header('location: '.$GLOBALS['APP_URL']);
    }catch (Exception $e){
        $error = $e->getMessage();
    }
}
?>

<div class="jumbotron text-center py-4">
    <h1>Login</h1>
</div>

<div class="container">
    <div class="row">
        <div class="col-sm-6 offset-sm-3">
            <?php if(isset($error)): ?>
                <h4 class="text-danger text-center"><?= $error ?></h4>
            <?php endif ?>
            <form action="/login.php" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" placeholder="Enter Username" id="username" name="username" value="<?= isset($username) ? $username : ''?>">
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" placeholder="Enter Password" id="password" name="password">
                </div>
                <div class="form-group">
                    <a href="/forgot-password.php">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>
</div>

<?php include('layouts/footer.php') ?>
