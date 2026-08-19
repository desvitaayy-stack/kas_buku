<?php

namespace App\Helpers;

use App\Models\User;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    public static function broadcast(string $title, string $body, ?int $actorUserId = null): void
{
    try {
        $messaging = app(Messaging::class);

        $actorToken = null;

        // 1. Kirim ke pelaku dengan pesan "Kamu"
        if ($actorUserId !== null) {
            $actor = User::where('id', $actorUserId)
                         ->whereNotNull('fcm_token')
                         ->where('fcm_token', '!=', '')
                         ->first();

            if ($actor) {
                $actorToken = $actor->fcm_token;
                $bodyActor  = str_replace($actor->name, 'Kamu', $body);

                $message = CloudMessage::new()
                    ->withNotification(Notification::create($title, $bodyActor))
                    ->withAndroidConfig(['priority' => 'high']);

                $messaging->sendMulticast($message, [$actorToken]);
                Log::info('FCM Actor terkirim', ['actor' => $actor->name]);
            }
        }

        // 2. Kirim ke user lain — skip token yang sama dengan actor
        $query = User::whereNotNull('fcm_token')
                     ->where('fcm_token', '!=', '');

        if ($actorUserId !== null) {
            $query->where('id', '!=', $actorUserId);
        }

        $tokens = $query->pluck('fcm_token')
                        ->unique()        // ← hapus token duplikat
                        ->filter(fn($t) => $t !== $actorToken) // ← skip kalau sama dengan actor
                        ->values()
                        ->toArray();

        Log::info('FCM Others', ['jumlah_penerima' => count($tokens)]);

        if (!empty($tokens)) {
            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withAndroidConfig(['priority' => 'high']);

            foreach (array_chunk($tokens, 500) as $chunk) {
                $messaging->sendMulticast($message, $chunk);
            }
            Log::info('FCM Others terkirim');
        }

    } catch (\Exception $e) {
        Log::error('FCM Error: ' . $e->getMessage());
        Log::error($e->getTraceAsString());
    }
}
}