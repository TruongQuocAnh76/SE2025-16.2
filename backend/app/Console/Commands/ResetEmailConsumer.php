<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ResetEmailConsumer extends Command
{
    protected $signature = 'rabbitmq:consume-reset-email';
    protected $description = 'Consume reset email messages from RabbitMQ and send email';

    public function handle()
    {
        $host = env('RABBITMQ_HOST', 'rabbitmq');
        $port = env('RABBITMQ_PORT', 5672);
        $user = env('RABBITMQ_USER', 'guest');
        $pass = env('RABBITMQ_PASSWORD', 'guest');
        $queue = env('RABBITMQ_QUEUE', 'reset_email');

        $connection = new AMQPStreamConnection($host, $port, $user, $pass);
        $channel = $connection->channel();
        $channel->queue_declare($queue, false, true, false, false);

        $this->info("Worker started. Listening for messages on queue: $queue");

        $callback = function (AMQPMessage $msg) use ($channel) {
            $data = json_decode($msg->body, true);
            $email = $data['email'] ?? null;
            $token = $data['token'] ?? null;

            if ($email && $token) {
                $frontend = env('FRONTEND_URL');
                $link = ($frontend ? rtrim($frontend, '/') : '') . '/auth/forgot-password?token=' . $token;
                Mail::raw(
                    'Click link để đặt lại mật khẩu: ' . $link,
                    function ($message) use ($email) {
                        $message->to($email)
                                ->subject('Reset your password');
                    }
                );

                $this->info("[x] Sent reset email to $email");
            } else {
                $this->error("Invalid message: " . $msg->body);
            }

            // Acknowledge message
            $channel->basic_ack($msg->get('delivery_tag'));
        };

        $channel->basic_consume($queue, '', false, false, false, false, $callback);

        // CHUẨN PHẢI LÀ CÁI NÀY
        while (count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
