<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Currency;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $currency = $this->input('currency_id');
        $travelers = $this->input('travelers');

        $normalizedTravelers = $travelers;

        if (is_array($travelers)) {
            $normalizedTravelers = array_map(
                static function (mixed $traveler): mixed {
                    if (! is_array($traveler)) {
                        return $traveler;
                    }

                    $fullName = $traveler['full_name'] ?? null;
                    $dateOfBirth = $traveler['date_of_birth'] ?? null;

                    if (is_string($fullName)) {
                        $traveler['full_name'] = trim($fullName);
                    }

                    if (is_string($dateOfBirth)) {
                        $traveler['date_of_birth'] = trim($dateOfBirth);
                    }

                    return $traveler;
                },
                $travelers,
            );
        }

        $this->merge([
            'currency_id' => is_string($currency)
                ? strtoupper(trim($currency))
                : $currency,

            'start_date' => is_string($this->input('start_date'))
                ? trim($this->input('start_date'))
                : $this->input('start_date'),

            'end_date' => is_string($this->input('end_date'))
                ? trim($this->input('end_date'))
                : $this->input('end_date'),

            'travelers' => $normalizedTravelers,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'currency_id' => [
                'required',
                Rule::enum(Currency::class),
            ],

            'start_date' => [
                'required',
                'date_format:Y-m-d',
            ],

            'end_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:start_date',
            ],

            'travelers' => [
                'required',
                'array',
                'min:1',
                'max:20',
            ],

            'travelers.*' => [
                'required',
                'array',
            ],

            'travelers.*.full_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],

            'travelers.*.date_of_birth' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:start_date',
            ],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $startDateValue = $this->input('start_date');

                if (! is_string($startDateValue)) {
                    return;
                }

                $startDate = CarbonImmutable::createFromFormat(
                    'Y-m-d',
                    $startDateValue,
                )->startOfDay();

                $travelers = $this->input('travelers', []);

                if (! is_array($travelers)) {
                    return;
                }

                foreach ($travelers as $index => $traveler) {
                    if (! is_array($traveler)) {
                        continue;
                    }

                    $dateOfBirthValue =
                        $traveler['date_of_birth'] ?? null;

                    if (! is_string($dateOfBirthValue)) {
                        continue;
                    }

                    $dateOfBirth = CarbonImmutable::createFromFormat(
                        'Y-m-d',
                        $dateOfBirthValue,
                    )->startOfDay();

                    $age = (int) $dateOfBirth->diffInYears(
                        $startDate,
                        absolute: true,
                    );

                    if ($age < 18 || $age > 70) {
                        $validator->errors()->add(
                            "travelers.{$index}.date_of_birth",
                            'The traveler must be between 18 and 70 years old on the trip start date.',
                        );
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'currency_id.required' =>
            'The currency is required.',

            'currency_id.enum' =>
            'The currency must be EUR, GBP, or USD.',

            'start_date.required' =>
            'The trip start date is required.',

            'start_date.date_format' =>
            'The trip start date must use YYYY-MM-DD format.',

            'end_date.required' =>
            'The trip end date is required.',

            'end_date.date_format' =>
            'The trip end date must use YYYY-MM-DD format.',

            'end_date.after_or_equal' =>
            'The trip end date cannot be before the start date.',

            'travelers.required' =>
            'At least one traveler is required.',

            'travelers.array' =>
            'Travelers must be provided as an array.',

            'travelers.min' =>
            'At least one traveler is required.',

            'travelers.max' =>
            'A quotation may contain no more than 20 travelers.',

            'travelers.*.full_name.required' =>
            'Each traveler must have a full name.',

            'travelers.*.date_of_birth.required' =>
            'Each traveler must have a date of birth.',

            'travelers.*.date_of_birth.before_or_equal' =>
            'A traveler date of birth cannot be after the trip start date.',
        ];
    }
}
