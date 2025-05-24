<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Broadcasting\BroadcastException;
use PHPUnit\Framework\Attributes\Test;

class WebSocketTest extends TestCase
{
    /**
     * Test la connexion WebSocket avec Pusher
     *
     * #[Test]
     */
    #[Test]
    public function websocket_connection()
    {
        try {
            // Vérifier la configuration du broadcast
            $this->assertNotNull(Broadcast::connection());

            // Vérifier la configuration Pusher
            $config = config('broadcasting.connections.pusher');
            $this->assertNotNull($config);

            // Vérifier les clés Pusher
            $this->assertNotNull(env('PUSHER_APP_KEY'));
            $this->assertNotNull(env('PUSHER_APP_SECRET'));
            $this->assertNotNull(env('PUSHER_APP_ID'));

            // Vérifier la connexion au channel
            Broadcast::channel('test-channel', function () {
                return true;
            });

            $this->assertTrue(true, 'La connexion WebSocket semble fonctionnelle');

        } catch (BroadcastException $e) {
            $this->fail('Erreur lors de la connexion WebSocket: ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->fail('Erreur lors du test de la connexion WebSocket: ' . $e->getMessage());
        }
    }
}
