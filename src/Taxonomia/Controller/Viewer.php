<?php

namespace Taxonomia\Controller;

use Interop\Container\ContainerInterface;
use League\Flysystem\Util\MimeType;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

use GuzzleHttp\Psr7\Stream;

use Taxonomia\Analyse\Wordcloud;

class Viewer
{
   protected $container;

   protected $logger;

   protected $settings;

   protected $fs;

   public function __construct(ContainerInterface $container)
   {
       $this->container = $container;
       $this->logger = $container->get('logger');

       $settings = $container->get("settings");
       $this->settings = $settings['controller']['viewer'];
   }

   public function getFs()
   {
       if($this->fs === null) {
           $root = $this->settings['template_path'];
           $local = new Local($root, LOCK_EX, Local::SKIP_LINKS);
           $this->fs = new Filesystem($local);
       }
       return $this->fs;
   }

   public function __invoke($request, $response, $args)
   {
       $this->logger->info("Viewer Controller: " . var_export($args,true));
       $this->logger->info("Run 'Viewer' for route "  . $request->getRequestTarget());
       if(is_numeric($args['arg'])) {
           if($args['type'] == 'text') {
               return $this->viewText($response,$args);
           }
           elseif($args['type'] == 'markitup') {
                $renderer = $this->container->get('renderer');
                return $renderer->render($response, "viewer/markitup/markitup.phtml", $args);
           }
           elseif($args['type'] == 'cloud') {
               return $this->viewCloud($response,$args);
           }
           else {
               $renderer = $this->container->get('renderer');
               return $renderer->render($response, "viewer/pdf/pdf.phtml", $args);
           }
       }

       $file = $args['type'] . '/' . $args['arg'];
       $mimetype = MimeType::detectByFilename($file);
       $filesize = $this->getFs()->getSize($file);
       $source = $this->getFs()->readStream($file);
       $stream = new Stream($source,['size' => $filesize]);

       $response = $response
          ->withHeader('Content-Type', $mimetype)
          ->withHeader("Content-Length",$filesize)
          ->withBody($stream);
       return $response;
   }

   protected function viewText($response,$args)
   {
       $model = $this->container->get('model');
       $storage = $this->container->get('text_storage');

       $args['triple'] = $triple = $model->getTriple($args['arg']);
       $model->searchTriples(['s' => $triple['s']['id'],'p' => ['concept' => 'sha1 digest']],
           function ($row) use ($model,&$args) {
               $args['hash'] = $model->getSha1($row['o']);
           });

       if(!$storage->has($args['hash'])) {
           $shelf = $this->container->get('shelf');
           $fs = $shelf->getFilesystem();
           $path = $shelf->getPath($triple['s']['value']);
           $indexer = new \Taxonomia\Indexer\Pdf($fs,$path);
           $indexer->setTextConverter('pdf2txt');
           $indexer->setTempbase('/dev/shm');
           $indexer->setDebug(true);
           $storage->store($args['hash'],$indexer->extractText());
       }
       $text = $storage->retrieve($args['hash']);
       $response = $response
           ->withHeader('Content-Type', 'text/plain')
           ->withHeader('Content-Length', strlen($text));
       $response->getBody()->write($text);
       return $response;
   }

   protected function viewCloud($response,$args)
   {
       $model = $this->container->get('model');
       $args['triple'] = $triple = $model->getTriple($args['arg']);
       $model->searchTriples(['s' => $triple['s']['id'],'p' => ['concept' => 'in language']],
           function ($row) use ($model,&$args) {
               $args['language'] = $model->getEntity($row['o']);
           });

       $textresponse = $this->viewText($response,$args);
       $cloudbuilder = (new Wordcloud())->setText($textresponse->getBody());
       $cloudbuilder->setLanguage($args['language']);
       $cloud = $cloudbuilder->getCloud();
       $response = $response->withJson($cloud);
       return $response;
   }
}
