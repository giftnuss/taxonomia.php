<?php

$debug =! true;

$autoload = require __DIR__ . '/../vendor/autoload.php';
$baseDir = dirname(__DIR__);
$autoload->add('Siox\\', array($baseDir.'/src'));

$tempBase = "/dev/shm/temp";
if(!is_dir($tempBase)) {
    mkdir($tempBase);
}

$app = require __DIR__ . "/../app/src/app.php";
$c = $app->getContainer();

$db = $c->get('db');
$model = $c->get('model');
$shelf = $c->get('shelf');
$shelf->addSkipDocumentExtension(array('jp2'));

$model->lockCreation();
$topic['document']    = $model->concept('document');
$topic['is a']        = $model->concept('is a');
$topic['in language'] = $model->concept('in language');
$topic['contains']    = $model->concept('contains');
$topic['german']      = $model->entity('german');
$topic['english']     = $model->entity('english');
$model->unlockCreation();

$shelf->collectDocuments(function ($shelf,$entry)
    use($db,$model,$debug,$topic,$tempBase) {
    $db->beginTransaction();

    $uri = $model->uri($shelf->makeUri($entry));
    $parent = $model->uri($shelf->makeParentUri($entry)); echo "$uri $parent\n";
    $model->triple($uri,$topic['is a'],$topic['document']);
    $model->triple($parent,$topic['contains'],$uri);

    if(isset($entry['extension'])) {
        if(strtolower($entry['extension']) === 'pdf') {
            $fs = $shelf->getFilesystem();
            $path = $entry['path'];
            $indexer = new Taxonomia\Indexer\Pdf($fs,$path);
            $indexer->setDebug($debug);
            $indexer->setTextConverter('pdftotext');
            $indexer->setTempbase($tempBase);

            $found = 0;
            $model->searchTriples(['s' => $uri, 'p' => $topic['in language']],
                function ($row) use (&$found) { ++$found; });
            if(!$found) {
                $languages = $indexer->detectLanguages();
                if(isset($languages['de'])) {
                    $model->triple($uri,$topic['in language'],$topic['german']);
                }
                if(isset($languages['en'])) {
                    $model->triple($uri,$topic['in language'],$topic['english']);
                }
            }
        }
    }
    $db->commit();
});
