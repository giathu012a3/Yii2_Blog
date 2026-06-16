<?php

use yii\db\Migration;

class m260616_033824_alter_post_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('post','thumbnail');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('post','thumbnail', $this->string());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260616_033824_alter_post_table cannot be reverted.\n";

        return false;
    }
    */
}
