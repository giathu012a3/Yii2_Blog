<?php

use yii\db\Migration;

class m260608_073112_create_table_media extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('media',[
            'id' => $this->primaryKey(),
            'model_id' => $this->integer()->notNull(),
            'model_name' => $this->string()->notNull(),
            'url' => $this->string(),
            'file_size' => $this->integer(),
            'mime_type' => $this->string(),
            'created_at' => $this->integer(),
        ]);

        $this->createIndex('idx-media-model_id','media',['model_id','model_name']);


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-media-model_id','media');
        $this->dropTable('media');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260608_073112_create_table_media cannot be reverted.\n";

        return false;
    }
    */
}
