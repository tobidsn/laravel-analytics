<?php

namespace Tobidsn\LaravelAnalytics\Services;

use Carbon\Carbon;
use Tobidsn\LaravelAnalytics\DataTransferObjects\DateRangeDto;

final class DateRangeCalculator
{
    public function calculateFromPreset(?string $preset, ?string $startDate = null, ?string $endDate = null, int $defaultDays = 30): DateRangeDto
    {
        $now = Carbon::now();

        if ($preset) {
            return match ($preset) {
                'today' => new DateRangeDto(
                    $now->copy()->startOfDay(),
                    $now->copy()->endOfDay()
                ),
                'yesterday' => new DateRangeDto(
                    $now->copy()->subDay()->startOfDay(),
                    $now->copy()->subDay()->endOfDay()
                ),
                '7d' => new DateRangeDto(
                    $now->copy()->subDays(6)->startOfDay(),
                    $now->copy()->endOfDay()
                ),
                '30d' => new DateRangeDto(
                    $now->copy()->subDays(29)->startOfDay(),
                    $now->copy()->endOfDay()
                ),
                '90d' => new DateRangeDto(
                    $now->copy()->subDays(89)->startOfDay(),
                    $now->copy()->endOfDay()
                ),
                'ytd' => new DateRangeDto(
                    $now->copy()->startOfYear(),
                    $now->copy()->endOfYear()
                ),
                'last_month' => new DateRangeDto(
                    $now->copy()->subMonth()->startOfMonth(),
                    $now->copy()->subMonth()->endOfMonth()
                ),
                'custom' => $this->parseCustomDates($startDate, $endDate, $defaultDays, $now),
                default => $this->getDefaultRange($defaultDays, $now),
            };
        }

        if ($startDate && $endDate) {
            return new DateRangeDto(
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            );
        }

        return $this->getDefaultRange($defaultDays, $now);
    }

    public function calculatePreviousRange(DateRangeDto $currentRange, ?string $preset = null): DateRangeDto
    {
        $isSingleDay = $currentRange->startDate->isSameDay($currentRange->endDate);

        if ($isSingleDay) {
            $previousDate = $currentRange->startDate->copy()->subDay();

            return new DateRangeDto($previousDate, $previousDate);
        }

        if ($preset === 'ytd') {
            return new DateRangeDto(
                $currentRange->startDate->copy()->subYear(),
                $currentRange->endDate->copy()->subYear()
            );
        }

        $currentDays = $currentRange->getDays();
        $previousEndDate = $currentRange->startDate->copy()->subDay();
        $previousStartDate = $previousEndDate->copy()->subDays($currentDays - 1);

        return new DateRangeDto($previousStartDate, $previousEndDate);
    }

    public function generatePreviousPeriodLabel(DateRangeDto $currentRange, ?string $preset = null): string
    {
        if ($currentRange->startDate->isSameDay($currentRange->endDate)) {
            return 'Previous day';
        }

        if ($preset === 'ytd') {
            return 'Previous year';
        }

        $previousRange = $this->calculatePreviousRange($currentRange, $preset);
        $startDateStr = $previousRange->startDate->format('M j');
        $endDateStr = $previousRange->endDate->format('M j');

        return $startDateStr === $endDateStr ? $startDateStr : "$startDateStr - $endDateStr";
    }

    private function parseCustomDates(?string $startDate, ?string $endDate, int $defaultDays, Carbon $now): DateRangeDto
    {
        if ($startDate && $endDate) {
            return new DateRangeDto(
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            );
        }

        return $this->getDefaultRange($defaultDays, $now);
    }

    private function getDefaultRange(int $defaultDays, Carbon $now): DateRangeDto
    {
        return new DateRangeDto(
            $now->copy()->subDays($defaultDays - 1)->startOfDay(),
            $now->copy()->endOfDay()
        );
    }
}
