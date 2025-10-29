<?php
namespace App\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/health',
            openapiContext: [
                'summary' => 'Health check endpoint',
                'description' => 'Returns the health status of the API',
                'responses' => [
                    '200' => [
                        'description' => 'API is healthy',
                        'content' => [
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
                        ]
                    ]
                ]
            ],
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
