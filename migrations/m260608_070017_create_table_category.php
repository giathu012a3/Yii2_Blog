<?php

use yii\db\Migration;

class m260608_070017_create_table_category extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('category',[
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'status' => $this->integer()->notNull()->defaultValue(1),
            'slug' => $this->string()->notNull()->unique(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createIndex('idx-category-status','category','status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-category-status','category');
        $this->dropTable('category');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260608_070017_create_table_category cannot be reverted.\n";

        return false;
    }
    */
}
