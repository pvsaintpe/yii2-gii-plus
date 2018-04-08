<?php

namespace app\tests;

use yii\phpunit\TestCase;

class BaseModelTest extends TestCase
{

    /**
     * @return array
     */
    public function modelNameDataProvider()
    {
        return [ // [$modelName]
            ['RootFolderType'],
            ['RootFolder'],
            ['Folder'],
            ['File'],
            ['FileInfoType'],
            ['FileInfo'],
            ['FileReport'],
            ['Sequence'],
            ['Something']
        ];
    }

    /**
     * @param string $modelName
     * @dataProvider modelNameDataProvider
     */
    public function testClassExists($modelName)
    {
        static::assertTrue(class_exists('app\models\base\\' . $modelName . 'Base'));
        static::assertTrue(class_exists('app\models\query\base\\' . $modelName . 'QueryBase'));
        static::assertTrue(class_exists('app\models\\' . $modelName));
        static::assertTrue(class_exists('app\models\query\\' . $modelName . 'Query'));
    }

    /**
     * @param string $modelName
     * @dataProvider modelNameDataProvider
     */
    public function testGetParentClass($modelName)
    {
        static::assertEquals('yii\boost\db\ActiveRecord', get_parent_class('app\models\base\\' . $modelName . 'Base'));
        static::assertEquals('yii\boost\db\ActiveQuery', get_parent_class('app\models\query\base\\' . $modelName . 'QueryBase'));
        static::assertEquals('app\models\base\\' . $modelName . 'Base', get_parent_class('app\models\\' . $modelName));
        static::assertEquals('app\models\query\base\\' . $modelName . 'QueryBase', get_parent_class('app\models\query\\' . $modelName . 'Query'));
    }

    /**
     * @return array
     */
    public function tableNameDataProvider()
    {
        return [ // [$modelName, $tableName]
            ['RootFolderType', 'root_folder_type'],
            ['RootFolder', 'root_folder'],
            ['Folder', 'folder'],
            ['File', 'file'],
            ['FileInfoType', 'file_info_type'],
            ['FileInfo', 'file_info'],
            ['FileReport', 'file_report'],
            ['Sequence', 'sequence'],
            ['Something', 'something']
        ];
    }

    /**
     * @param string $modelName
     * @param string $tableName
     * @dataProvider tableNameDataProvider
     */
    public function testTableName($modelName, $tableName)
    {
        /* @var $modelClass string|\yii\boost\db\ActiveRecord */
        $modelClass = 'app\models\\' . $modelName;
        static::assertEquals($tableName, $modelClass::tableName());
    }

    /**
     * @param string $modelName
     * @dataProvider modelNameDataProvider
     */
    public function testFind($modelName)
    {
        /* @var $modelClass string|\yii\boost\db\ActiveRecord */
        $modelClass = 'app\models\\' . $modelName;
        static::assertEquals('app\models\query\\' . $modelName . 'Query', get_class($modelClass::find()));
    }

    /**
     * @return array
     */
    public function tableIsViewStaticDataProvider()
    {
        return [ // [$modelName, $tableIsView, $tableIsStatic]
            ['RootFolderType', false, true],
            ['RootFolder', false, false],
            ['Folder', false, false],
            ['File', false, false],
            ['FileInfoType', false, true],
            ['FileInfo', false, false],
            ['FileReport', true, false],
            ['Sequence', false, false],
            ['Something', false, false]
        ];
    }

    /**
     * @param string $modelName
     * @param bool $tableIsView
     * @param bool $tableIsStatic
     * @dataProvider tableIsViewStaticDataProvider
     */
    public function testTableIsView($modelName, $tableIsView, $tableIsStatic)
    {
        /* @var $modelClass string|\yii\boost\db\ActiveRecord */
        $modelClass = 'app\models\\' . $modelName;
        static::assertEquals($tableIsView, $modelClass::tableIsView());
    }

    /**
     * @param string $modelName
     * @param bool $tableIsView
     * @param bool $tableIsStatic
     * @dataProvider tableIsViewStaticDataProvider
     */
    public function testTableIsStatic($modelName, $tableIsView, $tableIsStatic)
    {
        /* @var $modelClass string|\yii\boost\db\ActiveRecord */
        $modelClass = 'app\models\\' . $modelName;
        static::assertEquals($tableIsStatic, $modelClass::tableIsStatic());
    }

