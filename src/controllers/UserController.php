<?php

require_once __DIR__ . '/../models/User.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController {

    public static function register(Request $request, Response $response) {
        $data = $request->getParsedBody();

        // Validaciones mínimas
        if (!isset($data['email']) || !isset($data['password']) || 
            !isset($data['first_name']) || !isset($data['last_name'])) {
            
            $response->getBody()->write(json_encode([
                'error' => 'Faltan datos requeridos'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(400);
        }

        // Hash de la contraseña
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        // Token opcional (ejemplo: para activación)
        $data['token'] = bin2hex(random_bytes(16));
        $data['expired'] = date('Y-m-d H:i:s', strtotime('+1 day'));
        $data['is_admin'] = $data['is_admin'] ?? 0;

        // Crear usuario
        $newUser = User::create($data);

        $response->getBody()->write(json_encode([
            'message' => 'Usuario registrado con éxito',
            'user'    => $newUser
        ]));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(201);
    }


}
