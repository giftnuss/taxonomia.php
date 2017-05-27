<?php

namespace Taxonomia\Controller;

use Interop\Container\ContainerInterface;
use League\Flysystem\Util\MimeType;

use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

use GuzzleHttp\Psr7\Stream;

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
           $renderer = $this->container->get('renderer');
           return $renderer->render($response, "viewer/pdf/pdf.phtml", $args);
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
}
