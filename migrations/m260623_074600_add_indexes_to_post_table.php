<?php

use yii\db\Migration;

/**
 * Class m260623_074600_add_indexes_to_post_table
 */
class m260623_074600_add_indexes_to_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idx-post-published_at', 'post', 'published_at');
        $this->createIndex('idx-post-created_at', 'post', 'created_at');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-post-published_at', 'post');
        $this->dropIndex('idx-post-created_at', 'post');
    }
}
