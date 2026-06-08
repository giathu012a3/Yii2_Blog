<?php

use yii\db\Migration;

class m260608_072015_create_table_comment extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('comment',[
            'id' => $this->primaryKey(),
            'post_id' => $this->integer()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'parent_id' => $this->integer(),
            'content' => $this->text(),
            'status' => $this->integer()->notNull()->defaultValue(1),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createIndex('idx-comment-post_id','comment','post_id');
        $this->createIndex('idx-comment-author_id','comment','author_id');
        $this->createIndex('idx-comment-status','comment','status');
        $this->createIndex('idx-comment-parent_id','comment','parent_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-comment-post_id','comment');
        $this->dropIndex('idx-comment-author_id','comment');
        $this->dropIndex('idx-comment-status','comment');
        $this->dropIndex('idx-comment-parent_id','comment');
        $this->dropTable('comment');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260608_072015_create_table_comment cannot be reverted.\n";

        return false;
    }
    */
}
