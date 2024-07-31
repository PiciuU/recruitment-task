<?php

namespace App\Services;

use App\PetStatusEnum;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class PetService
{
    /**
     * The base URL of the Pet Store API.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * PetService constructor.
     *
     * Initializes the base URL for the Pet Store API.
     */
    public function __construct()
    {
        $this->baseUrl = 'https://petstore.swagger.io/v2';
    }

    /**
     * Retrieve a pet by its ID.
     *
     * Sends a GET request to fetch the pet details from the API.
     *
     * @param int $id The ID of the pet to retrieve.
     * @return array The pet details if the request is successful.
     * @throws \Exception If the request fails or the pet is not found.
     */
    public function getPet(int $id)
    {
        $response = Http::get("{$this->baseUrl}/pet/{$id}");

        if ($response->successful()) {
            return $response->json();
        }

        $this->handleErrorResponse($response);
    }

    /**
     * Create a new pet.
     *
     * Sends a POST request to create a new pet record in the API.
     *
     * @param array $data The data of the pet to be created.
     * @return array The created pet details if the request is successful.
     * @throws \Exception If the request fails.
     */
    public function createPet(array $data)
    {
        $response = Http::post("{$this->baseUrl}/pet", $data);

        if ($response->successful()) {
            return $response->json();
        }

        $this->handleErrorResponse($response, "Failed to add pet");
    }

    /**
     * Update an existing pet.
     *
     * Sends a POST request to update the pet record identified by the ID.
     *
     * @param int $id The ID of the pet to update.
     * @param array $data The updated data for the pet.
     * @return array The updated pet details if the request is successful.
     * @throws \Exception If the request fails.
     */
    public function updatePet(int $id, array $data)
    {
        $response = Http::asForm()->post("{$this->baseUrl}/pet/{$id}", $data);

        if ($response->successful()) {
            return $this->getPet($id);
        }

        $this->handleErrorResponse($response, "Failed to update pet");
    }

    /**
     * Delete a pet by its ID.
     *
     * Sends a DELETE request to remove the pet record from the API.
     *
     * @param int $id The ID of the pet to delete.
     * @return bool True if the pet was successfully deleted.
     * @throws \Exception If the request fails.
     */
    public function deletePet(int $id)
    {
        $response = Http::delete("{$this->baseUrl}/pet/{$id}");

        if ($response->successful()) {
            return true;
        }

        $this->handleErrorResponse($response, "Failed to delete pet");
    }

    /**
     * Retrieve available status values for pets.
     *
     * Returns the list of status values defined in the PetStatusEnum.
     *
     * @return array An array of status values.
     */
    public function getStatusValues()
    {
        return PetStatusEnum::getValues();
    }

    /**
     * Handle API error responses.
     *
     * Throws exceptions based on the type of error returned by the API response.
     *
     * @param Response $response The API response object.
     * @param string $errorMessage Custom error message to throw in case of unexpected errors.
     * @throws \Exception
     */
    private function handleErrorResponse(Response $response, string $errorMessage = "Unexpected error")
    {
        if ($response->badRequest()) {
            throw new \Exception("Invalid ID supplied");
        } else if ($response->notFound()) {
            throw new \Exception("Pet not found");
        } else if ($response->status() === 405) {
            throw new \Exception("Invalid input");
        } else {
            throw new \Exception($errorMessage);
        }
    }
}