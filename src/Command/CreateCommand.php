<?php
/**
 * App for generating an sqlite PHP function database using the PHP documentation SVN repository
 *
 * @link https://github.com/chrismou/phpdocs-to-db for the canonical source repository
 * @copyright Copyright (c) 2014 Chris Chrisostomou (http://mou.me)
 * @package Chrismou\PhpdocsToDb
 */

namespace Chrismou\PhpdocsToDb\Command;

use Cilex\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

use Chrismou\PhpdocsToDb\Helpers\FileProcessor;

/**
 * Creation command
 *
 * @category Chrismou
 * @package Chrismou\PhpdocsToDb\Command
 */
class CreateCommand extends BaseCommand
{
    /** @var array */
    protected $done = [];

    /** @var Application */
    protected $app;

    /** @var \Chrismou\PhpdocsToDb\Helpers\FileProcessor */
    protected $fileProcessor;

    function __construct(Application $app) {
        $this->app = $app;
        $this->fileProcessor = new FileProcessor();

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('phpdocdb:create')
            ->setDescription('Start the conversion process');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //$this->setupDb();

        // Set the colours
        $output->getFormatter()->setStyle('notImplemented', new OutputFormatterStyle('red'));
        $output->getFormatter()->setStyle('doing', new OutputFormatterStyle('yellow'));

        $output->getFormatter()->setStyle('type', new OutputFormatterStyle('green'));
        $output->getFormatter()->setStyle('function', new OutputFormatterStyle('cyan'));
        $output->getFormatter()->setStyle('methodname', new OutputFormatterStyle('cyan'));
        $output->getFormatter()->setStyle('parameter', new OutputFormatterStyle('cyan'));
        $output->getFormatter()->setStyle('initializer', new OutputFormatterStyle('red'));

        $output->writeln('Grabbing latest docs');
        $output->writeln('<notImplemented>Not yet implemented - skipping</notImplemented>');

        $output->writeln('Selecting the language');
        $output->writeln('<notImplemented>Not yet implemented - skipping</notImplemented>');

        $output->writeln('Traversing the docs');
        //$output->writeln('<notImplemented>Not yet implemented - skipping</notImplemented>');

        //TODO: db creation & lite db support
        //sqlite_open(BUILD_DIRECTORY.'/')
        $existingfunctions = $this->app['db']->query("DELETE FROM function");
        $existingparams = $this->app['db']->query("DELETE FROM parameter");

        $this->scanDirectory(DOCS_DIRECTORY, $output);

    }

    protected function scanDirectory($baseDir, OutputInterface $output)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($baseDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );

        $paths = array($baseDir);
        foreach ($iterator as $path => $dir) {
            if (false !== strpos($path, '.svn')) {
                continue;
            }

            //$output->writeln($path);
            if ($dir->isDir()) {
                $this->scanDirectory($path, $output);
            } else {
                $data = $this->fileProcessor->processFile($path);

                if (count($data) && !isset($this->done[$data['name']])) {
                    // Grab out the array of parameters and unset it from the main data array before saving
                    $params = $data['params'];
                    unset($data['params']);

                    $this->app['db']->insert('function', $data);
                    $functionId = $this->app['db']->lastInsertId();

                    foreach ($params as $param) {
                        $param['functionId'] = $functionId;
                        $this->app['db']->insert('parameter', $param);
                    }

                    $this->done[(string)$data['name']] = true;

                    $output->writeln(sprintf(
                        '<type>%s</type> <methodname>%s</methodname> ( %s )',
                        $data['type'],
                        $data['name'],
                        $data['parameterDescription']
                    ));
                }
            }
        }
    }
}