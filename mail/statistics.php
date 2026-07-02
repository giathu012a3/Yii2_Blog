<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $date string */
/* @var $postsCount int */
/* @var $commentsCount int */
/* @var $likesCount int */
/* @var $viewsCount int */
?>
<div class="statistics-report" style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;">
    <h2 style="color: #2196F3; border-bottom: 2px solid #2196F3; padding-bottom: 10px;">Daily Post Statistics Report</h2>
    <p>Hello Admin,</p>
    <p>Here is the automated post statistics report for <strong><?= Html::encode($date) ?></strong>:</p>
    
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th style="border: 1px solid #dddddd; text-align: left; padding: 8px;">Metric</th>
                <th style="border: 1px solid #dddddd; text-align: right; padding: 8px;">Count</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border: 1px solid #dddddd; padding: 8px;">New Posts Published</td>
                <td style="border: 1px solid #dddddd; text-align: right; padding: 8px; font-weight: bold;"><?= $postsCount ?></td>
            </tr>
            <tr>
                <td style="border: 1px solid #dddddd; padding: 8px;">New Comments Added</td>
                <td style="border: 1px solid #dddddd; text-align: right; padding: 8px; font-weight: bold;"><?= $commentsCount ?></td>
            </tr>
            <tr>
                <td style="border: 1px solid #dddddd; padding: 8px;">New Likes Received</td>
                <td style="border: 1px solid #dddddd; text-align: right; padding: 8px; font-weight: bold;"><?= $likesCount ?></td>
            </tr>
            <tr>
                <td style="border: 1px solid #dddddd; padding: 8px;">Total Views of Posts Published Today</td>
                <td style="border: 1px solid #dddddd; text-align: right; padding: 8px; font-weight: bold;"><?= $viewsCount ?></td>
            </tr>
        </tbody>
    </table>
    
    <p>This is an automated system email. Please do not reply to this message.</p>
    <br>
    <p>Best regards,</p>
    <p><strong>Yii2 Blog App System</strong></p>
</div>
