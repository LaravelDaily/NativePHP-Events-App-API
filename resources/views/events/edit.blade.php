<x-layouts.app>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Edit Event') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Update event details and talks') }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <form method="POST" action="{{ route('events.update', $event) }}" id="eventForm">
            @csrf
            @method('PUT')

            <!-- Event Details -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('Event Details') }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Event Title') }} *
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title', $event->title) }}"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                            required
                        >
                        @error('title')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Location') }} *
                        </label>
                        <input
                            type="text"
                            id="location"
                            name="location"
                            value="{{ old('location', $event->location) }}"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                            required
                        >
                        @error('location')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="start_datetime" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('Start Date & Time') }} *
                        </label>
                        <input
                            type="datetime-local"
                            id="start_datetime"
                            name="start_datetime"
                            value="{{ old('start_datetime', $event->start_datetime->format('Y-m-d\TH:i')) }}"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                            required
                        >
                        @error('start_datetime')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_datetime" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('End Date & Time') }} *
                        </label>
                        <input
                            type="datetime-local"
                            id="end_datetime"
                            name="end_datetime"
                            value="{{ old('end_datetime', $event->end_datetime->format('Y-m-d\TH:i')) }}"
                            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                            required
                        >
                        @error('end_datetime')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Description') }} *
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                        required
                    >{{ old('description', $event->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Talks Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('Talks') }}</h2>
                    <button
                        type="button"
                        id="addTalkBtn"
                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-md transition-colors duration-200"
                    >
                        {{ __('Add Talk') }}
                    </button>
                </div>

                <div id="talksContainer" class="space-y-6">
                    @foreach($event->talks as $index => $talk)
                        <div class="talk-item bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border border-gray-200 dark:border-gray-600">
                            <input type="hidden" name="talks[{{ $index }}][id]" value="{{ $talk->id }}">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-md font-medium text-gray-800 dark:text-gray-100">{{ __('Talk') }} {{ $index + 1 }}</h3>
                                <button
                                    type="button"
                                    class="remove-talk-btn text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('Talk Title') }} *
                                    </label>
                                    <input
                                        type="text"
                                        name="talks[{{ $index }}][title]"
                                        value="{{ old("talks.{$index}.title", $talk->title) }}"
                                        class="talk-title block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('Speaker Name') }} *
                                    </label>
                                    <input
                                        type="text"
                                        name="talks[{{ $index }}][speaker_name]"
                                        value="{{ old("talks.{$index}.speaker_name", $talk->speaker_name) }}"
                                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('Start Time') }} *
                                    </label>
                                    <input
                                        type="datetime-local"
                                        name="talks[{{ $index }}][start_time]"
                                        value="{{ old("talks.{$index}.start_time", $talk->start_time->format('Y-m-d\TH:i')) }}"
                                        class="talk-start-time block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                                        required
                                    >
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('End Time') }} *
                                    </label>
                                    <input
                                        type="datetime-local"
                                        name="talks[{{ $index }}][end_time]"
                                        value="{{ old("talks.{$index}.end_time", $talk->end_time->format('Y-m-d\TH:i')) }}"
                                        class="talk-end-time block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                                        required
                                    >
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('Talk Description') }} *
                                </label>
                                <textarea
                                    name="talks[{{ $index }}][description]"
                                    rows="3"
                                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                                    required
                                >{{ old("talks.{$index}.description", $talk->description) }}</textarea>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4">
                <a
                    href="{{ route('events.show', $event) }}"
                    class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium rounded-md transition-colors duration-200"
                >
                    {{ __('Cancel') }}
                </a>
                <button
                    type="submit"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors duration-200"
                >
                    {{ __('Update Event') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Talk Template (hidden) -->
    <template id="talkTemplate">
        <div class="talk-item bg-gray-50 dark:bg-gray-700 rounded-lg p-6 border border-gray-200 dark:border-gray-600">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-md font-medium text-gray-800 dark:text-gray-100">{{ __('Talk') }} <span class="talk-number"></span></h3>
                <button
                    type="button"
                    class="remove-talk-btn text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Talk Title') }} *
                    </label>
                    <input
                        type="text"
                        name="talks[TALK_INDEX][title]"
                        class="talk-title block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                        required
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Speaker Name') }} *
                    </label>
                    <input
                        type="text"
                        name="talks[TALK_INDEX][speaker_name]"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                        required
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Start Time') }} *
                    </label>
                    <input
                        type="datetime-local"
                        name="talks[TALK_INDEX][start_time]"
                        class="talk-start-time block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                        required
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('End Time') }} *
                    </label>
                    <input
                        type="datetime-local"
                        name="talks[TALK_INDEX][end_time]"
                        class="talk-end-time block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                        required
                    >
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    {{ __('Talk Description') }} *
                </label>
                <textarea
                    name="talks[TALK_INDEX][description]"
                    rows="3"
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400"
                    required
                ></textarea>
            </div>
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let talkIndex = {{ $event->talks->count() }};
            const addTalkBtn = document.getElementById('addTalkBtn');
            const talksContainer = document.getElementById('talksContainer');
            const talkTemplate = document.getElementById('talkTemplate');

            // Add remove functionality to existing talks
            document.querySelectorAll('.remove-talk-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.talk-item').remove();
                    updateTalkNumbers();
                });
            });

            addTalkBtn.addEventListener('click', function() {
                const talkItem = talkTemplate.content.cloneNode(true);
                
                // Update all input names with the current index
                const inputs = talkItem.querySelectorAll('input, textarea');
                inputs.forEach(input => {
                    input.name = input.name.replace('TALK_INDEX', talkIndex);
                });

                // Update talk number
                const talkNumber = talkItem.querySelector('.talk-number');
                talkNumber.textContent = talkIndex + 1;

                // Add remove functionality
                const removeBtn = talkItem.querySelector('.remove-talk-btn');
                removeBtn.addEventListener('click', function() {
                    this.closest('.talk-item').remove();
                    updateTalkNumbers();
                });

                talksContainer.appendChild(talkItem);
                talkIndex++;
            });

            function updateTalkNumbers() {
                const talkItems = talksContainer.querySelectorAll('.talk-item');
                talkItems.forEach((item, index) => {
                    const talkNumber = item.querySelector('.talk-number');
                    if (talkNumber) {
                        talkNumber.textContent = index + 1;
                    }
                });
            }

            // Auto-set end time when start time changes
            talksContainer.addEventListener('change', function(e) {
                if (e.target.classList.contains('talk-start-time')) {
                    const talkItem = e.target.closest('.talk-item');
                    const startTime = e.target.value;
                    const endTimeInput = talkItem.querySelector('.talk-end-time');
                    
                    if (startTime) {
                        // Set end time to 1 hour after start time
                        const startDate = new Date(startTime);
                        const endDate = new Date(startDate.getTime() + 60 * 60 * 1000); // Add 1 hour
                        endTimeInput.value = endDate.toISOString().slice(0, 16);
                    }
                }
            });
        });
    </script>
</x-layouts.app> 