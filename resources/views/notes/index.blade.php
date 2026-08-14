<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Notes</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
            {{-- Form to Add Note --}}
            <div class="p-6 bg-white shadow sm:rounded-lg">
                <form action="{{ route('notes.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" id="title" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="body" class="block text-sm font-medium text-gray-700">Content</label>
                        <textarea name="body" id="body" rows="3" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save Note</button>
                </form>
            </div>

            {{-- Notes List --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($notes as $note)
                    <div class="p-6 bg-white shadow sm:rouneded-lg flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">{{ $note->title }}</h3>
                            <p class="mt-2 text-gray-600 whitespace-pre-line">{{ $note->body }}</p>
                        </div>
                        <div class="mt-4 flex justify-between items-center text-xs text-gray-400">
                            <span>{{ $note->created_at->diffForHumans() }}</span>
                            <form action="{{ route('notes.destroy', $note) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-6 bg-white shadow sm:rounded-lg text-center text-gray-500">
                        No notes found. Create your first note above!
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>