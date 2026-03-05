<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class SupabaseService
{
    protected $client;
    protected $url;
    protected $key;
    protected $serviceKey;

    public function __construct()
    {
        $this->url = config('services.supabase.url');
        $this->key = config('services.supabase.key');
        $this->serviceKey = config('services.supabase.service_key');

        $this->client = new Client([
            'base_uri' => $this->url,
            'headers' => [
                'apikey' => $this->key,
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function from($table, $filters = [], $select = ['*']) 
    {
        try {
            $selectQuery = implode(',', $select);
            $url = "/rest/v1/{$table}?select={$selectQuery}";

            foreach ($filters as $key => $value) {
                $url .= "&{$key}=eq.{$value}";
            }

            $response = $this->client->get($url);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Supabase query error: ' . $e->getMessage());
            return null;
        }
    }

    public function insert($table, $data)
    {
        try {
            $response = $this->client->post("/rest/v1/{$table}", [
                'json' => $data,
                'headers' => [
                    'Prefer' => 'return=representation'
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Supabase insert error: ' . $e->getMessage());
            return null;
        }
    }

    public function update($table, $filters, $data)
    {
        try {
            $url = "/rest/v1/{$table}?";

            foreach ($filters as $key => $value) {
                $url .= "{$key}=eq.{$value}&";
            }
            $url = rtrim($url, '&');

            $response = $this->client->patch($url, [
                'json' => $data,
                'headers' => [
                    'Prefer' => 'return=representation'
                ]
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Supabase update error: ' . $e->getMessage());
            return null;
        }
    }

    public function delete($table, $filters)
    {
        try {
            $url = "/rest/v1/{$table}?";

            foreach ($filters as $key => $value) {
                $url .= "{$key}=eq.{$value}&";
            }
            $url = rtrim($url, '&');

            $this->client->delete($url);
            return true;
        } catch (GuzzleException $e) {
            Log::error('Supabase delete error: ' . $e->getMessage());
            return false;
        }
    }

    public function uploadFile($bucket, $path, $file)
    {
        try {
            // Handle both file paths and direct content
            if (is_string($file)) {
                // If it's a string, check if it's a file path or direct content
                if (file_exists($file)) {
                    $fileContents = file_get_contents($file);
                    if ($fileContents === false) {
                        throw new \Exception('Failed to read file contents from path: ' . $file);
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
                'url' => $this->url . "/storage/v1/object/{$bucket}/{$path}"
            ]);

            $response = $this->client->post("/storage/v1/object/{$bucket}/{$path}", [
                'body' => $fileContents,
                'headers' => [
                    'apikey' => $this->key,
                    'Authorization' => 'Bearer ' . $this->serviceKey,
                    'Content-Type' => 'application/octet-stream',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            Log::info('Supabase upload response', [
                'status' => $statusCode,
                'body' => $body
            ]);

            return [
                'success' => true,
                'status_code' => $statusCode,
                'response' => $body,
                'url' => $this->getPublicUrl($bucket, $path),
                'message' => 'File uploaded successfully'
            ];
        } catch (RequestException $e) {
            $errorMessage = $e->getMessage();
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;
            $responseBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : null;
            
            Log::error('Supabase upload error', [
                'message' => $errorMessage,
                'status_code' => $statusCode,
                'response' => $responseBody,
                'bucket' => $bucket,
                'path' => $path
            ]);
            
            return [
                'success' => false,
                'status_code' => $statusCode,
                'error' => $errorMessage,
                'response' => $responseBody,
                'message' => 'Upload failed: ' . $errorMessage
            ];
        } catch (GuzzleException $e) {
            Log::error('Supabase Guzzle error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Upload failed: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            Log::error('File upload error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }
    public function getPublicUrl($bucket, $path)
    {
        return "{$this->url}/storage/v1/object/public/{$bucket}/{$path}";
    }

    public function deleteFile($bucket, $path)
    {
        try {
            $this->client->delete("/storage/v1/object/{$bucket}/{$path}");
            return true;
        } catch (GuzzleException $e) {
            Log::error('Supabase delete file error: ' . $e->getMessage());
            return false;
        }
    }

    public function listFiles($bucket, $path = '')
    {
        try {
            $response = $this->client->post("/storage/v1/object/list/{$bucket}", [
                'json' => [
                    'prefix' => $path,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Supabase list files error: ' . $e->getMessage());
            return null;
        }
    }
    public function query($query)
    {
        try {
            $response = $this->client->post("/rest/v1/rpc/sql_query", [
                'json' => ['query' => $query],
                'headers' => [
                    'apikey' => $this->serviceKey,
                    'Authorization' => 'Bearer ' . $this->serviceKey,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            Log::error('Supabase query error: ' . $e->getMessage());
            return null;
        }
    }
}

