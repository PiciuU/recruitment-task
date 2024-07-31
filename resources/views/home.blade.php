@extends('layouts.default')
@section('title', 'Pet Store')
@section('content')
    <main class="bg-gray-100 flex flex-col items-center min-h-screen p-6">

        <div class="container mx-auto mt-5 p-6 bg-white shadow-md rounded">
            <div class="bg-yellow-300 text-black text-center p-4 font-bold w-full z-50 mb-8 rounded">
                This application utilizes publicly available API; hence, data retrieved may be subject to frequent updates and changes.
            </div>
            <!-- Search Pet by ID -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold mb-4">Search Pet by ID</h1>
                <form id="search_pet_form" action="{{ route('pets.search') }}" method="POST">
                    @csrf
                    <div class="flex items-center">
                        <input type="number" id="pet_id" name="id" placeholder="Enter Pet ID"  value="{{ session('searched_pet_id') }}" class="border p-2 rounded w-1/2 mr-2" required>
                        <button type="submit" class="bg-blue-500 text-white ml-4 px-4 py-2 rounded">Search</button>
                    </div>
                </form>
            </div>

            <!-- Add Pet Button -->
            <div>
                <button id="open_modal_button" class="bg-green-500 text-white px-4 py-2 rounded">Add Pet</button>
            </div>

            <!-- Error handling -->
            @php
                $alertTypes = [
                    'success' => 'bg-green-100 text-green-800',
                    'error' => 'bg-red-100 text-red-800',
                ];
            @endphp

            @foreach ($alertTypes as $type => $classes)
                @if (session($type))
                    <div class="mt-6 {{ $classes }} p-4 rounded">
                        {{ session($type) }}
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Add Pet Modal -->
        <div id="add_pet_modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 {{ $showModal === 'add' ? '' : 'hidden' }}">

            <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg max-h-[100vh] overflow-y-auto">
                <h2 class="text-2xl font-bold mb-4">Add New Pet</h2>

                <form id="add_pet_form" action="{{ route('pets.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="id" class="block text-gray-700">ID:</label>
                        <input type="number" id="id" name="id" required value="{{ old('id', 1) }}" min="1" class="border p-2 rounded w-full @error('id') border-red-500 @enderror" placeholder="Enter Pet ID">
                        @error('id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="name" class="block text-gray-700">Name:</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="border p-2 rounded w-full @error('name') border-red-500 @enderror" placeholder="Enter Pet Name">
                        @error('name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="category_id" class="block text-gray-700">Category ID:</label>
                        <input type="number" id="category_id" name="category_id" value="{{ old('category_id') }}" class="border p-2 rounded w-full @error('category_id') border-red-500 @enderror" placeholder="Enter Category ID">
                        @error('category_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="category_name" class="block text-gray-700">Category Name:</label>
                        <input type="text" id="category_name" name="category_name" value="{{ old('category_name') }}" class="border p-2 rounded w-full @error('category_name') border-red-500 @enderror" placeholder="Enter Category Name">
                        @error('category_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="photoUrls" class="block text-gray-700">Photo URLs:</label>
                        <div id="photoUrlsContainer">
                            @if(old('photoUrls'))
                                @foreach(old('photoUrls') as $index => $photoUrl)
                                    <input type="text" name="photoUrls[]" value="{{ $photoUrl }}" class="border p-2 rounded w-full mb-2">
                                @endforeach
                            @else
                                <input type="text" id="photoUrls" name="photoUrls[]" class="border p-2 rounded w-full mb-2" placeholder="Enter Photo URL">
                            @endif
                        </div>
                        @error('photoUrls.*')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <button type="button" id="addPhotoUrl" class="bg-blue-500 text-white px-4 py-2 rounded">Add More Photos</button>
                    </div>

                    <div class="mb-4">
                        <label for="tags" class="block text-gray-700">Tags:</label>
                        <div id="tagsContainer">
                            @if(old('tags'))
                                @foreach(old('tags') as $index => $tag)
                                    <input type="text" name="tags[{{ $index }}][name]" value="{{ $tag['name'] }}" class="border p-2 rounded w-full mb-2">
                                @endforeach
                            @else
                                <input type="text" id="tags" name="tags[0][name]" class="border p-2 rounded w-full mb-2" placeholder="Enter Tag Name">
                            @endif
                        </div>
                        @error('tags.*.name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <button type="button" id="addTag" class="bg-blue-500 text-white px-4 py-2 rounded">Add More Tags</button>
                    </div>

                    <div class="mb-4">
                        <label for="status" class="block text-gray-700">Status:</label>
                        <select id="status" name="status" required class="border p-2 rounded w-full @error('status') border-red-500 @enderror">
                            @foreach($statusValues as $status)
                                <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        @error('status')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded mr-2">Add Pet</button>
                        <button type="button" id="close_modal_button" class="bg-red-500 text-white px-4 py-2 rounded">Close</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Display Pet Information -->
        @if($pet = session('pet'))
            <div class="container mx-auto mt-5 p-4 bg-white shadow-md rounded">
                <div id="pet_details" class="mb-4 p-4 border rounded">
                    <h2 class="text-xl font-semibold mb-4">Pet Details (ID: {{ $pet['id'] }})</h2>

                    <div class="mb-4">
                        <strong class="text-gray-700">Name:</strong>
                        <p class="text-gray-900">
                            @empty($pet['name'])
                                <p class="text-gray-500">Pet name is not available.</p>
                            @else
                                {{ $pet['name'] }}
                            @endempty
                        </p>
                    </div>

                    <div class="mb-4">
                        <strong class="text-gray-700">Status:</strong>
                        <p class="text-gray-900">
                            @empty($pet['status'])
                                <p class="text-gray-500">Status information is not available.</p>
                            @else
                                {{ $pet['status'] }}
                            @endempty
                        </p>
                    </div>

                    <div class="mb-4">
                        <strong class="text-gray-700">Category:</strong>
                        @if(optional($pet)['category'])
                            <p class="text-gray-900">ID:
                                @empty($pet['category']['id'])
                                    0
                                @else
                                    {{ $pet['category']['id'] }}
                                @endempty
                            </p>
                            <p class="text-gray-900">Name:
                                @empty($pet['category']['name'])
                                    N/A
                                @else
                                    {{ $pet['category']['name'] }}
                                @endempty
                            </p>
                        @else
                            <p class="text-gray-500">Category information is not available.</p>
                        @endif
                    </div>

                    <div class="mb-4">
                        <strong class="text-gray-700">Tags:</strong>
                        @if(optional($pet)['tags'])
                            <ul class="list-disc pl-5">
                                @foreach($pet['tags'] as $tag)
                                    <li class="text-gray-900">ID: {{ $tag['id'] }} - Name:
                                        @empty($tag['name'])
                                            N/A
                                        @else
                                            {{ $tag['name'] }}
                                        @endempty
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500">Tags information is not available.</p>
                        @endif
                    </div>

                    <div class="mb-4">
                        <strong class="text-gray-700">Photo URLs:</strong>
                        @if(optional($pet)['photoUrls'])
                            <ul class="list-disc pl-5">
                                @foreach(optional($pet)['photoUrls'] as $photoUrl)
                                    <li class="mb-2">
                                        @empty($photoUrl)
                                            <p class="text-gray-500">Photo information is not available.</p>
                                        @else
                                            {{ $photoUrl }}
                                        @endempty
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500">No photos found.</p>
                        @endif
                    </div>

                    <div class="flex space-x-4">
                        <button id="edit_button" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Edit</button>
                        <button id="delete_button" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Delete</button>

                        <form id="delete_pet_form" action="{{ route('pets.destroy', $pet['id']) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Pet Modal -->
            <div id="edit_pet_modal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 {{ $showModal === 'edit' ? '' : 'hidden' }}">
                <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg max-h-[90vh] overflow-y-auto">
                    <h2 class="text-2xl font-bold mb-4">Edit Pet (ID: {{ $pet['id'] }})</h2>

                    <form id="edit_pet_form" action="{{ route('pets.update', $pet['id']) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="edit_name" class="block text-gray-700">Name:</label>
                            <input type="text" id="edit_name" name="name" value="{{ old('name', $pet['name'] ?? '') }}" class="border p-2 rounded w-full" placeholder="Enter Pet Name">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="edit_status" class="block text-gray-700">Status:</label>
                            <select id="edit_status" name="status" class="border p-2 rounded w-full" required>
                                @foreach($statusValues as $status)
                                    <option value="{{ $status }}" {{ old('status', $pet['status'] ?? '') == $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-between">
                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Save Changes</button>
                            <button type="button" id="close_edit_modal_button" class="bg-red-500 text-white px-4 py-2 rounded">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </main>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Open add modal
            $('#open_modal_button').click(function() {
                $('#add_pet_modal').removeClass('hidden');
            });

            // Close add modal
            $('#close_modal_button').click(function() {
                $('#add_pet_modal').addClass('hidden');
            });

            // Open edit Modal
            $('#edit_button').click(function() {
                $('#edit_pet_modal').removeClass('hidden');
            });

            // Close edit Modal
            $('#close_edit_modal_button').click(function() {
                $('#edit_pet_modal').addClass('hidden');
            });

            // Delete pet action
            $('#delete_button').click(function() {
                if (confirm('Are you sure you want to delete this pet?')) {
                    $('#delete_pet_form').submit();
                }
            });

            // Add more photo URL fields
            $('#addPhotoUrl').click(function() {
                $('#photoUrlsContainer').append('<input type="text" name="photoUrls[]" class="border p-2 rounded w-full mb-2" placeholder="Enter Photo URL">');
            });

            // Add more tag fields
            $('#addTag').click(function() {
                var index = $('#tagsContainer input').length;
                $('#tagsContainer').append('<input type="text" name="tags[' + index + '][name]" class="border p-2 rounded w-full mb-2" placeholder="Enter Tag Name">');
            });
        });
    </script>
@endsection