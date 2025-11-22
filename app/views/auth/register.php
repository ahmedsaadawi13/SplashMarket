<!-- FILE: /app/views/auth/register.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SplashMarket</title>
    <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <h1>SplashMarket</h1>
            <h2>Create your store</h2>

            <?php
            $error = Session::getFlash('error');
            if ($error):
            ?>
            <div class="alert alert-error">
                <?php echo e($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="/register" class="auth-form">
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label for="store_name">Store Name</label>
                    <input type="text" id="store_name" name="store_name" required value="<?php echo old('store_name'); ?>">
                </div>

                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" required value="<?php echo old('name'); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required value="<?php echo old('email'); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <small>Minimum 6 characters</small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Store</button>
            </form>

            <p class="auth-footer">
                Already have an account? <a href="/login">Login here</a>
            </p>
        </div>
    </div>
</body>
</html>
