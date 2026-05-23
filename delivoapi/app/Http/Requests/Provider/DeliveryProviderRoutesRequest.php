<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class DeliveryProviderRoutesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'routes' => ['present', 'array'],
            'routes.*.origin_city' => ['required', 'string', 'max:120'],
            'routes.*.destination_city' => ['required', 'string', 'max:120', 'different:routes.*.origin_city'],
            'routes.*.waypoints' => ['nullable', 'array'],
            'routes.*.waypoints.*' => ['string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'routes.*.destination_city.different' => 'Destination city must differ from the origin city.',
        ];
    }
}
