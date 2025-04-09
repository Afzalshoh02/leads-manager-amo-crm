<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Services\AmoCrmService;
use Illuminate\Support\Facades\Http;
use App\Models\AmoToken;

class AmoCrmServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['amo.base_domain' => 'test.amocrm.ru']);
        config(['amo.client_id' => 'test_client_id']);
        config(['amo.client_secret' => 'test_client_secret']);
        config(['amo.redirect_uri' => 'http://test.com']);
    }

    public function test_authorization_and_token_refresh()
    {
        Http::fake([
            '*/oauth2/access_token' => Http::response([
                'access_token' => 'mock_token',
                'refresh_token' => 'mock_refresh',
                'expires_in' => 3600
            ], 200),
        ]);

        AmoToken::create([
            'access_token' => 'old_token',
            'refresh_token' => 'old_refresh',
            'expires_at' => now()->subHour()
        ]);

        $amoService = new AmoCrmService();
        $token = $amoService->authorize();

        $this->assertEquals('mock_token', $token->access_token);
        $this->assertDatabaseHas('amo_tokens', ['access_token' => 'mock_token']);
    }

    public function test_get_leads_with_filters()
    {
        AmoToken::create([
            'access_token' => 'valid_token',
            'refresh_token' => 'valid_refresh',
            'expires_at' => now()->addHour()
        ]);

        Http::fake([
            '*/api/v4/leads*' => Http::response([
                '_embedded' => [
                    'leads' => [[
                        'name' => 'Lead 1',
                        'status_id' => 1,
                        'updated_at' => '2025-04-09T12:00:00',
                        '_embedded' => ['contacts' => [['name' => 'John Doe']]]
                    ]]
                ],
                '_total' => 1,
            ], 200),
        ]);

        $params = [
            'page' => 1,
            'limit' => 25,
            'status_id' => 1,
            'from_date' => '2025-01-01',
            'to_date' => '2025-12-31',
            'sort' => 'desc',
        ];

        $amoService = new AmoCrmService();
        $leadsData = $amoService->getLeads($params);

        $this->assertCount(1, $leadsData['_embedded']['leads']);
        $this->assertEquals('Lead 1', $leadsData['_embedded']['leads'][0]['name']);
    }

    public function test_lead_pagination()
    {
        AmoToken::create([
            'access_token' => 'valid_token',
            'refresh_token' => 'valid_refresh',
            'expires_at' => now()->addHour()
        ]);

        Http::fake([
            '*/api/v4/leads*' => Http::response([
                '_embedded' => ['leads' => [['name' => 'Lead 1']]],
                '_total' => 100,
            ], 200),
        ]);

        $params = ['page' => 1, 'limit' => 10];
        $amoService = new AmoCrmService();
        $leadsData = $amoService->getLeads($params);

        $this->assertEquals(100, $leadsData['_total']);
        $this->assertCount(1, $leadsData['_embedded']['leads']);
    }

    public function test_error_when_token_is_missing()
    {
        $amoService = new AmoCrmService();
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('AmoCRM token not found');
        $amoService->getLeads([]);
    }

    public function test_invalid_date_format()
    {
        AmoToken::create([
            'access_token' => 'valid_token',
            'refresh_token' => 'valid_refresh',
            'expires_at' => now()->addHour()
        ]);

        $params = ['from_date' => 'invalid-date'];
        $amoService = new AmoCrmService();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Invalid date format/');
        $amoService->getLeads($params);
    }
}
