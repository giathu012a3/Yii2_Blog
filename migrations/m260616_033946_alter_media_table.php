<?php

use yii\db\Migration;

class m260616_033946_alter_media_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('media', 'model_name', $this->string()->null());
        $this->alterColumn('media', 'model_id', $this->integer()->null());

        $this->addColumn('media', 'collection', $this->string());
        $this->createIndex('idx-media-collection', 'media', 'collection');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-media-collection', 'media');
        $this->dropColumn('media','collection');
        $this->alterColumn('media', 'model_name', $this->string());
        $this->alterColumn('media', 'model_id', $this->integer());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260616_033946_alter_media_table cannot be reverted.\n";

        return false;
    }
    */
}
