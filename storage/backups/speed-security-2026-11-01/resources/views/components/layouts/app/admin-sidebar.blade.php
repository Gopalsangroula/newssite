
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>


        <flux:navlist.group class="grid">
            <!-- Posts Dropdown -->
            <flux:navlist.group :heading="__('Posts')" icon="file-text">
                <flux:navlist.item :href="route('admin.posts.index')" :current="request()->routeIs('admin.posts.index')"
                    wire:navigate>
                    {{ __('All Posts') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('admin.posts.create')"
                    :current="request()->routeIs('admin.posts.create')" wire:navigate>
                    {{ __('Add Post') }}
                </flux:navlist.item>
            </flux:navlist.group>

            <!-- Categories Dropdown -->
            <flux:navlist.group :heading="__('Categories')" icon="layers">
                <flux:navlist.item :href="route('admin.categories.index')"
                    :current="request()->routeIs('admin.categories.index')" wire:navigate>
                    {{ __('All Categories') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('admin.categories.create')"
                    :current="request()->routeIs('admin.categories.create')" wire:navigate>
                    {{ __('Add Category') }}
                </flux:navlist.item>
            </flux:navlist.group>
            <!-- Media Dropdown -->
            <flux:navlist.group :heading="__('Media')" icon="image">
                <flux:navlist.item :href="route('admin.media.index')" :current="request()->routeIs('admin.media.index')"
                    wire:navigate>
                    {{ __('All Media') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('admin.media.create')"
                    :current="request()->routeIs('admin.media.create')" wire:navigate>
                    {{ __('Add Media') }}
                </flux:navlist.item>
            </flux:navlist.group>
            <!-- user Dropdown -->
            <flux:navlist.group :heading="__('User')" icon="image">
                <flux:navlist.item :href="route('admin.users')" :current="request()->routeIs('admin.media.user')"
                    wire:navigate>
                    {{ __('All Users') }}
                </flux:navlist.item>
                 <!-- user Media -->
                <flux:navlist.item :href="route('admin.register')"
                    :current="request()->routeIs('admin.register')" wire:navigate>
                    {{ __('Add User') }}
                </flux:navlist.item>
            </flux:navlist.group>
            <!-- Tags Dropdown -->
            <flux:navlist.group :heading="__('Tags')" icon="tag">
                <flux:navlist.item :href="route('admin.tags.index')" :current="request()->routeIs('admin.tags.index')"
                    wire:navigate>
                    {{ __('All Tags') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('admin.tags.create')" :current="request()->routeIs('admin.tags.create')"
                    wire:navigate>
                    {{ __('Add Tag') }}
                </flux:navlist.item>
            </flux:navlist.group>

            <!-- Advertisement Dropdown -->
            <flux:navlist.group :heading="__('Advertisement')" icon="message-circle">
                <flux:navlist.item :href="route('admin.advertisements.index')"
                    :current="request()->routeIs('admin.advertisements.index')" wire:navigate>
                    {{ __('All Advertisement') }}
                </flux:navlist.item>
                <flux:navlist.item :href="route('admin.advertisements.create')"
                    :current="request()->routeIs('admin.advertisements.create')" wire:navigate>
                    {{ __('Create Advertisement') }}
                </flux:navlist.item>
            </flux:navlist.group>

            <!-- Comments Dropdown -->
            <flux:navlist.group :heading="__('Comments')" icon="message-circle">
                <flux:navlist.item :href="route('admin.comments.index')"
                    :current="request()->routeIs('admin.comments.index')" wire:navigate>
                    {{ __('All Comments') }}
                </flux:navlist.item>
            </flux:navlist.group>



        </flux:navlist.group>

        <flux:spacer />

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                icon:trailing="chevrons-up-down" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @fluxScripts
    @include('partials.footer')
</body>

</html>
