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

    public static function login(Request $request, Response $response)
    {
        $data = $request->getParsedBody();

        // Validaciones mínimas
        if (!isset($data['email']) || !isset($data['password'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Faltan credenciales (email y password)'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(400);
        }

        // Buscar usuario por email
        $user = User::findByEmail($data['email']);

        if (!$user) {
            $response->getBody()->write(json_encode([
                'error' => 'Usuario no encontrado'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(404);
        }

        // Verificar contraseña
        if (!password_verify($data['password'], $user['password'])) {
            $response->getBody()->write(json_encode([
                'error' => 'Contraseña incorrecta'
            ]));
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus(401);
        }
        // Si la contraseña es correcta, retorna el usuario
        $response->getBody()->write(json_encode([
            'message' => 'Login exitoso',
            'user'    => $user
        ]));
        // Generar nuevo token y expiración
        $newToken = bin2hex(random_bytes(16));
        $newExpiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        // Guardar token y expiración en la base de datos
        $pdo = new PDO("mysql:host=localhost;dbname=seminariophp;charset=utf8", "root", "");
        $stmt = $pdo->prepare("UPDATE users SET token = :token, expired = :expired WHERE id = :id");
        $stmt->execute([
            ':token'   => $newToken,
            ':expired' => $newExpiry,
            ':id'      => $user['id']
        ]);

        $response->getBody()->write(json_encode([
            'token' => $newToken
        ]));
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus(200);
    }

    public static function validateToken($token, $requireAdmin = false) {
        $pdo = new PDO("mysql:host=localhost;dbname=seminariophp;charset=utf8", "root", "");
        $stmt = $pdo->prepare("SELECT * FROM users WHERE token = :token");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return null;

        if (strtotime($user['expired']) < time()) return null;

        if ($requireAdmin && $user['is_admin'] != 1) return null;

        // Actualizar expiración
        $newExpiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));
        $update = $pdo->prepare("UPDATE users SET expired = :expired WHERE id = :id");
        $update->execute([
            ':expired' => $newExpiry,
            ':id'      => $user['id']
        ]);
    return $user;
}

}
