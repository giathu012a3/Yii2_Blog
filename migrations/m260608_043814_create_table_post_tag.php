<?php

use yii\db\Migration;

class m260608_043814_create_table_post_tag extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        try {
            $this->createTable('{{%post_tag}}', [
                'post_id' => $this->integer()->unsigned()->notNull(),
                'tag_id'  => $this->integer()->unsigned()->notNull(),
                'PRIMARY KEY(post_id, tag_id)',
            ]);
        } catch (\Throwable $e) {
            echo "    [SKIP] createTable {{%post_tag}}: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-post_tag-post_id', '{{%post_tag}}', 'post_id');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-post_tag-post_id: " . $e->getMessage() . "\n";
        }

        try {
            $this->createIndex('idx-post_tag-tag_id', '{{%post_tag}}', 'tag_id');
        } catch (\Throwable $e) {
            echo "    [SKIP] idx-post_tag-tag_id: " . $e->getMessage() . "\n";
        }
    }

    public function safeDown()
    {
        try {
            $this->dropTable('{{%post_tag}}');
        } catch (\Throwable $e) {
            echo "    [SKIP] dropTable {{%post_tag}}: " . $e->getMessage() . "\n";
        }
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
