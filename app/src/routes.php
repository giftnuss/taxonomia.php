<?php
// Routes

$app->get('/', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Run '/' route for "  . $request->getRequestTarget());
    $this->logger->info("Run '/' route for "  . $request->getUri()->getBaseUrl());
      // var_export($request,true));

    $db = $this->db;
    $args['db'] = $db;

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/cty/[{num}]', function ($request, $response, $args) {
    $response = $response->withHeader('Content-Type','application/json');

    $this->logger->info("Json '/cty' route " . (isset($args['num'])?$args['num']:''));

    $args['model'] = $this->model;
    if(empty($args['num'])) {
        $args['num'] = 0;
        return $this->renderer->render($response, "level/{$args['num']}.phtml", $args);
    }
    $args['triple'] = $triple = $this->model->getTriple($args['num']);

    if($triple['o']['type'] == 'concept') {
        return $this->renderer->render($response, "level/{$triple['o']['value']}.phtml", $args);
    }
    elseif($triple['o']['type'] == 'uri') { // uri contains uri
        return $this->renderer->render($response, "level/uri.phtml", $args);
    }
});

$app->get('/document/{num}', function ($request, $response, $args) {

    $args['triple'] = $triple = $this->model->getTriple($args['num']);
    $shelf = $this->shelf;

    $newStream = $shelf->getStream($triple['s']['value']);
    $response = $response
        ->withHeader('Content-Type', 'application/pdf')
        ->withHeader("Content-Disposition","inline; filename=downloaded.pdf")
        ->withHeader("Content-Length",$shelf->getFilesize($triple['s']['value']))
        ->withBody($newStream);

    return $response;
});

$app->get('/view/{type:[a-z]+}/{arg:.*}', \Taxonomia\Controller\Viewer::class);

$app->get('/view/view.js', function ($request,$response,$args) {
    $args = $request->getQueryParams();
    return $this->renderer->render($response, "viewer/view.js", $args);
});
