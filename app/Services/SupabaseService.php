<?php

namespace App\Services;

use App\Contracts\StorageServiceInterface;
use Composer\CaBundle\CaBundle;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SupabaseService implements StorageServiceInterface
{
    /** Present only when URL and API keys are valid; otherwise requests must not run. */
    protected ?Client $client = null;

    protected string $url = '';

    protected ?string $key = null;

    protected ?string $serviceKey = null;

    public function __construct()
    {
        $rawUrl = trim((string) config('services.supabase.url', ''));
        $this->url = $rawUrl !== '' ? rtrim($rawUrl, '/') : '';
        $this->key = $this->normalizeSecret(config('services.supabase.key'));
        $this->serviceKey = $this->normalizeSecret(config('services.supabase.service_key'));

        $this->client = null;

        if (! $this->hasValidSupabaseConfig()) {
            return;
        }

        $defaultHeaders = array_merge(
            [
                'Content-Type' => 'application/json',
                'apikey' => $this->key,
            ],
            $this->authorizationHeaderForSupabaseKey($this->key)
        );

        $this->client = new Client([
            'base_uri' => $this->url.'/',
            'verify' => $this->resolveSslCaBundlePath(),
            'headers' => $defaultHeaders,
        ]);
    }

    /**
     * True when a Guzzle client can safely be created (valid URL, both keys present and recognized shape).
     */
    protected function hasValidSupabaseConfig(): bool
    {
        if ($this->url === '' || ! preg_match('#^https?://#i', $this->url)) {
            return false;
        }

        if (str_starts_with(strtolower($this->url), 'http://')
            && ! app()->environment(['local', 'testing'])) {
            return false;
        }

        if ($this->key === null || $this->serviceKey === null) {
            return false;
        }

        return $this->isLikelySupabaseApiKey($this->key)
            && $this->isLikelySupabaseApiKey($this->serviceKey);
    }

    protected function clientAvailable(): bool
    {
        return $this->client instanceof Client;
    }

    protected function normalizeSecret(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * Legacy JWT anon/service_role keys (three segments) or modern sb_publishable_* / sb_secret_* keys.
     */
    protected function isLikelySupabaseApiKey(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        return substr_count($token, '.') === 2
            || str_starts_with($token, 'sb_publishable_')
            || str_starts_with($token, 'sb_secret_');
    }

    /**
     * Opaque Supabase keys must not be sent as Bearer tokens; JWT-style keys use Bearer.
     *
     * @return array<string, string>
     */
    protected function authorizationHeaderForSupabaseKey(?string $key): array
    {
        if ($key === null || $key === '') {
            return [];
        }

        if (substr_count($key, '.') === 2) {
            return ['Authorization' => 'Bearer '.$key];
        }

        return [];
    }

    /**
     * Use a known CA bundle so HTTPS to Supabase works on systems where PHP/cURL has no CA store (common on Windows).
     */
    protected function resolveSslCaBundlePath(): bool|string
    {
        $configured = config('services.supabase.ca_bundle');
        if (is_string($configured) && $configured !== '' && is_readable($configured)) {
            return $configured;
        }
        if (is_string($configured) && $configured !== '') {
            Log::warning('Supabase Guzzle SSL: configured CA bundle is not readable, falling back', [
                'path' => $configured,
            ]);
        }

        try {
            return CaBundle::getSystemCaRootBundlePath();
        } catch (\Throwable $e) {
            Log::warning('Supabase Guzzle SSL: could not resolve CA bundle, falling back to default verify', [
                'error' => $e->getMessage(),
            ]);

            return true;
        }
    }

    public function from($table, $filters = [], $select = ['*'])
    {
        if (! $this->clientAvailable()) {
            Log::warning('Supabase REST query skipped: client not configured');

            return null;
        }

        try {
            $selectQuery = implode(',', $select);
            $url = "/rest/v1/{$table}?select={$selectQuery}";

            foreach ($filters as $key => $value) {
                $url .= "&{$key}=eq.{$value}";
            }

            $response = $this->client->get($url);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $exception) {
            Log::error('Supabase query error: '.$exception->getMessage());

            return null;
        }
    }

    public function insert($table, $data)
    {
        if (! $this->clientAvailable()) {
            Log::warning('Supabase insert skipped: client not configured');

            return null;
        }

        try {
            $response = $this->client->post("/rest/v1/{$table}", [
                'json' => $data,
                'headers' => [
                    'Prefer' => 'return=representation',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $exception) {
            Log::error('Supabase insert error: '.$exception->getMessage());

            return null;
        }
    }

    public function update($table, $filters, $data)
    {
        if (! $this->clientAvailable()) {
            Log::warning('Supabase update skipped: client not configured');

            return null;
        }

        try {
            $url = "/rest/v1/{$table}?";

            foreach ($filters as $key => $value) {
                $url .= "{$key}=eq.{$value}&";
            }
            $url = rtrim($url, '&');

            $response = $this->client->patch($url, [
                'json' => $data,
                'headers' => [
                    'Prefer' => 'return=representation',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $exception) {
            Log::error('Supabase update error: '.$exception->getMessage());

            return null;
        }
    }

    public function delete($table, $filters)
    {
        if (! $this->clientAvailable()) {
            Log::warning('Supabase delete skipped: client not configured');

            return false;
        }

        try {
            $url = "/rest/v1/{$table}?";

            foreach ($filters as $key => $value) {
                $url .= "{$key}=eq.{$value}&";
            }
            $url = rtrim($url, '&');

            $this->client->delete($url);

            return true;
        } catch (GuzzleException $exception) {
            Log::error('Supabase delete error: '.$exception->getMessage());

            return false;
        }
    }

    public function uploadFile(string $bucket, string $path, mixed $file): array
    {
        if (! $this->clientAvailable()) {
            $message = 'Supabase Storage is not configured. Set SUPABASE_URL, SUPABASE_KEY, and SUPABASE_SERVICE_KEY in your .env file (Supabase Dashboard → Settings → API).';

            Log::warning('Supabase upload skipped: client not configured');

            return [
                'success' => false,
                'message' => $message,
            ];
        }

        try {
            // Handle both file paths and direct content
            if (is_string($file)) {
                // If it's a string, check if it's a file path or direct content
                if (file_exists($file)) {
                    $fileContents = file_get_contents($file);
                    if ($fileContents === false) {
                        throw new \Exception('Failed to read file contents from path: '.$file);
                    }
                } else {
                    // It's direct content, not a file path
                    $fileContents = $file;
                }
            } else {
                $fileContents = $file;
            }

            if (empty($fileContents)) {
                throw new \Exception('File contents are empty');
            }

            Log::info('Attempting Supabase upload', [
                'bucket' => $bucket,
                'path' => $path,
                'size' => strlen($fileContents),
                'url' => $this->url."/storage/v1/object/{$bucket}/{$path}",
            ]);

            // Storage API expects both `apikey` and `Authorization` to match the service key. Explicitly set
            // Authorization so the Guzzle client's default anon Bearer header does not leak into this request,
            // which triggers `signature verification failed` on projects using the new (sb_secret_*) key format.
            $storageHeaders = [
                'apikey' => $this->serviceKey,
                'Authorization' => 'Bearer '.$this->serviceKey,
                'Content-Type' => 'application/octet-stream',
            ];

            $response = $this->client->post("/storage/v1/object/{$bucket}/{$path}", [
                'body' => $fileContents,
                'headers' => $storageHeaders,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            Log::info('Supabase upload response', [
                'status' => $statusCode,
                'body' => $body,
            ]);

            return [
                'success' => true,
                'status_code' => $statusCode,
                'response' => $body,
                'url' => $this->getPublicUrl($bucket, $path),
                'message' => 'File uploaded successfully',
            ];
        } catch (RequestException $exception) {
            $errorMessage = $exception->getMessage();
            $statusCode = $exception->hasResponse() ? $exception->getResponse()->getStatusCode() : null;
            $responseBody = $exception->hasResponse() ? $exception->getResponse()->getBody()->getContents() : null;

            Log::error('Supabase upload error', [
                'message' => $errorMessage,
                'status_code' => $statusCode,
                'response' => $responseBody,
                'bucket' => $bucket,
                'path' => $path,
            ]);

            return [
                'success' => false,
                'status_code' => $statusCode,
                'error' => $errorMessage,
                'response' => $responseBody,
                'message' => 'Upload failed: '.$errorMessage,
            ];
        } catch (GuzzleException $exception) {
            Log::error('Supabase Guzzle error: '.$exception->getMessage());

            return [
                'success' => false,
                'error' => $exception->getMessage(),
                'message' => 'Upload failed: '.$exception->getMessage(),
            ];
        } catch (\Exception $exception) {
            Log::error('File upload error: '.$exception->getMessage());

            return [
                'success' => false,
                'error' => $exception->getMessage(),
                'message' => 'Upload failed: '.$exception->getMessage(),
            ];
        }
    }

    public function getPublicUrl(string $bucket, string $path): string
    {
        return "{$this->url}/storage/v1/object/public/{$bucket}/{$path}";
    }

    public function deleteFile(string $bucket, string $path): bool
    {
        if (! $this->clientAvailable()) {
            Log::warning('Supabase delete file skipped: client not configured');

            return false;
        }

        try {
            $this->client->delete("/storage/v1/object/{$bucket}/{$path}", [
                'headers' => [
                    'apikey' => $this->serviceKey,
                    'Authorization' => 'Bearer '.$this->serviceKey,
                ],
            ]);

            return true;
        } catch (GuzzleException $exception) {
            Log::error('Supabase delete file error: '.$exception->getMessage());

            return false;
        }
    }

    public function listFiles(string $bucket, string $path = ''): ?array
    {
        if (! $this->clientAvailable()) {
            Log::warning('Supabase list files skipped: client not configured');

            return null;
        }

        try {
            $response = $this->client->post("/storage/v1/object/list/{$bucket}", [
                'json' => [
                    'prefix' => $path,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $exception) {
            Log::error('Supabase list files error: '.$exception->getMessage());

            return null;
        }
    }

    public function query($query)
    {
        if (! $this->clientAvailable()) {
            Log::warning('Supabase RPC query skipped: client not configured');

            return null;
        }

        try {
            $rpcHeaders = array_merge(
                [
                    'apikey' => $this->serviceKey,
                ],
                $this->authorizationHeaderForSupabaseKey($this->serviceKey)
            );

            $response = $this->client->post('/rest/v1/rpc/sql_query', [
                'json' => ['query' => $query],
                'headers' => $rpcHeaders,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $exception) {
            Log::error('Supabase query error: '.$exception->getMessage());

            return null;
        }
    }
}
