<?php

declare(strict_types=1);

namespace Tobidsn\LaravelAnalytics\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnalyticsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'preset' => [
                'nullable',
                'string',
                Rule::in(['today', 'yesterday', '7d', '30d', '90d', 'ytd', 'last_month', 'custom']),
            ],
            'start_date' => [
                'required_if:preset,custom',
                'date',
                'before_or_equal:end_date',
                'after_or_equal:'.now()->subYear()->format('Y-m-d'),
            ],
            'end_date' => [
                'required_if:preset,custom',
                'date',
                'after_or_equal:start_date',
                'before_or_equal:'.now()->format('Y-m-d'),
            ],
            'page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:1000',
            ],
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
            'sort_by' => [
                'sometimes',
                'string',
                Rule::in(['sessions', 'users', 'pageviews', 'bounce_rate', 'avg_session_duration']),
            ],
            'sort_direction' => [
                'sometimes',
                'string',
                Rule::in(['asc', 'desc']),
            ],
            'filters' => [
                'sometimes',
                'array',
            ],
            'filters.country' => [
                'sometimes',
                'string',
                'max:2', // ISO country code
            ],
            'filters.device_category' => [
                'sometimes',
                'string',
                Rule::in(['mobile', 'desktop', 'tablet']),
            ],
            'filters.source' => [
                'sometimes',
                'string',
                'max:255',
            ],
            'refresh_cache' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'preset.in' => 'The preset must be one of: today, yesterday, 7d, 30d, 90d, ytd, last_month, or custom.',
            'start_date.required_if' => 'Start date is required when period is custom.',
            'end_date.required_if' => 'End date is required when period is custom.',
            'start_date.before_or_equal' => 'Start date must be before or equal to end date.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'start_date.after_or_equal' => 'Start date cannot be more than 1 year ago.',
            'end_date.before_or_equal' => 'End date cannot be in the future.',
            'page.min' => 'Page number must be at least 1.',
            'page.max' => 'Page number cannot exceed 1000.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 100.',
            'sort_by.in' => 'Sort by must be one of: sessions, users, pageviews, bounce_rate, avg_session_duration.',
            'sort_direction.in' => 'Sort direction must be either asc or desc.',
            'filters.country.max' => 'Country filter must be a valid 2-character ISO code.',
            'filters.device_category.in' => 'Device category must be one of: mobile, desktop, tablet.',
            'filters.source.max' => 'Source filter cannot exceed 255 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'start_date' => 'start date',
            'end_date' => 'end date',
            'per_page' => 'items per page',
            'sort_by' => 'sort field',
            'sort_direction' => 'sort direction',
            'refresh_cache' => 'refresh cache',
            'filters.country' => 'country filter',
            'filters.device_category' => 'device category filter',
            'filters.source' => 'source filter',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set default pagination if not provided (pagination is optional)
        if (! $this->has('page')) {
            $this->merge(['page' => 1]);
        }

        if (! $this->has('per_page')) {
            $this->merge(['per_page' => 10]);
        }

        // Set default sorting if not provided (sorting is optional)
        if (! $this->has('sort_by')) {
            $this->merge(['sort_by' => 'sessions']);
        }

        if (! $this->has('sort_direction')) {
            $this->merge(['sort_direction' => 'desc']);
        }

        // Normalize data
        if ($this->has('preset')) {
            $this->merge(['preset' => trim($this->input('preset'))]);
        }

        if ($this->has('sort_direction')) {
            $this->merge(['sort_direction' => strtolower(trim($this->input('sort_direction')))]);
        }

        // Normalize preset dates - only if preset is present and not custom
        if ($this->has('preset') && $this->input('preset') !== 'custom') {
            // Remove start_date and end_date completely for non-custom presets
            $input = $this->except(['start_date', 'end_date']);
            $this->replace($input);
        }
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Additional validation logic after basic validation passes
        if ($this->preset === 'custom') {
            $startDate = $this->date('start_date');
            $endDate = $this->date('end_date');

            if ($startDate && $endDate) {
                $daysDiff = $startDate->diffInDays($endDate);

                // Limit custom date ranges to maximum 1 year
                if ($daysDiff > 365) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'end_date' => ['Custom date range cannot exceed 365 days.'],
                    ]);
                }
            }
        }
    }
}
