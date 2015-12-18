<?php

namespace Chrismou\PhpdocsToDb\Helpers;

class CreateDb
{
    // TODO
    public function sql()
    {
        $functions = "CREATE TABLE `function` (`id`	INTEGER PRIMARY KEY AUTOINCREMENT, `name` TEXT UNIQUE, `type` TEXT, `description` TEXT";
        $params = "CREATE TABLE `parameter` (`id` INTEGER PRIMARY KEY AUTOINCREMENT, `parameter` TEXT NOT NULL, `type` TEXT NOT NULL, `initializer`	TEXT, `functionId` INTEGER NOT NULL";

        $schema = new \Doctrine\DBAL\Schema\Schema();
        $functions = $schema->createTable('function');
        $functions->addColumn('id', 'integer', array('autoincrement'=>true));
        $functions->addColumn('name', 'text');
        $functions->addColumn('type', 'text');
        $functions->addColumn('description', 'text');
        $functions->setPrimaryKey(array('id'));
        $functions->addUniqueIndex(array('name'));
        $schema->createSequence('function_seq');

        $parameter = $schema->createTable('parameter');
        $parameter->addColumn('id', 'integer', array('autoincrement'=>true));
        $parameter->addColumn('parameter', 'text');
        $parameter->addColumn('type', 'text');
        $parameter->addColumn('initializer', 'text');
        $parameter->setPrimaryKey(array('id'));
        $schema->createSequence('parameter_seq');

        $sql = $schema->toSql(new \Doctrine\DBAL\Platforms\SqlitePlatform);

        var_dump($sql);
        die;
    }
}