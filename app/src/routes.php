<?php
// Routes

$app->get('/level/[{num}]', function ($request, $response, $args) {
    $response->withHeader('Content-Type','application/json');

    $this->logger->info("Slim-Skeleton '/' route " . var_export($args,true));
    if(empty($args['num'])) {
        $args['num'] = 0;
    }

    $args['model'] = $this->model;
    $this->logger->info("Slim-Skeleton '/' route " . var_export($model,true));

    // Render index view
    #sleep(5);
    return $this->renderer->render($response, "level/{$args['num']}.phtml", $args);

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

