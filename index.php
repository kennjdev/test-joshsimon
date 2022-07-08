<?php include(__DIR__.'/layouts/header.php'); ?>

<div class="jumbotron text-center py-4">
    <h1>Home Page</h1>
</div>
<div class="container">
    <?php if(isset($_SESSION['user'])): ?>
    <div class="row">
        <div class="col-sm-12 text-center">
            <!--Displays the user's email address.-->
            Welcome, <b><?= $_SESSION['user']['username']?></b>
            <!--Let's user know they have logged in.-->
            <p>Thank you for logging in to your account!</p>
        </div>
    </div>
    <?php endif ?>
</div>

<?php include('layouts/footer.php') ?>