    /**
     * @return array
     */
    public function allRelationsDataProvider()
    {
        return [ // [$modelName, $allRelations]
            ['RootFolderType', ['rootFolders']],
            ['RootFolder', ['rootFolderType', 'folders', 'files', 'fileReports']],
            ['Folder', ['rootFolder', 'files', 'fileReports']],
            ['File', ['rootFolder', 'folder', 'fileInfo']],
            ['FileInfoType', ['fileInfos']],
            ['FileInfo', ['file', 'fileInfoType']],
            ['FileReport', ['rootFolder', 'folder']],
            ['Sequence', ['parent', 'sequence']],
            ['Something', []]
        ];
    }

    /**
     * @param string $modelName
     * @param string[] $singularRelations
     * @dataProvider allRelationsDataProvider
     */
    public function testAllRelations($modelName, array $allRelations)
    {
        /* @var $modelClass string|\yii\boost\db\ActiveRecord */
        $modelClass = 'app\models\\' . $modelName;
        static::assertCount(count($allRelations), $modelClass::allRelations());
        foreach ($allRelations as $allRelation) {
            static::assertContains($allRelation, array_keys($modelClass::allRelations()));
        }
    }

    /**
     * @return array
     */
    public function singularRelationsDataProvider()
    {
        return [ // [$modelName, $singularRelations]
            ['RootFolderType', []],
            ['RootFolder', ['rootFolderType']],
            ['Folder', ['rootFolder']],
            ['File', ['rootFolder', 'folder', 'fileInfo']],
            ['FileInfoType', []],
            ['FileInfo', ['file', 'fileInfoType']],
            ['FileReport', ['rootFolder', 'folder']],
            ['Sequence', ['parent', 'sequence']],
            ['Something', []]
        ];
    }

    /**
     * @param string $modelName
     * @param string[] $singularRelations
     * @dataProvider singularRelationsDataProvider
     */
    public function testSingularRelations($modelName, array $singularRelations)
    {
        /* @var $modelClass string|\yii\boost\db\ActiveRecord */
        $modelClass = 'app\models\\' . $modelName;
        static::assertCount(count($singularRelations), $modelClass::singularRelations());
        foreach ($singularRelations as $singularRelation) {
            static::assertContains($singularRelation, array_keys($modelClass::singularRelations()));
        }
    }

    /**
     * @return array
     */
    public function pluralRelationsDataProvider()
    {
        return [ // [$modelName, $pluralRelations]
            ['RootFolderType', ['rootFolders']],
            ['RootFolder', ['folders', 'files', 'fileReports']],
            ['Folder', ['files', 'fileReports']],
            ['File', []],
            ['FileInfoType', ['fileInfos']],
            ['FileInfo', []],
            ['FileReport', []],
            ['Sequence', []],
            ['Something', []]
        ];
    }

    /**
     * @param string $modelName
     * @param string[] $pluralRelations
     * @dataProvider pluralRelationsDataProvider
     */
    public function testPluralRelations($modelName, array $pluralRelations)
    {
        /* @var $modelClass string|\yii\boost\db\ActiveRecord */
        $modelClass = 'app\models\\' . $modelName;
        static::assertCount(count($pluralRelations), $modelClass::pluralRelations());
        foreach ($pluralRelations as $pluralRelation) {
            static::assertContains($pluralRelation, array_keys($modelClass::pluralRelations()));
        }
    }

    /**
     * @return array
     */
    public function booleanAttributesDataProvider()
    {
        return [ // [$modelName, $booleanAttributes]
            ['RootFolderType', []],
            ['RootFolder', []],
            ['Folder', ['visible', 'deleted']],
            ['File', ['visible', 'deleted']],
            ['FileInfoType', []],
            ['FileInfo', []],
            ['FileReport', ['visible', 'deleted']],
            ['Sequence', []],
            ['Something', []]
        ];
    }

    /**
     * @param string $modelName
     * @param string[] $booleanAttributes
     * @dataProvider booleanAttributesDataProvider
     */
    public function testBooleanAttributes($modelName, $booleanAttributes)
    {
        /* @var $modelClass string|\yii\boost\db\ActiveRecord */
        $modelClass = 'app\models\\' . $modelName;
        static::assertEquals($booleanAttributes, $modelClass::booleanAttributes());
    }

    /**
     * @return array
     */
    public function dateAttributesDataProvider()
    {
        return [ // [$modelName, $dateAttributes]
            ['RootFolderType', []],
            ['RootFolder', []],
            ['Folder', []],
            ['File', []],
            ['FileInfoType', []],
            ['FileInfo', []],
            ['FileReport', []],
            ['Sequence', []],
            ['Something', ['expires_at', 'second_expires_at']]
        ];
    }

