<?php

use yii\db\Migration;

class m260608_072408_create_table_post_like extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('post_like',[
            'post_id' => $this->integer()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'created_at' => $this->integer(),
        ]);

        $this->addPrimaryKey('post-like','post_like',['post_id','author_id']);
        $this->createIndex('idx-post-like-author','post_like','author_id');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-post-like-author','post_like');
        $this->dropTable('post_like');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260608_072408_create_table_like cannot be reverted.\n";

        return false;
    }
    */
}
