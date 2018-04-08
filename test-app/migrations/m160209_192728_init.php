<?php

use yii\boost\db\Migration;

class m160209_192728_init extends Migration
{

    public function up()
    {
        // root_folder_type
        $this->createTableWithComment('root_folder_type', [
            'id' => $this->tinyInteger()->unsigned(),
            'name' => $this->string(25)->notNull()->unique()->comment('Название'),
            'code' => $this->string(25)->notNull()->unique()->comment('Код')
        ], 'Тип корневой папки');
        $this->addPrimaryKey(null, 'root_folder_type', ['id']);
        $this->insert('root_folder_type', ['id' => 1, 'name' => 'Музыка', 'code' => 'music']);
        $this->insert('root_folder_type', ['id' => 2, 'name' => 'Видео', 'code' => 'video']);

        // root_folder
        $this->createTableWithComment('root_folder', [
            'id' => $this->primaryKey(),
            'root_folder_type_id' => $this->tinyInteger()->unsigned()->notNull()->comment('Тип корневой папки'),
            'alt_type_id' => $this->tinyInteger()->unsigned()->notNull()->comment('Тип корневой папки'),
            'name' => $this->string(50)->notNull()->unique()->comment('Название')
        ], 'Корневая папка');
        $this->addForeignKey(null, 'root_folder', ['root_folder_type_id'], 'root_folder_type', ['id']);
        $this->addForeignKey(null, 'root_folder', ['alt_type_id'], 'root_folder_type', ['id']);

        // folder
        $this->createTableWithComment('folder', [
            'id' => $this->primaryKey(),
            'root_folder_id' => $this->integer()->unsigned()->notNull()->comment('Корневая папка'),
            'alt_folder_id' => $this->integer()->unsigned()->notNull()->comment('Корневая папка'),
            'name' => $this->string(50)->notNull()->comment('Название'),
            'visible' => $this->boolean()->notNull()->defaultValue(1)->comment('Видимый'),
            'created_at' => $this->createdAtShortcut()->comment('Создано в'),
            'updated_at' => $this->updatedAtShortcut()->comment('Обновлено в'),
            'deleted' => $this->deletedShortcut()
        ], 'Папка');
        $this->createUnique(null, 'folder', ['root_folder_id', 'name']);
        $this->addForeignKey(null, 'folder', ['root_folder_id'], 'root_folder', ['id']);
        $this->addForeignKey(null, 'folder', ['alt_folder_id'], 'root_folder', ['id']);

        // file
        $this->createTableWithComment('file', [
            'id' => $this->primaryKey(),
            'root_folder_id' => $this->integer()->unsigned()->notNull()->comment('Корневая папка'),
            'folder_id' => $this->integer()->unsigned()->notNull()->comment('Папка'),
            'name' => $this->string(50)->notNull()->comment('Название'),
            'visible' => $this->boolean()->notNull()->defaultValue(1)->comment('Видимый'),
            'created_at' => $this->createdAtShortcut()->comment('Создано в'),
            'updated_at' => $this->updatedAtShortcut()->comment('Обновлено в'),
            'deleted' => $this->deletedShortcut()
        ], 'Файл');
        $this->createUnique(null, 'file', ['folder_id', 'name']);
        $this->addForeignKey(null, 'file', ['root_folder_id'], 'root_folder', ['id']);
        $this->createIndex(null, 'folder', ['id', 'root_folder_id']);
        $this->addForeignKey(null, 'file', ['folder_id', 'root_folder_id'], 'folder', ['id', 'root_folder_id']);

        // file_info_type
        $this->createTableWithComment('file_info_type', [
            'id' => $this->tinyInteger()->unsigned(),
            'name' => $this->string(25)->notNull()->unique()->comment('Название'),
            'code' => $this->string(25)->notNull()->unique()->comment('Код')
        ], 'Тип информации о файле');
        $this->addPrimaryKey(null, 'file_info_type', ['id']);
        $this->insert('file_info_type', ['id' => 1, 'name' => 'Музыка', 'code' => 'music']);
        $this->insert('file_info_type', ['id' => 2, 'name' => 'Видео', 'code' => 'video']);

        // file_info
        $this->createTableWithComment('file_info', [
            'file_id' => $this->integer()->unsigned()->notNull(),
            'file_info_type_id' => $this->tinyInteger()->unsigned()->notNull()->comment('Тип информации о файле'),
            'info' => $this->text()
        ], 'Информация о файле');
        $this->addPrimaryKey(null, 'file_info', ['file_id']);
        $this->addForeignKey(null, 'file_info', ['file_id'], 'file', ['id']);
        $this->addForeignKey(null, 'file_info', ['file_info_type_id'], 'file_info_type', ['id']);

        // file_report
        $sql = <<<SQL
CREATE VIEW `file_report`
AS SELECT
    f.id AS `pk_id`,
    f.root_folder_id AS `pk_root_folder_id`,
    f.folder_id AS `uk_folder_id`,
    f.name AS `uk_name`,
    f.visible,
    f.created_at,
    f.updated_at,
    f.deleted
FROM `file` f;
SQL;
        $this->db->createCommand($sql)->execute();

        // something
        $this->createTable('something', [
            'tiny_id' => $this->tinyInteger()->unsigned(),
            'small_id' => $this->smallInteger()->unsigned(),
            'expires_at' => $this->date(),
            'second_expires_at' => $this->date()->notNull(),
            'third_expires_at' => $this->dateTime(),
            'fourth_expires_at' => $this->dateTime()->notNull()
        ]);
        $this->addPrimaryKey(null, 'something', ['tiny_id', 'small_id']);

        // sequence
        $this->createTable('sequence', [
            'id' => $this->primaryKey(),
            'parent_id' => $this->integer()->unsigned()->null()->unique(),
            'value' => $this->integer()
        ]);
        $this->addForeignKey(null, 'sequence', ['parent_id'], 'sequence', ['id']);
    }

    public function down()
    {
        return false;
    }
}
