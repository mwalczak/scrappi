<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HealthCheckTest extends WebTestCase
{
    public function testHealthCheckReturnsSuccessfulResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/health');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('healthy', $responseData['status']);
        $this->assertArrayHasKey('timestamp', $responseData);

        // Verify timestamp is a valid ISO 8601 format
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/',
            $responseData['timestamp']
        );
    }

    public function testHealthCheckReturnsJsonContent(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/health');

        // API Platform returns application/ld+json by default
        $this->assertResponseHeaderSame('Content-Type', 'application/ld+json; charset=utf-8');
    }

    public function testHealthCheckAppearsInApiDocumentation(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/docs.json');

        $this->assertResponseIsSuccessful();

        $apiDoc = json_decode($client->getResponse()->getContent(), true);

        // Check that /health endpoint is documented
        $this->assertArrayHasKey('paths', $apiDoc);
        $this->assertArrayHasKey('/api/health', $apiDoc['paths']);
        $this->assertArrayHasKey('get', $apiDoc['paths']['/api/health']);

        // Verify the documentation contains expected information
        $healthEndpoint = $apiDoc['paths']['/api/health']['get'];
        $this->assertStringContainsString('health', strtolower($healthEndpoint['summary'] ?? ''));
    }
}

