<?php
// Routes

$app->get('/cty/[{num}]', function ($request, $response, $args) {
    $response->withHeader('Content-Type','application/json');

    $this->logger->info("Slim-Skeleton '/cty' route " . var_export($args,true));

    $args['model'] = $this->model;
    if(empty($args['num'])) {
        $args['num'] = 0;
        return $this->renderer->render($response, "level/{$args['num']}.phtml", $args);
    }
    $args['triple'] = $triple = $this->model->getTriple($args['num']);

    if($triple['o']['type'] == 'concept') {
        return $this->renderer->render($response, "level/{$triple['o']['value']}.phtml", $args);
    }
});

$app->get('/foo', function ($request, $response, $args) {
    $response->withHeader('Content-Type','application/json');
    // Render index view
    //sleep(5);
    return $this->renderer->render($response, 'foo.phtml', $args);

});

$app->get('/[{name}]', function ($request, $response, $args) {
    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    $db = $this->db;
    $args['db'] = $db;

    // Render index view
    return $this->renderer->render($response, 'index.phtml', $args);
});

