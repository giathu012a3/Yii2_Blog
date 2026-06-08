<?php

use yii\db\Migration;

class m260608_043814_create_table_post_tag extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260608_043814_create_table_post_tag cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260608_043814_create_table_post_tag cannot be reverted.\n";

        return false;
    }
    */
}
