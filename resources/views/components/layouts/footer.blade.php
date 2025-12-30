<footer
    class="pt-4 mt-6 bg-zinc-800 dark:bg-zinc-950"
    id="footer"
>
    <div class="flex flex-wrap m-auto max-w-6xl justify-left text-zinc-800">

        {{-- Col-1 --}}
        <div class="p-5 w-1/2 sm:w-1/3">
            {{-- Title --}}
            <div class="mb-6 text-lg font-semibold uppercase text-zinc-50">
                About
            </div>
            {{-- Links --}}
            <a
                class="block my-3 font-medium duration-300 text-zinc-400 hover:text-zinc-50"
                href="https://github.com/YilanBoy/docfunc/"
                target="_blank"
                rel="nofollow noopener noreferrer"
            >
                Website Source Code
            </a>
            <a
                class="block my-3 font-medium duration-300 text-zinc-400 hover:text-zinc-50"
                href="https://portfolio.docfunc.com/"
                target="_blank"
                rel="nofollow noopener noreferrer"
            >
                Author
            </a>
        </div>

        {{-- Col-2 --}}
        <div class="p-5 w-1/2 sm:w-1/3">
            {{-- Title --}}
            <div class="mb-6 text-lg font-semibold uppercase text-zinc-50">
                Learning
            </div>

            {{-- Links --}}
            <a
                class="block my-3 font-medium duration-300 text-zinc-400 hover:text-zinc-50"
                href="https://www.freecodecamp.org/"
                target="_blank"
                rel="nofollow noopener noreferrer"
            >
                freeCodeCamp
            </a>

            <a
                class="block my-3 font-medium duration-300 text-zinc-400 hover:text-zinc-50"
                href="https://laracasts.com/"
                target="_blank"
                rel="nofollow noopener noreferrer"
            >
                Laracasts
            </a>

            <a
                class="block my-3 font-medium duration-300 text-zinc-400 hover:text-zinc-50"
                href="https://www.jetbrains.com/academy/"
                target="_blank"
                rel="nofollow noopener noreferrer"
            >
                JetBrains Academy
            </a>
        </div>

        {{-- Col-3 --}}
        <div class="p-5 w-1/2 sm:w-1/3">
            {{-- Title --}}
            <div class="mb-6 text-lg font-semibold uppercase text-zinc-50">
                Special Thanks
            </div>

            {{-- Links --}}
            <a
                class="block my-3 font-medium duration-300 text-zinc-400 hover:text-zinc-50"
                href="https://laravel.com/"
                target="_blank"
                rel="nofollow noopener noreferrer"
            >
                Laravel
            </a>
            <a
                class="block my-3 font-medium duration-300 text-zinc-400 hover:text-zinc-50"
                href="https://learnku.com/laravel/"
                target="_blank"
                rel="nofollow noopener noreferrer"
            >
                Laravel China
            </a>
            <a
                class="block my-3 font-medium duration-300 text-zinc-400 hover:text-zinc-50"
                href="https://www.facebook.com/groups/498481680220886/"
                target="_blank"
                rel="nofollow noopener noreferrer"
            >
                Laravel Taiwan
            </a>
            <a
                class="block my-3 font-medium duration-300 text-zinc-400 hover:text-zinc-50"
                href="https://livewire.laravel.com/"
                target="_blank"
                rel="nofollow noopener noreferrer"
            >
                Laravel Livewire
            </a>
            <a
                class="block my-3 font-medium duration-300 text-zinc-400 hover:text-zinc-50"
                href="https://tailwindcss.com/"
                target="_blank"
                rel="nofollow noopener noreferrer"
            >
                Tailwind CSS
            </a>
            <a
                class="block my-3 font-medium duration-300 text-zinc-400 hover:text-zinc-50"
                href="https://getbootstrap.com/"
                target="_blank"
                rel="nofollow noopener noreferrer"
            >
                Bootstrap
            </a>
            <a
                class="block my-3 font-medium duration-300 text-zinc-400 hover:text-zinc-50"
                href="https://alpinejs.dev/"
                target="_blank"
                rel="nofollow noopener noreferrer"
            >
                Alpine.js
            </a>
        </div>
    </div>

    {{-- Copyright Bar --}}
    <div class="pt-2">
        <div class="flex flex-col px-3 pt-5 pb-5 m-auto max-w-6xl border-t md:flex-row border-zinc-500">
            <div class="flex justify-center items-center mb-2 text-sm md:mb-0 text-zinc-400">
                © Copyright 2020-{{ date('Y') }}. All Rights Reserved.
            </div>

            <div class="flex flex-row justify-center items-center space-x-4 md:flex-auto md:justify-end">
                <a
                    class="text-2xl duration-300 text-zinc-400 hover:text-zinc-50"
                    href="https://github.com/yilanboy/"
                    aria-label="GitHub"
                    target="_blank"
                    rel="nofollow noopener noreferrer"
                >
                    <x-icons.github class="w-6" />
                </a>
                <a
                    class="text-2xl duration-300 text-zinc-400 hover:text-zinc-50"
                    href="https://x.com/bVK1uFaMvQkDyPR/"
                    aria-label="Twitter"
                    target="_blank"
                    rel="nofollow noopener noreferrer"
                >
                    <x-icons.twitter-x class="w-6" />
                </a>
                <a
                    class="text-2xl duration-300 text-zinc-400 hover:text-zinc-50"
                    href="https://www.facebook.com/profile.php?id=100004204543711"
                    aria-label="Facebook"
                    target="_blank"
                    rel="nofollow noopener noreferrer"
                >
                    <x-icons.facebook class="w-6" />
                </a>
            </div>
        </div>
    </div>
</footer>
