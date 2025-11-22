<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Base test case for API functional tests.
 * Provides helper methods for common API testing patterns.
 */
abstract class ApiTestCase extends WebTestCase
{
    /**
     * Get and decode JSON response from the client.
     *
     * @return array<mixed>
     */
    protected function getJsonResponse(KernelBrowser $client): array
    {
        $content = $client->getResponse()->getContent();
        $this->assertIsString($content, 'Response content should be a string');

        $decoded = json_decode($content, true);
        $this->assertIsArray($decoded, 'Response content should be valid JSON');

        return $decoded;
    }
}
