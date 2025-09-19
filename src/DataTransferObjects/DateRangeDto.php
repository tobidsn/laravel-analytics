<?php

namespace Tobidsn\LaravelAnalytics\DataTransferObjects;

use Carbon\Carbon;
use InvalidArgumentException;

final readonly class DateRangeDto
{
    public function __construct(
        public Carbon $startDate,
        public Carbon $endDate,
    ) {
        if ($this->startDate->isAfter($this->endDate)) {
            throw new InvalidArgumentException('Start date must be before or equal to end date');
        }
    }

    public static function fromPeriod(string $period, ?string $startDate = null, ?string $endDate = null): self
    {
        if ($period === 'custom' && $startDate && $endDate) {
            return new self(
                startDate: Carbon::parse($startDate),
                endDate: Carbon::parse($endDate),
            );
        }

        // Handle predefined periods like "7", "30", "90" or "7d", "30d", "90d"
        $days = (int) str_replace('d', '', $period);

        return new self(
            startDate: Carbon::now()->subDays($days - 1)->startOfDay(),
            endDate: Carbon::now()->endOfDay(),
        );
    }

    public static function fromCustom(string $startDate, string $endDate): self
    {
        return new self(
            startDate: Carbon::parse($startDate)->startOfDay(),
            endDate: Carbon::parse($endDate)->endOfDay(),
        );
    }

    public function getDays(): int
    {
        return (int) $this->startDate->diffInDays($this->endDate) + 1;
    }

    public function getDaysDifference(): int
    {
        return (int) $this->startDate->diffInDays($this->endDate);
    }

    public function toGoogleAnalyticsFormat(): array
    {
        return [
            'startDate' => $this->startDate->format('Y-m-d'),
            'endDate' => $this->endDate->format('Y-m-d'),
        ];
    }

    public function getPreviousPeriod(): self
    {
        $daysDiff = $this->getDaysDifference();

        return new self(
            startDate: $this->startDate->copy()->subDays($daysDiff + 1),
            endDate: $this->startDate->copy()->subDay(),
        );
    }

    public function toArray(): array
    {
        return [
            'start_date' => $this->startDate->format('Y-m-d'),
            'end_date' => $this->endDate->format('Y-m-d'),
            'days' => $this->getDays(),
        ];
    }
}
