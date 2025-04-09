<?php

namespace App\Services;

use App\Models\AmoToken;
use RuntimeException;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AmoCrmService
{
    protected string $baseUrl;
    public function __construct()
    {
        $this->baseUrl = "https://" . config('amo.base_domain');
    }
    protected function getAccessToken(): string
    {
        $token = AmoToken::first();

        if (!$token) {
            throw new RuntimeException('AmoCRM token not found');
        }

        if (now()->greaterThanOrEqualTo($token->expires_at)) {
            $this->refreshToken();
            $token = AmoToken::first();
        }

        return $token->access_token;
    }
    public function getLeads(array $params = [])
    {
        $token = $this->getAccessToken();

        $query = [
            'with' => 'contacts',
            'page' => max(1, (int)($params['page'] ?? 1)),
            'limit' => $this->normalizeLimit($params['limit'] ?? 25),
            'order[updated_at]' => $this->normalizeSort($params['sort'] ?? 'desc'),
        ];

        if (!empty($params['status_id'])) {
            $query['filter[statuses][]'] = (int)$params['status_id'];
        }

        try {
            if (!empty($params['from_date'])) {
                $query['filter[updated_at][from]'] = Carbon::parse($params['from_date'])->timestamp;
            }
            if (!empty($params['to_date'])) {
                $query['filter[updated_at][to]'] = Carbon::parse($params['to_date'])->timestamp;
            }
        } catch (\Exception $e) {
            throw new RuntimeException('Invalid date format: ' . $e->getMessage());
        }

        $response = Http::withToken($token)
            ->timeout(30)
            ->retry(3, 500)
            ->get("{$this->baseUrl}/api/v4/leads", $query);

        if ($response->failed()) {
            throw new RuntimeException('AmoCRM API error: ' . $response->status() . ' - ' . $response->body());
        }

        return $response->json();
    }
    public function getPipelines(): array
    {
        return Cache::remember('amo_pipelines', 3600, function () {
            $token = $this->getAccessToken();
            $response = Http::withToken($token)
                ->timeout(30)
                ->retry(3, 500)
                ->get("{$this->baseUrl}/api/v4/leads/pipelines");

            if ($response->failed()) {
                throw new RuntimeException('AmoCRM API error: ' . $response->status() . ' - ' . $response->body());
            }

            return $response->json();
        });
    }
    public function getStatusName(int $statusId, array $pipelines): ?string
    {
        foreach ($pipelines['_embedded']['pipelines'] as $pipeline) {
            foreach ($pipeline['_embedded']['statuses'] as $status) {
                if ($status['id'] === $statusId) {
                    return $status['name'];
                }
            }
        }
        return null;
    }
    public function refreshToken(): void
    {
        $token = AmoToken::first();

        $response = Http::post("{$this->baseUrl}/oauth2/access_token", [
            'client_id' => config('amo.client_id'),
            'client_secret' => config('amo.client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $token?->refresh_token ?? config('amo.refresh_token'),
            'redirect_uri' => config('amo.redirect_uri'),
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Failed to refresh token: ' . $response->body());
        }

        $data = $response->json();

        AmoToken::updateOrCreate(
            ['id' => $token?->id ?? 1],
            [
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'expires_at' => now()->addSeconds($data['expires_in']),
            ]
        );
    }
    public function authorize(): AmoToken
    {
        $token = AmoToken::first();

        if (!$token) {
            throw new RuntimeException('AmoCRM token not found');
        }

        if (now()->greaterThanOrEqualTo($token->expires_at)) {
            $this->refreshToken();
            $token = AmoToken::first();
        }

        return $token;
    }
    private function normalizeLimit($limit): int
    {
        $limit = (int)$limit;
        return in_array($limit, [10, 25, 50]) ? $limit : 25;
    }
    private function normalizeSort($sort): string
    {
        $sort = strtolower($sort);
        return in_array($sort, ['asc', 'desc']) ? $sort : 'desc';
    }
}
