<?php
//TODO: config
define('DOCS_DIRECTORY', __DIR__.'/data');
define('BUILD_DIRECTORY', __DIR__.'/build');

if (!$loader = include __DIR__.'/vendor/autoload.php') {
    die('You must set up the project dependencies.');
}
$app = new \Cilex\Application('phpdocsToDb');
$app->register(new Cilex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__.'/build/phpdoc.db',
    ),
));
$app->command(new Chrismou\PhpdocsToDb\Command\CreateCommand($app));
$app->run();