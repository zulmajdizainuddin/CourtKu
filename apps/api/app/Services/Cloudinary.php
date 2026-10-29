<?php

namespace App\Services;

use App\Exceptions\ApiException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Stores venue photos on Cloudinary (ADR-018), using its plain web API.
 *
 * Every request is signed with our API secret, so only CourtKu's server can add or
 * remove photos; the secret never leaves the server. We keep only Cloudinary's id for
 * each photo and build its web addresses here, in one place, so moving to another
 * storage later means changing this class.
 */
class Cloudinary
{
    /** Photos are stored at most 2000 pixels wide or high, re-encoded without metadata (e.g. GPS). */
    private const STORE_AS = 'c_limit,w_2000,h_2000';

    public function __construct(
        private readonly ?string $cloudName,
        private readonly ?string $apiKey,
        private readonly ?string $apiSecret,
        private readonly string $folder,
    ) {}

    public function isConfigured(): bool
    {
        return filled($this->cloudName) && filled($this->apiKey) && filled($this->apiSecret);
    }

    /**
     * Uploads a photo into "{folder}/{subfolder}" under a random name chosen by Cloudinary.
     *
     * @return array{public_id: string, width: int, height: int}
     */
    public function upload(UploadedFile $file, string $subfolder): array
    {
        $this->ensureConfigured();

        $params = [
            'folder' => $this->folder.'/'.$subfolder,
            'timestamp' => time(),
            'transformation' => self::STORE_AS,
        ];

        $response = Http::timeout(30)
            ->attach('file', (string) file_get_contents($file->getRealPath()), 'photo')
            ->post($this->apiUrl('image/upload'), [
                ...$params,
                'api_key' => $this->apiKey,
                'signature' => $this->sign($params),
            ]);

        if ($response->failed()) {
            Log::warning('Cloudinary upload failed.', ['status' => $response->status(), 'error' => $response->json('error.message')]);
            throw new ApiException('PHOTO_UPLOAD_FAILED', 'Could not store the photo. Please try again in a moment.', 502);
        }

        return [
            'public_id' => (string) $response->json('public_id'),
            'width' => (int) $response->json('width'),
            'height' => (int) $response->json('height'),
        ];
    }

    /**
     * Removes a photo from Cloudinary. A failure is only logged: the photo is already
     * gone from CourtKu, and an unused file costs nothing but a little storage.
     */
    public function destroy(string $publicId): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $params = ['public_id' => $publicId, 'timestamp' => time()];
        $response = Http::timeout(15)->asForm()->post($this->apiUrl('image/destroy'), [
            ...$params,
            'api_key' => $this->apiKey,
            'signature' => $this->sign($params),
        ]);

        if ($response->failed()) {
            Log::warning('Cloudinary delete failed.', ['public_id' => $publicId, 'status' => $response->status()]);
        }
    }

    /**
     * The web address of a photo, resized and compressed by Cloudinary as it is delivered.
     * f_auto picks the best format for the browser (e.g. WebP), q_auto a good compression.
     */
    public function url(string $publicId, string $size): string
    {
        return "https://res.cloudinary.com/{$this->cloudName}/image/upload/{$size},f_auto,q_auto/{$publicId}";
    }

    /**
     * Cloudinary's signature: the parameters sorted by name, joined as "a=1&b=2",
     * the API secret added at the end, then SHA-1 of all of it.
     *
     * @param  array<string, string|int>  $params
     */
    public function sign(array $params): string
    {
        ksort($params);
        $pairs = array_map(fn (string $key, string|int $value) => "{$key}={$value}", array_keys($params), $params);

        return sha1(implode('&', $pairs).$this->apiSecret);
    }

    private function apiUrl(string $path): string
    {
        return "https://api.cloudinary.com/v1_1/{$this->cloudName}/{$path}";
    }

    private function ensureConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new ApiException('PHOTO_STORAGE_OFF', 'Photo uploads are not set up on this server yet.', 503);
        }
    }
}