    /**
     * @param string $modelName
     * @param string[] $dateAttributes
     * @dataProvider dateAttributesDataProvider
     */
    public function testDateAttributes($modelName, $dateAttributes)
    {
        /* @var $modelClass string|\yii\boost\db\ActiveRecord */
        $modelClass = 'app\models\\' . $modelName;
        static::assertEquals($dateAttributes, $modelClass::dateAttributes());
    }

    /**
     * @return array
     */
    public function datetimeAttributesDataProvider()
    {
        return [ // [$modelName, $datetimeAttributes]
            ['RootFolderType', []],
            ['RootFolder', []],
            ['Folder', ['created_at', 'updated_at']],
            ['File', ['created_at', 'updated_at']],
            ['FileInfoType', []],
            ['FileInfo', []],
            ['FileReport', ['created_at', 'updated_at']],
            ['Sequence', []],
            ['Something', ['third_expires_at', 'fourth_expires_at']]
        ];
    }

    /**
     * @param string $modelName
     * @param string[] $datetimeAttributes
     * @dataProvider datetimeAttributesDataProvider
     */
    public function testDatetimeAttributes($modelName, $datetimeAttributes)
    {
        /* @var $modelClass string|\yii\boost\db\ActiveRecord */
        $modelClass = 'app\models\\' . $modelName;
        static::assertEquals($datetimeAttributes, $modelClass::datetimeAttributes());
    }

    /**
     * @return array
     */
    public function classShortNameDataProvider()
    {
        return [ // [$modelName, $classShortName]
            ['RootFolderType', 'RootFolderType'],
            ['RootFolder', 'RootFolder'],
            ['Folder', 'Folder'],
            ['File', 'File'],
            ['FileInfoType', 'FileInfoType'],
            ['FileInfo', 'FileInfo'],
            ['FileReport', 'FileReport'],
            ['Sequence', 'Sequence'],
            ['Something', 'Something']
        ];
    }

    /**
     * @param string $modelName
     * @param string $classShortName
     * @dataProvider classShortNameDataProvider
     */
    public function testClassShortName($modelName, $classShortName)
    {
        /* @var $modelClass string|\yii\boost\db\ActiveRecord */
        $modelClass = 'app\models\\' . $modelName;
        static::assertEquals($classShortName, $modelClass::classShortName());
    }

    /**
     * @return array
     */
    public function modelTitleDataProvider()
    {
        return [ // [$modelName, $modelTitle]
            ['RootFolderType', 'Тип корневой папки'],
            ['RootFolder', 'Корневая папка'],
            ['Folder', 'Папка'],
            ['File', 'Файл'],
            ['FileInfoType', 'Тип информации о файле'],
            ['FileInfo', 'Информация о файле'],
            ['FileReport', 'File report'],
            ['Sequence', 'Sequence'],
            ['Something', 'Something']
        ];
    }

    /**
     * @param string $modelName
     * @param string $modelTitle
     * @dataProvider modelTitleDataProvider
     */
    public function testModelTitle($modelName, $modelTitle)
    {
        /* @var $modelClass string|\yii\boost\db\ActiveRecord */
        $modelClass = 'app\models\\' . $modelName;
        static::assertEquals($modelTitle, $modelClass::modelTitle());
    }

    /**
     * @return array
     */
    public function primaryKeyDataProvider()
    {
        return [ // [$modelName, $primaryKey]
            ['RootFolderType', ['id']],
            ['RootFolder', ['id']],
            ['Folder', ['id']],
            ['File', ['id']],
            ['FileInfoType', ['id']],
            ['FileInfo', ['file_id']],
            ['FileReport', ['pk_id', 'pk_root_folder_id']],
            ['Sequence', ['id']],
            ['Something', ['tiny_id', 'small_id']]
        ];
    }

    /**
     * @param string $modelName
     * @param string[] $primaryKey
     * @dataProvider primaryKeyDataProvider
     */
    public function testPrimaryKey($modelName, array $primaryKey)
    {
        /* @var $modelClass string|\yii\boost\db\ActiveRecord */
        $modelClass = 'app\models\\' . $modelName;
        static::assertEquals($primaryKey, $modelClass::primaryKey());
    }

    /**
     * @return array
     */
    public function titleKeyDataProvider()
    {
        return [ // [$modelName, $titleKey]
            ['RootFolderType', ['name']],
            ['RootFolder', ['name']],
            ['Folder', ['root_folder_id', 'name']],
            ['File', ['folder_id', 'name']],
            ['FileInfoType', ['name']],
            ['FileInfo', ['file_id']],
            ['FileReport', ['uk_folder_id', 'uk_name']],
            ['Sequence', ['id']],
            ['Something', ['tiny_id', 'small_id']]
        ];
    }

