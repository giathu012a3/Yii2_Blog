<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use Yii;
use app\models\DailyPostStats;
use yii\web\BadRequestHttpException;
use yii\filters\AccessControl;

/**
 * PostStatsController serves daily post statistics to the frontend.
 */
class PostStatsController extends BaseApiController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index'],
                    'roles' => ['@'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * Get daily post statistics filtered by date range.
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        if ($startDate === null) {
            $startDate = date('Y-m-d', strtotime('-6 days'));
        }
        if ($endDate === null) {
            $endDate = date('Y-m-d');
        }
        $dateRegex = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($dateRegex, $startDate) || !preg_match($dateRegex, $endDate)) {
            throw new BadRequestHttpException(Yii::t('app', 'Invalid date format. Use YYYY-MM-DD.'));
        }

        if (strtotime($startDate) > strtotime($endDate)) {
            throw new BadRequestHttpException(Yii::t('app', 'start_date cannot be greater than end_date.'));
        }

        $cacheKey = "daily_post_stats_{$startDate}_{$endDate}";
        $data = Yii::$app->cache->get($cacheKey);

        if ($data === false) {
            $data = DailyPostStats::find()
                ->andWhere(['between', 'date', $startDate, $endDate])
                ->orderBy(['date' => SORT_ASC])
                ->all();

            Yii::$app->cache->set($cacheKey, $data, 300);
        }

        return $data;
    }
}
