<?php

use Tobidsn\LaravelAnalytics\Http\Requests\AnalyticsRequest;

describe('AnalyticsRequest', function () {

    it('validates preset parameter correctly', function () {
        $request = new AnalyticsRequest;
        $rules = $request->rules();

        expect($rules['preset'])->toContain('nullable');
        expect($rules['preset'])->toContain('string');

        // Check that we have a Rule::in object for valid presets
        $hasInRule = false;
        foreach ($rules['preset'] as $rule) {
            if ($rule instanceof \Illuminate\Validation\Rules\In) {
                $hasInRule = true;
                break;
            }
        }
        expect($hasInRule)->toBeTrue();
    });

    it('validates date parameters for custom preset', function () {
        $request = new AnalyticsRequest;
        $rules = $request->rules();

        expect($rules['start_date'])->toContain('required_if:preset,custom');
        expect($rules['start_date'])->toContain('date');
        expect($rules['start_date'])->toContain('before_or_equal:end_date');

        expect($rules['end_date'])->toContain('required_if:preset,custom');
        expect($rules['end_date'])->toContain('date');
        expect($rules['end_date'])->toContain('after_or_equal:start_date');
    });

    it('validates pagination parameters', function () {
        $request = new AnalyticsRequest;
        $rules = $request->rules();

        expect($rules['page'])->toContain('sometimes');
        expect($rules['page'])->toContain('integer');
        expect($rules['page'])->toContain('min:1');

        expect($rules['per_page'])->toContain('sometimes');
        expect($rules['per_page'])->toContain('integer');
        expect($rules['per_page'])->toContain('min:1');
        expect($rules['per_page'])->toContain('max:100');
    });

    it('validates sorting parameters', function () {
        $request = new AnalyticsRequest;
        $rules = $request->rules();

        expect($rules['sort_by'])->toContain('sometimes');
        expect($rules['sort_by'])->toContain('string');

        expect($rules['sort_direction'])->toContain('sometimes');
        expect($rules['sort_direction'])->toContain('string');

        // Check that we have a Rule::in object for sort_direction
        $hasInRule = false;
        foreach ($rules['sort_direction'] as $rule) {
            if ($rule instanceof \Illuminate\Validation\Rules\In) {
                $hasInRule = true;
                break;
            }
        }
        expect($hasInRule)->toBeTrue();
    });

    it('provides custom error messages', function () {
        $request = new AnalyticsRequest;
        $messages = $request->messages();

        expect($messages)->toHaveKey('preset.in');
        expect($messages)->toHaveKey('start_date.required_if');
        expect($messages)->toHaveKey('end_date.after_or_equal');
    });

    it('provides custom attribute names', function () {
        $request = new AnalyticsRequest;
        $attributes = $request->attributes();

        expect($attributes)->toHaveKey('start_date');
        expect($attributes)->toHaveKey('end_date');
        expect($attributes)->toHaveKey('per_page');
        expect($attributes)->toHaveKey('sort_by');
        expect($attributes)->toHaveKey('sort_direction');
    });

    it('prepares input correctly', function () {
        $request = new AnalyticsRequest;

        // Mock the input method
        $request->merge([
            'preset' => '  7d  ',
            'sort_direction' => '  ASC  ',
            'page' => '2',
        ]);

        // Use reflection to call protected method
        $reflection = new ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        expect($request->input('preset'))->toBe('7d');
        expect($request->input('sort_direction'))->toBe('asc');
        expect($request->input('page'))->toBe('2');
    });

    it('determines if validation passes with valid data', function () {
        $validData = [
            'preset' => '30d',
        ];

        $request = AnalyticsRequest::create('/', 'GET', $validData);

        expect($request->preset)->toBe('30d');
    });

    it('determines if validation passes with custom date range', function () {
        $validData = [
            'preset' => 'custom',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ];

        $request = AnalyticsRequest::create('/', 'GET', $validData);

        expect($request->preset)->toBe('custom');
        expect($request->start_date)->toBe('2024-01-01');
        expect($request->end_date)->toBe('2024-01-31');
    });
});
