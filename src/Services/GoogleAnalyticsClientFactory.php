<?php

namespace Tobidsn\LaravelAnalytics\Services;

use Exception;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;

final class GoogleAnalyticsClientFactory
{
    public function __construct(
        private readonly ?string $propertyId,
        private readonly ?string $credentialsPath
    ) {}

    public function create(): BetaAnalyticsDataClient
    {
        if (! $this->propertyId) {
            throw new Exception('Google Analytics Property ID is not configured.');
        }

        if (! $this->credentialsPath || ! file_exists($this->credentialsPath)) {
            throw new Exception('Google Analytics credentials file not found at: '.($this->credentialsPath ?? 'null'));
        }

        return new BetaAnalyticsDataClient([
            'credentials' => $this->credentialsPath,
        ]);
    }

    public function getPropertyId(): string
    {
        if (! $this->propertyId) {
            throw new Exception('Google Analytics Property ID is not configured.');
        }

        return $this->propertyId;
    }

    public function isConfigured(): bool
    {
        return $this->propertyId && $this->credentialsPath && file_exists($this->credentialsPath);
    }
}
