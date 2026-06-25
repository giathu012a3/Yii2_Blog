<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user app\models\User */

?>
<div class="welcome-email" style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;">
    <h2 style="color: #4CAF50; border-bottom: 2px solid #4CAF50; padding-bottom: 10px;">Welcome to Yii2 Blog App!</h2>
    <p>Hello <strong><?= Html::encode($user->username) ?></strong>,</p>
    <p>Thank you for registering on our platform. Your account has been successfully created.</p>
    <p>You can now log in using your registered username or email and start participating in the blog!</p>
    <div style="margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-left: 4px solid #4CAF50;">
        <p style="margin: 0 0 10px 0;"><strong>Your Account Details:</strong></p>
        <p style="margin: 0 0 5px 0;">Username: <code><?= Html::encode($user->username) ?></code></p>
        <p style="margin: 0;">Email: <code><?= Html::encode($user->email) ?></code></p>
    </div>
    <br>
    <p>Best regards,</p>
    <p><strong>Yii2 Blog App Team</strong></p>
</div>
