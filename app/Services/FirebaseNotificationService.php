<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserService;
use Google\Client as GoogleClient;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected string $projectId;

    protected string $credentialsPath;

    public function __construct()
    {
        $this->projectId = trim((string) config('services.fcm.project_id'));
        $this->credentialsPath = $this->resolveCredentialsPath(
            (string) config('services.fcm.credentials')
        );
    }

    public function sendToUser(User|int $user, string $title, string $body, array $data = []): bool
    {
        $userId = $user instanceof User ? $user->id : $user;

        $tokens = UserService::query()
            ->where('user_id', $userId)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->unique()
            ->filter();

        if ($tokens->isEmpty()) {
            Log::info('FCM skipped: no device tokens for user.', ['user_id' => $userId]);

            return false;
        }

        $sent = false;

        foreach ($tokens as $token) {
            if ($this->sendPushNotification($token, $title, $body, $data)) {
                $sent = true;
            }
        }

        return $sent;
    }

    public function sendPushNotification(?string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (blank($fcmToken)) {
            Log::info('FCM skipped: empty device token.');

            return false;
        }

        if ($this->projectId === '' || ! is_readable($this->credentialsPath)) {
            Log::error('FCM misconfigured.', [
                'project_id' => $this->projectId,
                'credentials_path' => $this->credentialsPath,
            ]);

            return false;
        }

        try {
            $accessToken = $this->getAccessToken();

            if (! $accessToken) {
                return false;
            }

            $payload = [
                'message' => [
                    'token' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $this->normalizeData($data),
                    'android' => [
                        'priority' => 'HIGH',
                        'notification' => [
                            'channel_id' => 'high_importance_channel',
                            'sound' => 'default',
                        ],
                    ],
                    'apns' => [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => 1,
                            ],
                        ],
                    ],
                ],
            ];

            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->post($this->endpoint(), $payload);

            if ($response->successful()) {
                Log::info('FCM notification sent.', [
                    'token_prefix' => substr($fcmToken, 0, 12),
                ]);

                return true;
            }

            $this->handleFailedResponse($fcmToken, $response);

            return false;
        } catch (\Throwable $e) {
            Log::error('FCM exception.', ['message' => $e->getMessage()]);

            return false;
        }
    }

    private function resolveCredentialsPath(string $credentials): string
    {
        if ($credentials === '') {
            return storage_path('app/firebase-credentials.json');
        }

        if (is_file($credentials)) {
            return $credentials;
        }

        $storagePath = storage_path('app/' . ltrim($credentials, '/'));

        if (is_file($storagePath)) {
            return $storagePath;
        }

        return base_path($credentials);
    }

    private function normalizeData(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            $normalized[(string) $key] = is_scalar($value) || $value === null
                ? (string) $value
                : json_encode($value);
        }

        return $normalized;
    }

    private function endpoint(): string
    {
        return "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
    }

    private function getAccessToken(): ?string
    {
        $client = new GoogleClient();
        $client->setAuthConfig($this->credentialsPath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();

        if (isset($token['error'])) {
            Log::error('FCM auth failed.', $token);

            return null;
        }

        return $token['access_token'] ?? null;
    }

    private function handleFailedResponse(string $fcmToken, Response $response): void
    {
        $body = $response->json();

        Log::warning('FCM send failed.', [
            'status' => $response->status(),
            'body' => $body,
        ]);

        $errorCode = data_get($body, 'error.details.0.errorCode')
            ?? data_get($body, 'error.status');

        if (in_array($errorCode, ['UNREGISTERED', 'NOT_FOUND', 'INVALID_ARGUMENT'], true)) {
            UserService::query()
                ->where('fcm_token', $fcmToken)
                ->delete();

            Log::info('FCM removed invalid device token.');
        }
    }
}
