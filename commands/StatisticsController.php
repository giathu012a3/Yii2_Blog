<?php

declare(strict_types=1);

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;
use app\models\Post;
use app\models\Comment;
use app\models\PostLike;
use app\models\DailyPostStats;

/**
 * Handles post statistics generation.
 */
class StatisticsController extends Controller
{
    /**
     * Calculates statistics (posts, comments, likes, views) for a given date.
     *
     * @param string|null $date Date in YYYY-MM-DD format. Defaults to current date.
     * @return int
     */
    public function actionCalculate(?string $date = null): int
    {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        // Validate date format YYYY-MM-DD
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->stderr("Error: Invalid date format. Please use YYYY-MM-DD.\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $start = strtotime($date . ' 00:00:00');
        $end = strtotime($date . ' 23:59:59');

        if ($start === false || $end === false) {
            $this->stderr("Error: Invalid date parameters.\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Calculating statistics for {$date}...\n", Console::FG_YELLOW);

        // Count posts published on this date (where is_deleted = 0)
        $postsCount = (int) Post::find()
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['between', 'published_at', $start, $end])
            ->count();

        // Count comments created on this date (where is_deleted = 0)
        $commentsCount = (int) Comment::find()
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['between', 'created_at', $start, $end])
            ->count();

        // Count likes created on this date
        $likesCount = (int) PostLike::find()
            ->andWhere(['between', 'created_at', $start, $end])
            ->count();

        // Sum views of posts published on this date
        $viewsCount = (int) Post::find()
            ->andWhere(['is_deleted' => 0])
            ->andWhere(['between', 'published_at', $start, $end])
            ->sum('view_count');

        // Upsert stats record
        $stats = DailyPostStats::findOne($date);
        if ($stats === null) {
            $stats = new DailyPostStats();
            $stats->date = $date;
        }

        $stats->posts_count = $postsCount;
        $stats->comments_count = $commentsCount;
        $stats->likes_count = $likesCount;
        $stats->views_count = $viewsCount;

        if (!$stats->save()) {
            $errors = json_encode($stats->errors);
            $this->stderr("Error saving statistics: {$errors}\n", Console::FG_RED);
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $this->stdout("Daily statistics for {$date} successfully generated and stored:\n", Console::FG_GREEN);
        $this->stdout(" - New Posts: {$postsCount}\n");
        $this->stdout(" - New Comments: {$commentsCount}\n");
        $this->stdout(" - New Likes: {$likesCount}\n");
        $this->stdout(" - New Views: {$viewsCount}\n");

        // Send notification email to admin
        $adminEmail = Yii::$app->params['adminEmail'] ?? null;
        if (!empty($adminEmail)) {
            try {
                $sent = Yii::$app->mailer->compose('statistics', [
                    'date' => $date,
                    'postsCount' => $postsCount,
                    'commentsCount' => $commentsCount,
                    'likesCount' => $likesCount,
                    'viewsCount' => $viewsCount,
                ])
                ->setTo($adminEmail)
                ->setSubject("Daily Post Statistics Report - {$date}")
                ->send();

                if ($sent) {
                    $this->stdout("Statistics report email successfully sent to {$adminEmail}.\n", Console::FG_GREEN);
                } else {
                    $this->stdout("Failed to send statistics report email.\n", Console::FG_YELLOW);
                }
            } catch (\Throwable $e) {
                Yii::error("Failed to send stats email: " . $e->getMessage(), 'email');
                $this->stdout("Email notification skipped/failed: " . $e->getMessage() . "\n", Console::FG_YELLOW);
            }
        }

        return ExitCode::OK;
    }
}
