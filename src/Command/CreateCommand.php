<?php
/**
 * App for generating a PHPdocs sqlite DB using XML from the PHP SVN repo
 *
 * @link https://github.com/chrismou/phergie-irc-plugin-react-php for the canonical source repository
 * @copyright Copyright (c) 2014 Chris Chrisostomou (http://mou.me)
 * @package Chrismou\PhpdocsToSqlite
 */

namespace Chrismou\PhpdocsToSqlite\Command;

use Cilex\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * Creation command
 *
 * @category Chrismou
 * @package Chrismou\PhpdocsToSqlite\Command
 */
class CreateCommand extends BaseCommand
{
    protected $done = array();

    function __construct(Application $app) {
        $this->app = $app;
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
        //$output->getFormatter()->setStyle('notImplemented', new OutputFormatterStyle('red'));
        //$output->getFormatter()->setStyle('doing', new OutputFormatterStyle('yellow'));

        $output->getFormatter()->setStyle('type', new OutputFormatterStyle('green'));
        $output->getFormatter()->setStyle('function', new OutputFormatterStyle('cyan'));
        $output->getFormatter()->setStyle('methodname', new OutputFormatterStyle('cyan'));
        $output->getFormatter()->setStyle('parameter', new OutputFormatterStyle('cyan'));
        $output->getFormatter()->setStyle('initializer', new OutputFormatterStyle('red'));

        $output->writeln('Grabbing latest docs');
        $output->writeln('<doing>*grabs latest docs*</doing>');

        $output->writeln('Selecting the language');
        $output->writeln('<notImplemented>Not yet implemented - skipping</notImplemented>');

        $output->writeln('Traversing the docs');
        //$output->writeln('<notImplemented>Not yet implemented - skipping</notImplemented>');

        //TODO: db creation
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
            if (false !== strpos($path, '.svn')) continue;

            //$output->writeln($path);
            if ($dir->isDir()) {
                $this->scanDirectory($path, $output);
            } else {
                $this->processFile($path, $output);
            }
        }
    }

    protected function processFile($path, OutputInterface $output)
    {
        if (false !== strpos($path, '/functions/') && substr($path, strlen($path)-4, strlen($path))=='.xml') {
            $xml = file_get_contents($path);
            $data = simplexml_load_string(str_replace("&", "", $xml));
            if (isset($data->refsect1->methodsynopsis->type)) {

                $method = $data->refsect1->methodsynopsis;

                if (isset($this->done[(string)$method->methodname])) return;

                $params = array();
                $paramStrings = array();
                $paramString = 'void';

                if (isset($method->methodparam) && count($method->methodparam)) {
                    for ($paramCount=0; $paramCount<count($method->methodparam); $paramCount++) {
                        //$paramStrings[] = sprintf('%s<type>%s</type> <parameter>%s</parameter>%s%s',
                        $paramStrings[] = sprintf('%s%s %s%s%s',
                            (isset($method->methodparam[$paramCount]['choice'])) ? '[ ' : '',
                            $method->methodparam[$paramCount]->type,
                            $method->methodparam[$paramCount]->parameter,
                            //isset($method->methodparam[$paramCount]->initializer) ? sprintf('<initializer> = %s</initializer>', $method->methodparam[$paramCount]->initializer) : '',
                            isset($method->methodparam[$paramCount]->initializer) ? sprintf(' = %s', $method->methodparam[$paramCount]->initializer) : '',
                            (isset($method->methodparam[$paramCount]['choice'])) ? ' ]' : ''
                        );

                        $paramDescription = str_replace("\n", " ", trim(strip_tags((string)$data->refsect1->para->asXml())));
                        $fixedParamDescription = $paramDescription;

                        while ($paramDescription=$this->stripExtraSpaces($paramDescription)) {
                            $fixedParamDescription = $paramDescription;
                        }

                        $params[] = array(
                            'parameter' => $method->methodparam[$paramCount]->parameter,
                            'type' => $method->methodparam[$paramCount]->type,
                            'description' => ($fixedParamDescription) ? $fixedParamDescription : null,
                            'initializer' => isset($method->methodparam[$paramCount]->initializer) ? $method->methodparam[$paramCount]->initializer : null,
                            'optional' => ($method->methodparam[$paramCount]['choice'] && $method->methodparam[$paramCount]['choice']=='opt') ? 1 : 0
                        );
                    }
                }

                if (count($paramStrings)) {
                    $paramString = implode(', ', $paramStrings);
                }

                $description = str_replace("\n", " ", trim(strip_tags((string)$data->refsect1->para->asXml())));
                $fixedDescription = $description;

                while ($description=$this->stripExtraSpaces($description)) {
                    $fixedDescription = $description;
                }


                $output->writeln(sprintf(
                    '<type>%s</type> <methodname>%s</methodname> ( %s )',
                    $method->type,
                    $method->methodname,
                    $paramString
                ));
                $output->writeln((string) $fixedDescription);

                $data = array(
                    'name' => (string)$method->methodname,
                    'type' => (string)$method->type,
                    'parameterString' => $paramString,
                    'description' => $fixedDescription
                );
                $this->app['db']->insert('function', $data);
                $functionId = $this->app['db']->lastInsertId();

                foreach ($params as $param) {
                    $param['functionId'] = $functionId;
                    $this->app['db']->insert('parameter', $param);
                }

                //TODO: wut
                $this->done[(string)$method->methodname] = true;

            }
        }
    }

    protected function stripExtraSpaces($string)
    {
        $changed = false;
        if (strpos($string, '  ')) {
            $changed = str_replace('  ', ' ', $string);
        }
        return $changed;
    }

    //TODO: auto db setup
    //TODO: option to switch between full DB (w/ fks) or basic (single table)
   /* protected function setupDb() {
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
    }*/
}