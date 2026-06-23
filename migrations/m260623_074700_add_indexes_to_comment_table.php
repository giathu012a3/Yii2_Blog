<?php

use yii\db\Migration;

/**
 * Class m260623_074700_add_indexes_to_comment_table
 */
class m260623_074700_add_indexes_to_comment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idx-comment-created_at', 'comment', 'created_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-comment-created_at', 'comment');
    }
}
