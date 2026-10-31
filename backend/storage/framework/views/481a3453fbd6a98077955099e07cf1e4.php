<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0f0f23;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #e2e8f0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .header {
            text-align: center;
            padding: 30px 0;
            border-bottom: 1px solid rgba(0, 212, 255, 0.2);
        }
        .header h1 {
            color: #00d4ff;
            font-size: 28px;
            margin: 0;
        }
        .header .lock {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px 0;
            line-height: 1.6;
        }
        .content h2 {
            color: #ffffff;
            font-size: 22px;
            margin-top: 0;
        }
        .btn {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(90deg, #00d4ff, #9945ff);
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            margin: 20px 0;
        }
        .btn:hover {
            background: linear-gradient(90deg, #00b8e6, #8833ff);
        }
        .notice {
            background: rgba(255, 170, 0, 0.05);
            border: 1px solid rgba(255, 170, 0, 0.15);
            border-radius: 8px;
            padding: 16px;
            margin: 20px 0;
            font-size: 13px;
            color: #ffaa00;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 12px;
            color: #718096;
        }
        .footer a {
            color: #00d4ff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="lock">🔐</div>
            <h1>CyberLab Academy</h1>
        </div>

        <div class="content">
            <h2>Reset Your Password</h2>

            <p>Hi <?php echo e($user->name); ?>,</p>

            <p>We received a request to reset your password for your CyberLab Academy account. Click the button below to set a new password:</p>

            <p style="text-align: center;">
                <a href="<?php echo e($resetUrl); ?>" class="btn">Reset Password</a>
            </p>

            <p>This link will expire in 2 hours.</p>

            <div class="notice">
                If you did not request a password reset, no further action is required. Your account is safe.
            </div>

            <p>If the button above doesn't work, copy and paste this URL into your browser:</p>
            <p style="word-break: break-all; font-size: 12px; color: #00d4ff;"><?php echo e($resetUrl); ?></p>
        </div>

        <div class="footer">
            <p>&copy; <?php echo e(date('Y')); ?> CyberLab Academy. All rights reserved.</p>
            <p>
                <a href="<?php echo e(config('app.url')); ?>">Visit our website</a>
            </p>
        </div>
    </div>
</body>
</html>
<?php /**PATH /Users/macbookuz/cyberlab-academy/backend/resources/views/emails/password-reset.blade.php ENDPATH**/ ?>