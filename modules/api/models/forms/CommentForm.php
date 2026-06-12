<?php

declare(strict_types=1);

namespace app\modules\api\models\forms;

use app\models\Comment;
use Yii;

class CommentForm extends Comment
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['content'], 'string', 'min' => 1, 'max' => 5000],
            [['parent_id'], 'validateParent'],
        ]);
    }

    public function load($data, $formName = null): bool
    {
        if (is_array($data)) {
            unset($data['post_id'], $data['user_id'], $data['is_deleted'], $data['deleted_at']);
        }
        return parent::load($data, $formName);
    }

    public function beforeValidate(): bool
    {
        if ($this->isNewRecord) {
            $this->user_id = (int) Yii::$app->user->id;
        }

        return parent::beforeValidate();
    }

    public function validateParent(string $attribute): void
    {
        if ($this->parent_id === null) {
            return;
        }

        $parent = Comment::find()->active()->andWhere(['id' => $this->parent_id])->one();

        if ($parent === null) {
            $this->addError($attribute, 'Parent comment does not exist or has been deleted.');
            return;
        }

        if ($parent->parent_id !== null) {
            $this->addError($attribute, 'Replies to replies are not allowed (max 1 level of nesting).');
        }
    }
}
