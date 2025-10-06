<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// Importar controlador y modelo
require __DIR__ . '/../src/controllers/UserController.php';
require __DIR__ . '/../src/models/DB.php';

require_once __DIR__ . '/../src/controllers/CourtController.php';
$courtController = new \CourtController();

$app->get('/api/courts', fn($req, $res) => $courtController::getAll($req, $res));
$app->post('/api/courts', fn($req, $res) => $courtController::create($req, $res));
$app->put('/api/courts/{id}', fn($req, $res, $args) => $courtController::update($req, $res, $args));
$app->delete('/api/courts/{id}', fn($req, $res, $args) => $courtController::delete($req, $res, $args));


$userController = new \UserController();

$app = AppFactory::create();

// Middleware básicos
$app->addRoutingMiddleware();
$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

// Middleware para CORS y JSON por defecto (solo para rutas API)
$app->add(function (Request $request, $handler) {
    $response = $handler->handle($request);

    $path = $request->getUri()->getPath();
    if (str_starts_with($path, '/api')) {
        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'OPTIONS, GET, POST, PUT, PATCH, DELETE')
            ->withHeader('Content-Type', 'application/json');
    }

    return $response;
});

/* ===========================
   RUTAS HTML
   =========================== */

// Página principal
$app->get('/', function (Request $request, Response $response) {
    $path = __DIR__ . '/index.html';

    if (!file_exists($path)) {
        $response->getBody()->write("Archivo no encontrado en: $path");
        return $response->withStatus(500);
    }

    $html = file_get_contents($path);
    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html');
});


// Formulario login
$app->get('/login', function (Request $request, Response $response) {
    $html = file_get_contents(__DIR__ . '/login.html');
    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html');
});


// Formulario registro
$app->get('/register', function (Request $request, Response $response) {
    $html = file_get_contents(__DIR__ . '/register.html');
    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html');
});

// Procesar registro
$app->post('/register', function (Request $request, Response $response) use ($userController) {
    return $userController->register($request, $response);
});

// Procesar login
$app->post('/login', function (Request $request, Response $response) use ($userController) {
    return $userController->login($request, $response);
});


//proteyer rutas

// Crear cancha (solo admin)
$app->post('/api/courts', function (Request $request, Response $response) use ($courtController) {
    $authHeader = $request->getHeaderLine('Authorization');
    $token = str_replace('Bearer ', '', $authHeader);

    $user = \UserController::validateToken($token, true);
    if (!$user) {
        $response->getBody()->write(json_encode(['error' => 'Acceso denegado']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
    }

    return $courtController::create($request, $response);
});

$app->get('/crear_canchas', function (Request $request, Response $response) {
    $html = file_get_contents(__DIR__ . '/crear_canchas.html');
    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html');
});


/* ===========================
   RUTAS API
   =========================== */

$app->post('/logout', function (Request $request, Response $response) {
    $authHeader = $request->getHeaderLine('Authorization');
    $token = str_replace('Bearer ', '', $authHeader);

    $pdo = new PDO("mysql:host=localhost;dbname=seminariophp;charset=utf8", "root", "");
    $stmt = $pdo->prepare("UPDATE users SET token = NULL, expired = NULL WHERE token = :token");
    $stmt->execute([':token' => $token]);

    $response->getBody()->write(json_encode(['message' => 'Sesión cerrada']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

// GET todos los usuarios
$app->get('/api/users', function (Request $request, Response $response) {
    $db = DB::getConnection();
    $stmt = $db->query("SELECT * FROM usuario");
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

// POST nuevo usuario
$app->post('/api/users', function (Request $request, Response $response) {
    try {
        $db = DB::getConnection();
        $data = $request->getParsedBody();

        $stmt = $db->prepare("INSERT INTO usuario (nombre, usuario, password) VALUES (:nombre, :usuario, :password)");
        $success = $stmt->execute([
            ':nombre' => $data['nombre'] ?? '',
            ':usuario' => $data['usuario'] ?? '',
            ':password' => $data['password'] ?? ''
        ]);

        if ($success) {
            $response->getBody()->write(json_encode(['status' => 'User created']));
        } else {
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode(['error' => 'User could not be created']));
        }

    } catch (PDOException $e) {
        $response = $response->withStatus(500);
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// PUT actualizar usuario
$app->put('/api/users/{id}', function (Request $request, Response $response, array $args) {
    try {
        $db = DB::getConnection();
        $id = $args['id'];
        $data = $request->getParsedBody();

        $stmt = $db->prepare("UPDATE usuario SET nombre = :nombre, usuario = :usuario, password = :password WHERE id = :id");
        $stmt->execute([
            ':id' => $id,
            ':nombre' => $data['nombre'] ?? '',
            ':usuario' => $data['usuario'] ?? '',
            ':password' => $data['password'] ?? ''
        ]);

        if ($stmt->rowCount() > 0) {
            $response->getBody()->write(json_encode(['status' => 'User updated']));
        } else {
            $response = $response->withStatus(404);
            $response->getBody()->write(json_encode(['error' => 'User not found or no changes made']));
        }

    } catch (PDOException $e) {
        $response = $response->withStatus(500);
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// DELETE usuario
$app->delete('/api/users/{id}', function (Request $request, Response $response, array $args) {
    try {
        $db = DB::getConnection();
        $id = $args['id'];

        $stmt = $db->prepare("DELETE FROM usuario WHERE id = :id");
        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() > 0) {
            $response->getBody()->write(json_encode(['status' => 'User deleted']));
        } else {
            $response = $response->withStatus(404);
            $response->getBody()->write(json_encode(['error' => 'User not found']));
        }

    } catch (PDOException $e) {
        $response = $response->withStatus(500);
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

$app->run();
