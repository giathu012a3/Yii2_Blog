<?php

namespace app\modules\api\models\forms;

use app\models\Comment;
use app\models\Post;

class CommentForm extends Comment
{
    public function rules()
    {
        return [
            [['content'], 'required'],
            ['post_id', 'exist',
                'targetClass' => Post::class,
                'targetAttribute' => 'id',
                'filter' => function ($query) {
                    $query->notDelete()->published();
                },
            ],
             ['parent_id', 'exist',
                'targetClass' => Comment::class,
                'targetAttribute' => 'id',
                'filter' => function ($query) {
                    $query->andWhere([
                        'status' => Comment::STATUS_ACTIVE,
                        'post_id' => $this->post_id,
                    ]);
                },
            ],
        ];
    }

}
