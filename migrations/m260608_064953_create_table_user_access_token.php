<?php

use yii\db\Migration;

class m260608_064953_create_table_user_access_token extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_access_token',[
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'token' => $this->string()->notNull(),
            'expires_at' => $this->integer(),
            'revoked_at' => $this->integer(),
            'device_name' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createIndex('idx-user_access_token-user_id','user_access_token','user_id');
        $this->createIndex('idx-user_access_token-token','user_access_token','token');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-user_access_token-user_id','user_access_token');
        $this->dropIndex('idx-user_access_token-token','user_access_token');
        $this->dropTable('user_access_token');

    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260608_064953_create_table_user_access_token cannot be reverted.\n";

        return false;
    }
    */
}
