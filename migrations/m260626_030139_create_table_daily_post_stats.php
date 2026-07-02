<?php

use yii\db\Migration;

class m260626_030139_create_table_daily_post_stats extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%daily_post_stats}}', [
            'date' => $this->date()->notNull(),
            'posts_count' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'comments_count' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'likes_count' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'views_count' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->addPrimaryKey('pk-daily_post_stats', '{{%daily_post_stats}}', 'date');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%daily_post_stats}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260626_030139_create_table_daily_post_stats cannot be reverted.\n";

        return false;
    }
    */
}
