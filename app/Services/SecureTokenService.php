<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

final class SecureTokenService
{
    private const CIPHER = 'aes-256-gcm';

    private const HASH_ALGO = 'sha256';

    /**
     * Encrypt WhatsApp token with user context
     */
    public function encryptToken($token, $userId, $userEmail, $userUuid): array
    {
        try {
            $key = $this->getUserKey($userId, $userEmail, $userUuid);

            // Generate random IV (Initialization Vector)
            $ivLength = openssl_cipher_iv_length(self::CIPHER);
            $iv = openssl_random_pseudo_bytes($ivLength);

            // Create payload with metadata
            $payload = json_encode([
                'token' => $token,
                'uuid' => $userUuid,
                'email' => $userEmail,
                'user_id' => $userId,
                'timestamp' => now()->timestamp,
            ]);

            // Encrypt with GCM mode (provides authentication)
            $tag = '';
            $encrypted = openssl_encrypt(
                $payload,
                self::CIPHER,
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag,
                '', // additional authenticated data
                16  // tag length
            );

            throw_if($encrypted === false, Exception::class, 'Encryption failed');

            // Create HMAC for additional integrity check
            $hmac = hash_hmac(
                self::HASH_ALGO,
                $encrypted.$iv.$tag,
                $key,
                true
            );

            // Combine everything and encode
            $combined = base64_encode($iv.$tag.$encrypted.$hmac);

            return [
                'encrypted_token' => $combined,
                'version' => 1, // For future algorithm updates
            ];
        } catch (Exception $exception) {
            Log::error('Token encryption failed: '.$exception->getMessage());
            throw new Exception('Failed to secure token', $exception->getCode(), $exception);
        }
    }

    /**
     * Decrypt WhatsApp token and verify user context
     */
    public function decryptToken($encryptedData, $userId, $userEmail, $userUuid): array
    {
        try {
            $key = $this->getUserKey($userId, $userEmail, $userUuid);

            // Decode the combined data
            $decoded = base64_decode((string) $encryptedData);

            throw_if($decoded === false, Exception::class, 'Invalid encrypted data format');

            // Extract components
            $ivLength = openssl_cipher_iv_length(self::CIPHER);
            $tagLength = 16;
            $hmacLength = 32; // SHA256 produces 32 bytes

            $iv = mb_substr($decoded, 0, $ivLength);
            $tag = mb_substr($decoded, $ivLength, $tagLength);
            $hmac = mb_substr($decoded, -$hmacLength);
            $encrypted = mb_substr($decoded, $ivLength + $tagLength, -$hmacLength);

            // Verify HMAC first
            $expectedHmac = hash_hmac(
                self::HASH_ALGO,
                $encrypted.$iv.$tag,
                $key,
                true
            );

            throw_unless(hash_equals($expectedHmac, $hmac), Exception::class, 'Data integrity check failed');

            // Decrypt
            $decrypted = openssl_decrypt(
                $encrypted,
                self::CIPHER,
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );

            throw_if($decrypted === false, Exception::class, 'Decryption failed');

            // Parse payload
            $payload = json_decode($decrypted, true);

            throw_unless($payload, Exception::class, 'Invalid payload format');

            // Verify user context matches
            throw_if($payload['uuid'] !== $userUuid ||
            $payload['email'] !== $userEmail ||
            $payload['user_id'] !== $userId, Exception::class, 'User context mismatch - possible tampering detected');

            // Check if token is not too old (optional - 1 year max age)
            $tokenAge = now()->timestamp - $payload['timestamp'];
            // 1 year in seconds
            throw_if($tokenAge > 31536000, Exception::class, 'Token expired - please reconnect WhatsApp');

            return [
                'token' => $payload['token'],
                'uuid' => $payload['uuid'],
                'email' => $payload['email'],
                'user_id' => $payload['user_id'],
                'created_at' => $payload['timestamp'],
            ];
        } catch (Exception $exception) {
            Log::error('Token decryption failed: '.$exception->getMessage(), [
                'user_id' => $userId,
            ]);
            throw new Exception('Failed to retrieve token - please reconnect WhatsApp', $exception->getCode(), $exception);
        }
    }

    /**
     * Re-encrypt token (for key rotation)
     */
    public function rotateToken($encryptedData, $userId, $userEmail, $userUuid): array
    {
        // Decrypt with old key
        $decrypted = $this->decryptToken($encryptedData, $userId, $userEmail, $userUuid);

        // Re-encrypt with new key
        return $this->encryptToken($decrypted['token'], $userId, $userEmail, $userUuid);
    }

    /**
     * Generate user-specific encryption key
     */
    private function getUserKey(string $userId, string $userEmail, string $userUuid): string
    {
        // Combine app key with user-specific data
        $appKey = config('app.key');
        $userSalt = config('app.whatsapp_salt'); // Add this to .env

        // Create a unique key for this user
        return hash_pbkdf2(
            self::HASH_ALGO,
            $appKey.$userSalt,
            $userUuid.$userEmail.$userId,
            100000, // iterations
            32,     // key length
            true    // raw output
        );
    }
}
