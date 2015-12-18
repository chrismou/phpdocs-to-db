<?php

namespace Chrismou\PhpdocsToDb\Helpers;

class FileProcessor
{

    public function processFile($path)
    {
        $data = [];

        $fileExtension = substr($path, strlen($path)-4, strlen($path));

        if (false !== strpos($path, '/functions/') && $fileExtension == '.xml') {
            $xml = file_get_contents($path);
            $data = simplexml_load_string(str_replace("&", "", $xml));
            if (isset($data->refsect1->methodsynopsis->type)) {

                $method = $data->refsect1->methodsynopsis;

                $params = array();
                $paramStrings = array();
                $paramString = 'void';

                if (isset($method->methodparam) && count($method->methodparam)) {
                    for ($paramCount=0; $paramCount<count($method->methodparam); $paramCount++) {

                        $paramStrings = $this->buildParamString($method, $paramCount);

                        $paramDescription = str_replace("\n", " ", trim(strip_tags((string)$data->refsect1->para->asXml())));
                        $newParamDescription = $paramDescription;

                        while ($paramDescription = $this->stripExtraSpaces($paramDescription)) {
                            $newParamDescription = $paramDescription;
                        }

                        $params[] = array(
                            'parameter' => $method->methodparam[$paramCount]->parameter,
                            'type' => $method->methodparam[$paramCount]->type,
                            'description' => ($newParamDescription) ? $newParamDescription : null,
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

                $data = array(
                    'name' => (string)$method->methodname,
                    'type' => (string)$method->type,
                    'params' => $params,
                    'parameterString' => $paramString,
                    'description' => $fixedDescription
                );
            }
        }

        return $data;
    }

    protected function buildParamString($method, $paramCount)
    {
        return sprintf('%s%s %s%s%s',
            (isset($method->methodparam[$paramCount]['choice'])) ? '[ ' : '',
            $method->methodparam[$paramCount]->type,
            $method->methodparam[$paramCount]->parameter,
            isset($method->methodparam[$paramCount]->initializer) ? sprintf(' = %s', $method->methodparam[$paramCount]->initializer) : '',
            (isset($method->methodparam[$paramCount]['choice'])) ? ' ]' : ''
        );
    }

    protected function stripExtraSpaces($string)
    {
        $changed = false;
        if (strpos($string, '  ')) {
            $changed = str_replace('  ', ' ', $string);
        }
        return $changed;
    }
}