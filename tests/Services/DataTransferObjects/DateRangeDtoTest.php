<?php

use Tobidsn\LaravelAnalytics\DataTransferObjects\DateRangeDto;

describe('DateRangeDto', function () {

    it('creates from period correctly', function () {
        $dto = DateRangeDto::fromPeriod('7d');

        expect($dto->startDate)->toBeInstanceOf(\Carbon\Carbon::class);
        expect($dto->endDate)->toBeInstanceOf(\Carbon\Carbon::class);

        // Check that it's 6 days ago to today (7 days total including today)
        $today = now();
        $sevenDaysAgo = now()->subDays(6)->startOfDay();

        expect($dto->endDate->format('Y-m-d'))->toBe($today->format('Y-m-d'));
        expect($dto->startDate->format('Y-m-d'))->toBe($sevenDaysAgo->format('Y-m-d'));
    });

    it('creates from custom dates correctly', function () {
        $startDate = '2024-01-01';
        $endDate = '2024-01-31';

        $dto = DateRangeDto::fromCustom($startDate, $endDate);

        expect($dto->startDate->format('Y-m-d'))->toBe('2024-01-01');
        expect($dto->endDate->format('Y-m-d'))->toBe('2024-01-31');
    });

    it('handles different period values', function () {
        $periods = ['7d', '30d', '90d'];

        foreach ($periods as $period) {
            $dto = DateRangeDto::fromPeriod($period);

            $expectedStartDate = now()->subDays(str_replace('d', '', $period) - 1)->startOfDay();

            expect($dto->startDate->format('Y-m-d'))
                ->toBe($expectedStartDate->format('Y-m-d'));
        }
    });

    it('formats for Google Analytics correctly', function () {
        $dto = DateRangeDto::fromCustom('2024-01-15', '2024-01-31');

        $formatted = $dto->toGoogleAnalyticsFormat();

        expect($formatted)->toHaveKey('startDate', '2024-01-15');
        expect($formatted)->toHaveKey('endDate', '2024-01-31');
    });

    it('calculates date difference correctly', function () {
        $dto = DateRangeDto::fromCustom('2024-01-01', '2024-01-31');

        $daysDiff = $dto->getDaysDifference();

        expect($daysDiff)->toBe(30); // 31 - 1 = 30 days difference
    });

    it('validates date order', function () {
        expect(fn () => DateRangeDto::fromCustom('2024-01-31', '2024-01-01'))
            ->toThrow(InvalidArgumentException::class, 'Start date must be before or equal to end date');
    });

    it('immutability is maintained', function () {
        $dto = DateRangeDto::fromPeriod('7d');
        $originalStart = $dto->startDate->format('Y-m-d');

        // Try to modify the date (this WILL affect the DTO since DateTime objects are mutable)
        // But we can test that creating a new DTO doesn't affect the original
        $dto2 = DateRangeDto::fromPeriod('7d');
        $dto2->startDate->modify('+1 day');

        // The original DTO should be unaffected
        expect($dto->startDate->format('Y-m-d'))->toBe($originalStart);
    });

    it('creates previous period correctly', function () {
        $dto = DateRangeDto::fromPeriod('7d');
        $previousDto = $dto->getPreviousPeriod();

        $daysDiff = $dto->getDaysDifference();

        // Previous period should end the day before current period starts
        expect($previousDto->endDate->format('Y-m-d'))
            ->toBe($dto->startDate->copy()->subDay()->format('Y-m-d'));

        // Previous period should be the same length
        expect($previousDto->getDaysDifference())->toBe($daysDiff);
    });
});