    /**
     * @param string $modelName
     * @param string[] $titleKey
     * @dataProvider titleKeyDataProvider
     */
    public function testTitleKey($modelName, array $titleKey)
    {
        /* @var $modelClass string|\yii\boost\db\ActiveRecord */
        $modelClass = 'app\models\\' . $modelName;
        static::assertEquals($titleKey, $modelClass::titleKey());
    }

//    /**
//     * @return array
//     */
//    public function getDisplayFieldDataProvider()
//    {
//        return [
//            ['Type', ['name' => 'Type name'], 'Type name'],
//            ['RootFolder', ['type_id' => 1, 'name' => 'Root Folder name'], '1 Root Folder name'],
//            ['Folder', ['root_folder_id' => 2, 'name' => 'Folder name'], '2 Folder name'],
//            ['File', ['folder_id' => 3, 'name' => 'File name'], '3 File name']
//        ];
//    }
//
//    /**
//     * @param string $modelName
//     * @param array $values
//     * @param string $displayField
//     * @dataProvider getDisplayFieldDataProvider
//     */
//    public function testMethodGetDisplayField($modelName, array $values, $displayField)
//    {
//        /* @var $modelClass string|\yii\boost\db\ActiveRecord */
//        $modelClass = 'app\models\\' . $modelName;
//        $reflection = new ReflectionClass($modelClass);
//        static::assertTrue($reflection->hasMethod('getDisplayField'));
//        static::assertNotTrue($reflection->getMethod('getDisplayField')->isStatic());
//        /* @var $model \yii\boost\db\ActiveRecord */
//        $model = new $modelClass;
//        $model->setAttributes($values, false);
//        static::assertEquals($displayField, $model->getDisplayField());
//    }
//
////    public function testMethodGetTypeOfFolder()
////    {
////        $reflection = new ReflectionClass('app\models\Folder');
////        static::assertTrue($reflection->hasMethod('getType'));
////        static::assertFalse($reflection->getMethod('getType')->isStatic());
////        static::assertEquals('app\models\query\TypeQuery', get_class((new \app\models\Folder)->getType()));
////    }
////
////    public function testMethodGetFilesOfFolder()
////    {
////        $reflection = new ReflectionClass('app\models\Folder');
////        static::assertTrue($reflection->hasMethod('getFiles'));
////        static::assertFalse($reflection->getMethod('getFiles')->isStatic());
////        static::assertEquals('app\models\query\FileQuery', get_class((new \app\models\Folder)->getFiles()));
////    }
////
////    public function testMethodGetFolderOfFile()
////    {
////        $reflection = new ReflectionClass('app\models\File');
////        static::assertTrue($reflection->hasMethod('getFolder'));
////        static::assertFalse($reflection->getMethod('getFolder')->isStatic());
////        static::assertEquals('app\models\query\FolderQuery', get_class((new \app\models\File)->getFolder()));
////    }
////
////    public function testMethodGetTypeOfFile()
////    {
////        $reflection = new ReflectionClass('app\models\File');
////        static::assertTrue($reflection->hasMethod('getType'));
////        static::assertFalse($reflection->getMethod('getType')->isStatic());
////        $query = (new \app\models\File)->getType();
////        static::assertEquals('app\models\query\TypeQuery', get_class($query));
////        static::assertInstanceOf('yii\db\ActiveQuery', $query->via);
////        /* @var $viaQuery \yii\db\ActiveQuery */
////        $viaQuery = $query->via;
////        static::assertInternalType('array', $viaQuery->from);
////        static::assertEquals(['folder via_folder'], $viaQuery->from);
////    }
////
////    public function testMethodNewFolderOfType()
////    {
////        $reflection = new ReflectionClass('app\models\Type');
////        static::assertTrue($reflection->hasMethod('newFolder'));
////        static::assertFalse($reflection->getMethod('newFolder')->isStatic());
////        $type = new \app\models\Type;
////        $type->id = mt_rand(1, 10);
////        $folder = $type->newFolder();
////        static::assertEquals($type->id, $folder->type_id);
////    }
////
////    public function testMethodNewFileOfFolder()
////    {
////        $reflection = new ReflectionClass('app\models\Folder');
////        static::assertTrue($reflection->hasMethod('newFile'));
////        static::assertFalse($reflection->getMethod('newFile')->isStatic());
////        $folder = new \app\models\Folder;
////        $folder->id = mt_rand(1, 10);
////        $file = $folder->newFile();
////        static::assertEquals($folder->id, $file->folder_id);
////    }
}
