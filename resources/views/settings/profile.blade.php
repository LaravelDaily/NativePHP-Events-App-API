<x-layouts.app>
    <!-- Breadcrumbs -->
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('settings.profile.edit') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Profile') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Profile') }}</span>
    </div>

    <!-- Page Title -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Profile') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Update your name and email address') }}</p>
    </div>

    <div class="p-6">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- Sidebar Navigation -->
            @include('settings.partials.navigation')

            <!-- Profile Content -->
            <div class="flex-1">
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                    <div class="p-6">
                        <!-- Profile Form -->
                        <form class="w-full mb-10 grid grid-cols-1 md:grid-cols-2 gap-4"
                            action="{{ route('settings.profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-4">
                                <x-forms.input label="Name" name="name" type="text"
                                    value="{{ old('name', $user->name) }}" />
                            </div>

                            <div class="mb-6">
                                <x-forms.input label="Email" name="email" type="email"
                                    value="{{ old('email', $user->email) }}" />
                            </div>

                            <div class="mb-4">
                                <x-forms.input label="Phone" name="phone" type="text"
                                    value="{{ old('phone', $user->phone) }}" />
                            </div>

                            <div class="mb-4">
                                <x-forms.input label="Company" name="company" type="text"
                                    value="{{ old('company', $user->company) }}" />
                            </div>

                            <div class="mb-4">
                                <x-forms.input label="Job Title" name="job_title" type="text"
                                    value="{{ old('job_title', $user->job_title) }}" />
                            </div>

                            <div class="mb-4">
                                <x-forms.input label="Country" name="country" type="text"
                                    value="{{ old('country', $user->country) }}" />
                            </div>

                            <div class="mb-4">
                                <x-forms.input label="City" name="city" type="text"
                                    value="{{ old('city', $user->city) }}" />
                            </div>

                            <div class="mb-4 col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    {{ __('Social Media Links') }}
                                </label>

                                <div x-data="socialLinks()" class="space-y-3">
                                    <template x-for="(social, index) in socials" :key="index">
                                        <div class="flex gap-3">
                                            <div class="flex-1">
                                                <input type="text" x-model="social.title"
                                                    :name="'socials[' + index + '][title]'"
                                                    placeholder="{{ __('Title (e.g., LinkedIn, Twitter)') }}"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                            </div>
                                            <div class="flex-1">
                                                <input type="url" x-model="social.url"
                                                    :name="'socials[' + index + '][url]'" placeholder="{{ __('URL') }}"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                                            </div>
                                            <button type="button" @click="removeSocial(index)"
                                                class="px-3 py-2 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>

                                    <button type="button" @click="addSocial()"
                                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        {{ __('Add Social Link') }}
                                    </button>
                                </div>

                                <script>
                                    function socialLinks() {
                                        return {
                                            socials: [
                                                @if(old('socials'))
                                                    @foreach(old('socials') as $social)
                                                        { title: '{{ $social['title'] }}', url: '{{ $social['url'] }}' },
                                                    @endforeach
                                                @elseif($user->socials)
                                                    @foreach($user->socials as $social)
                                                        { title: '{{ $social['title'] }}', url: '{{ $social['url'] }}' },
                                                    @endforeach
                                                @else
                                                    { title: '', url: '' }
                                                @endif
                                        ],
                                            addSocial() {
                                                this.socials.push({ title: '', url: '' });
                                            },
                                            removeSocial(index) {
                                                if (this.socials.length > 1) {
                                                    this.socials.splice(index, 1);
                                                }
                                            }
                                        }
                                    }
                                </script>
                            </div>

                            <div>
                                <x-button type="primary">{{ __('Save') }}</x-button>
                            </div>
                        </form>

                        <!-- Delete Account Section -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                            <h2 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-1">
                                {{ __('Delete account') }}
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                {{ __('Delete your account and all of its resources') }}
                            </p>
                            <form action="{{ route('settings.profile.destroy') }}" method="POST"
                                onsubmit="return confirm('{{ __('Are you sure you want to delete your account?') }}')">
                                @csrf
                                @method('DELETE')
                                <x-button type="danger">{{ __('Delete account') }}</x-button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>