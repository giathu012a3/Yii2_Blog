<?php

use yii\db\Migration;

class m260616_041940_add_column_storage_key_media_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('media','storage_key',$this->string()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('media','storage_key');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260616_041940_add_column_storage_key_media_table cannot be reverted.\n";

        return false;
    }
    */
}
