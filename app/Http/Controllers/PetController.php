<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PetService;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PetController extends Controller
{
    /**
     * The service instance for interacting with pet-related operations.
     *
     * @var PetService
     */
    protected $petService;

    /**
     * PetController constructor.
     *
     * Initializes the PetService instance.
     *
     * @param PetService $petService The service for pet-related operations.
     */
    public function __construct(PetService $petService)
    {
        $this->petService = $petService;
    }

    /**
     * Display the main view with status values and modal information.
     *
     * Retrieves status values from the PetService and checks for any modal visibility settings
     * from the session. Passes these values to the view.
     *
     * @return \Illuminate\View\View The view with status values and modal visibility.
     */
    public function index()
    {
        $statusValues = $this->petService->getStatusValues();

        $showModal = Session::get('show_modal', null);
        Session::forget('show_modal');

        return view('home', compact('statusValues', 'showModal'));
    }

    /**
     * Handle the request to show pet details by ID.
     *
     * Retrieves the pet details using the PetService and redirects back with the pet information.
     * If an error occurs, redirects back with an error message.
     *
     * @param Request $request The incoming request containing the pet ID.
     * @return \Illuminate\Http\RedirectResponse Redirects back with pet details or an error message.
     */
    public function show(Request $request)
    {
        try {
            $id = intval($request->get('id'));
            $pet = $this->petService->getPet($id);
            return redirect()->back()->with(['pet' => $pet, 'searched_pet_id' => $id]);
        } catch (\Exception $e) {
            return redirect()->back()->with(['error' => $e->getMessage(), 'searched_pet_id' => $id]);
        }
    }

    /**
     * Store a new pet record.
     *
     * Validates the incoming request data and creates a new pet using the PetService.
     * Redirects back with success or error messages based on the outcome.
     *
     * @param Request $request The incoming request containing pet data.
     * @return \Illuminate\Http\RedirectResponse Redirects back with success or error messages.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'name' => 'required|string',
            'category_id' => 'nullable|integer',
            'category_name' => 'nullable|string',
            'photoUrls.*' => 'nullable|url',
            'tags.*.name' => 'nullable|string',
            'status' => ['required', 'string', 'in:' . implode(',', $this->petService->getStatusValues())],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('show_modal', 'add');
        }

        $validated = $validator->validated();

        $data = [
            'id' => $validated['id'],
            'name' => $validated['name'],
            'photoUrls' => array_filter($validated['photoUrls'] ?? []),
            'status' => $validated['status'],
        ];

        if (!empty($validated['category_id']) && !empty($validated['category_name'])) {
            $data['category'] = [
                'id' => $validated['category_id'],
                'name' => $validated['category_name'],
            ];
        }

        if (!empty($validated['tags'])) {
            $data['tags'] = array_map(function($index, $tag) {
                return !empty($tag['name']) ? ['id' => $index + 1, 'name' => $tag['name']] : null;
            }, array_keys($validated['tags']), $validated['tags']);

            $data['tags'] = array_filter($data['tags']);
        }

        try {
            $pet = $this->petService->createPet($data);
            return redirect()->back()->with(['success' => 'Pet created successfully!', 'pet' => $pet, 'searched_pet_id' => $pet['id']]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update an existing pet record by ID.
     *
     * Validates the incoming request data and updates the pet using the PetService.
     * Handles validation errors and redirects back with appropriate messages.
     *
     * @param Request $request The incoming request containing the pet ID and updated data.
     * @param int $id The ID of the pet to update.
     * @return \Illuminate\Http\RedirectResponse Redirects back with success or error messages.
     */
    public function update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'status' => ['required', 'string', 'in:' . implode(',', $this->petService->getStatusValues())],
        ]);

        if ($validator->fails()) {
            try {
                $pet = $this->petService->getPet($id);
                return redirect()->back()->withErrors($validator)->withInput()->with(['searched_pet_id' => $id, 'pet' => $pet, 'show_modal' => 'edit']);
            } catch(\Exception $e) {
                return redirect()->back()->with(['error' => $e->getMessage(), 'searched_pet_id' => $id]);
            }
        }

        $data = $request->only(['name', 'status']);

        try {
            $pet = $this->petService->updatePet($id, $data);
            return redirect()->back()->with(['success' => 'Pet updated successfully!', 'pet' => $pet, 'searched_pet_id' => $id]);
        } catch (\Exception $e) {
            try {
                $pet = $this->petService->getPet($id);
                return redirect()->back()->withInput()->with(['error' => $e->getMessage(), 'searched_pet_id' => $id, 'pet' => $pet]);
            } catch(\Exception $exception) {
                return redirect()->back()->with(['error' => $e->getMessage(), 'searched_pet_id' => $id]);
            }
        }
    }

    /**
     * Delete a pet by its ID.
     *
     * Attempts to delete the pet using the PetService. Redirects back with success or error
     * messages based on the outcome.
     *
     * @param int $id The ID of the pet to delete.
     * @return \Illuminate\Http\RedirectResponse Redirects back with success or error messages.
     */
    public function destroy(int $id)
    {
        try {
            $this->petService->deletePet($id);
            return redirect()->back()->with('success', 'Pet deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
