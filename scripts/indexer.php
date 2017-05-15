<?php

$debug =! true;

$autoload = require __DIR__ . '/../vendor/autoload.php';
$baseDir = dirname(__DIR__);
$autoload->add('Siox\\', array($baseDir.'/src'));

$tempBase = "$baseDir/temp";
if(!is_dir($tempBase)) {
    mkdir($tempBase);
}

$app = require __DIR__ . "/../app/src/app.php";
$c = $app->getContainer();

$db = $c->get('db');
$model = $c->get('model');
$shelf = $c->get('shelf');
$shelf->addSkipDocumentExtension(array('jp2'));

$docconcept = $model->concept('document');
$isa = $model->concept('is a');
$language = $model->concept('in language');
$german = $model->concept('german');
$english = $model->concept('english');

$shelf->collectDocuments(function ($shelf,$entry)
    use($model,$docconcept,$isa,$debug,$language,$german,$english) {

    $uri = $model->uri($shelf->makeUri($entry)); echo "$uri\n";
    $model->triple($uri,$isa,$docconcept);

    if(isset($entry['extension'])) {
        if(strtolower($entry['extension']) === 'pdf') {
            $fs = $shelf->getFilesystem();
            $path = $entry['path'];
            $indexer = new Taxonomia\Indexer\Pdf($fs,$path);
            $indexer->setDebug($debug);

            $found = 0;
            $model->searchTriples(['s' => $uri, 'p' => $language],
                function ($row) use (&$found) { ++$found; });
            if(!$found) {
                $languages = $indexer->detectLanguages();
                if(isset($languages['de'])) {
                    $model->triple($uri,$language,$german);
                }
                if(isset($languages['en'])) {
                    $model->triple($uri,$language,$english);
                }
            }
        }
    }
});
