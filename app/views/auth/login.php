<!-- FILE: /app/views/auth/login.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SplashMarket</title>
    <link rel="stylesheet" href="<?php echo asset('css/admin.css'); ?>">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <h1>SplashMarket</h1>
            <h2>Login to your account</h2>

            <?php
            $error = Session::getFlash('error');
            if ($error):
            ?>
            <div class="alert alert-error">
                <?php echo e($error); ?>
            </div>
            <?php endif; ?>

            <?php
            $success = Session::getFlash('success');
            if ($success):
            ?>
            <div class="alert alert-success">
                <?php echo e($success); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="/login" class="auth-form">
                <?php echo csrf_field(); ?>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>

            <p class="auth-footer">
                Don't have an account? <a href="/register">Register here</a>
            </p>
        </div>
    </div>
</body>
</html>
