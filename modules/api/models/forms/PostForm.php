<?php
namespace app\modules\api\models\forms;

use app\models\Category;
use app\models\Post;
use Yii;

class PostForm extends Post
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['category_id'], 'exist', 'targetClass' => Category::class, 'targetAttribute' => 'id'],
            [['status'], 'default', 'value' => self::STATUS_DRAFT],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_PUBLISHED]],
            [['content'], 'required'],
        ]);
    }

    public function beforeValidate()
    {
        if($this->isNewRecord && empty($this->author_id))
        {
            $this->author_id = Yii::$app->user->id;
        }
        return parent::beforeValidate();
    }
}
