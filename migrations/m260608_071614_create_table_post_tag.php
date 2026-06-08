<?php

use yii\db\Migration;

class m260608_071614_create_table_post_tag extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('post_tag',[
            'post_id' => $this->integer()->notNull(),
            'tag_id' => $this->integer()->notNull(),
            'created_at' => $this->integer(),
        ]);

        $this->addPrimaryKey('post_tag','post_tag',['post_id','tag_id']);
        $this->createIndex('idx-post_tag-tag_id','post_tag','tag_id');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-post_tag-tag_id','post_tag');
        $this->dropTable('post_tag');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260608_071614_create_table_post_tag cannot be reverted.\n";

        return false;
    }
    */
}
