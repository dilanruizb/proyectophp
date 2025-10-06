<?php


use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
require_once __DIR__ . '/../models/Booking.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
class BookingController {
    public static function reservarCancha(Request $request, Response $response) {
        $auth = AuthMiddleware::handle($request); // Verifica token

        if (isset($auth['error'])) {
            $response->getBody()->write(json_encode(['error' => $auth['error']]));
            return $response->withHeader('Content-Type', 'application/json')
                            ->withStatus($auth['status']);
        }

        $user = $auth['user'];
        $data = $request->getParsedBody();

        // Validar campos
        if (!isset($data['court_id']) || !isset($data['booking_datetime']) || !isset($data['duration_blocks'])) {
            $response->getBody()->write(json_encode(['error' => 'Faltan datos de reserva']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Verificar disponibilidad
        $existing = Booking::getByCourtAndTime($data['court_id'], $data['booking_datetime']);
        if ($existing) {
            $response->getBody()->write(json_encode(['error' => 'La cancha ya está reservada en ese horario']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
        }

        // Crear reserva
        $bookingId = Booking::create([
            'created_by'      => $user['id'],
            'court_id'        => $data['court_id'],
            'booking_datetime'=> $data['booking_datetime'],
            'duration_blocks' => $data['duration_blocks']
        ]);

        $response->getBody()->write(json_encode([
            'message' => 'Reserva creada con éxito',
            'booking_id' => $bookingId
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        }
}
