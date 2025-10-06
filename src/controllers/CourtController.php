<?php

require_once __DIR__ . '/../models/Court.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CourtController {
    public static function getAll(Request $request, Response $response) {
        $courts = Court::getAll();
        $response->getBody()->write(json_encode($courts));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function create(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $newCourt = Court::create($data);
        $response->getBody()->write(json_encode($newCourt));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    }

    public static function update(Request $request, Response $response, array $args) {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $updated = Court::update($id, $data);
        $response->getBody()->write(json_encode($updated));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function delete(Request $request, Response $response, array $args) {
        $id = $args['id'];
        $success = Court::delete($id);
        $response->getBody()->write(json_encode(['deleted' => $success]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}