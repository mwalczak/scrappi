<?php
namespace App\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/health',
            openapi: new Operation(
                summary: 'Health check endpoint',
                description: 'Returns the health status of the API',
                responses: [
                    '200' => new Response(
                        description: 'API is healthy',
                        content: new \ArrayObject([
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'status' => [
                                            'type' => 'string',
                                            'example' => 'healthy'
                                        ],
                                        'timestamp' => [
                                            'type' => 'string',
                                            'format' => 'date-time',
                                            'example' => '2025-10-29T12:00:00+00:00'
                                        ]
                                    ]
                                ]
                            ]
                        ])
                    )
                ]
            ),
            provider: 'App\State\HealthCheckProvider'
        )
    ],
    shortName: 'HealthCheck'
)]
class HealthCheck
{
    public string $status;
    public string $timestamp;
}
