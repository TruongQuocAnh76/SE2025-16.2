<?php
namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService
{
    public static function sendResetEmail($email, $token)
    {

        // Define socket-related constants if they are missing in the PHP runtime
        if (!defined('SOCKET_EAGAIN')) {
            define('SOCKET_EAGAIN', 11);
        }
        if (!defined('SOCKET_EWOULDBLOCK')) {
            define('SOCKET_EWOULDBLOCK', SOCKET_EAGAIN);
        }
        if (!defined('SOCKET_EINTR')) {
            define('SOCKET_EINTR', 4);
        }

        $host = env('RABBITMQ_HOST', 'localhost');
        $port = env('RABBITMQ_PORT', 5672);
        $user = env('RABBITMQ_USER', 'guest');
        $pass = env('RABBITMQ_PASSWORD', 'guest');
        $queue = env('RABBITMQ_QUEUE', 'reset_email');

        $connection = new AMQPStreamConnection($host, $port, $user, $pass);
        $channel = $connection->channel();
        $channel->queue_declare($queue, false, true, false, false);

        $data = json_encode(['email' => $email, 'token' => $token]);
        $msg = new AMQPMessage($data);
        $channel->basic_publish($msg, '', $queue);

        $channel->close();
        $connection->close();
    }
}
