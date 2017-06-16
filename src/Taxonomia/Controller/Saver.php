<?php

namespace Taxonomia\Controller;

use Interop\Container\ContainerInterface;
use League\Flysystem\Util\MimeType;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

use GuzzleHttp\Psr7\Stream;

use Taxonomia\Analyse\Wordcloud;

class Saver
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

   public function __invoke($request, $response, $args)
   {
       $this->logger->info("Saver Controller: " . var_export($args,true));
       $this->logger->info("Run 'Saver' for route "  . $request->getRequestTarget());
       if(is_numeric($args['arg'])) {
           if($args['type'] == 'text') {
               $args['text'] = $request->getParam('text',null);
               return $this->saveText($response,$args);
           }
       }

       throw new \Exception("Route undefined.");
   }

   protected function saveText($response,$args)
   {
       $model = $this->container->get('model');
       $storage = $this->container->get('text_storage');

       $args['triple'] = $triple = $model->getTriple($args['arg']);
       $model->searchTriples(['s' => $triple['s']['id'],'p' => ['concept' => 'sha1 digest']],
           function ($row) use ($model,&$args) {
               $args['hash'] = $model->getSha1($row['o']);
           });

       if(!$storage->has($args['hash'])) {
           throw new \Exception("Unknown document.");
       }
       if(!isset($args['text'])) {
           throw new \Exception("Text?");
       }
       $storage->store($args['hash'],$args['text']);

       return $response->withJSON(['status' => 'ok']);
   }

}
