<?php

use yii\db\Migration;

/**
 * Class m260623_074500_add_indexes_to_media_table
 */
class m260623_074500_add_indexes_to_media_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idx-media-url', 'media', 'url');
        $this->createIndex('idx-media-storage_key', 'media', 'storage_key');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-media-url', 'media');
        $this->dropIndex('idx-media-storage_key', 'media');
    }
}
