<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\AddressRequest;
use App\Http\Responses\ApiResponse;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function __construct(private readonly AddressService $service) {}

    public function index(Request $request): JsonResponse
    {
        return ApiResponse::success(
            $this->service->listForUser($request->user()),
            'Addresses retrieved successfully.',
        );
    }

    public function store(AddressRequest $request): JsonResponse
    {
        $address = $this->service->create($request->user(), $request->validated());

        return ApiResponse::created($address, 'Address saved.');
    }

    public function update(AddressRequest $request, int $id): JsonResponse
    {
        $address = $this->service->findForUser($request->user(), $id);
        if ($address === null) {
            return ApiResponse::notFound('Address not found.');
        }

        $this->service->update($request->user(), $address, $request->validated());

        return ApiResponse::success([], 'Address updated.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $address = $this->service->findForUser($request->user(), $id);
        if ($address === null) {
            return ApiResponse::notFound('Address not found.');
        }

        $this->service->delete($request->user(), $address);

        return ApiResponse::success([], 'Address deleted.');
    }

    public function setDefault(Request $request, int $id): JsonResponse
    {
        $address = $this->service->findForUser($request->user(), $id);
        if ($address === null) {
            return ApiResponse::notFound('Address not found.');
        }

        $this->service->setDefault($request->user(), $address);

        return ApiResponse::success([], 'Default address updated.');
    }
}
