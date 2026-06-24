<?php

use yii\db\Migration;

class m260608_070440_create_table_post extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('post',[
            'id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'author_id' => $this->integer()->notNull(),
            'title' => $this->string()->notNull(),
            'description' => $this->text(),
            'thumbnail' => $this->string(),
            'slug' => $this->string()->notNull()->unique(),
            'content' => $this->text(),
            'status' => $this->integer()->notNull()->defaultValue(0),
            'published_at' => $this->integer(),
            'view_count' => $this->integer()->notNull()->defaultValue(0),
            'is_deleted' => $this->integer()->notNull()->defaultValue(0),
            'deleted_at' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createIndex('idx-post-status','post','status');
        $this->createIndex('idx-post-category_id','post','category_id');
        $this->createIndex('idx-post-author_id','post','author_id');
        $this->createIndex('idx-post-is_deleted','post','is_deleted');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-post-status','post');
        $this->dropIndex('idx-post-category_id','post');
        $this->dropIndex('idx-post-author_id','post');
        $this->dropIndex('idx-post-is_deleted','post');
        $this->dropTable('post');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260608_070440_create_table_post cannot be reverted.\n";

        return false;
    }
    */
}
