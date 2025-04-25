<footer
  class="mt-6 bg-gray-800 pt-4 dark:bg-gray-950"
  id="footer"
>
  <div class="justify-left m-auto flex max-w-6xl flex-wrap text-gray-800">

    {{-- Col-1 --}}
    <div class="w-1/2 p-5 sm:w-1/3">
      {{-- Title --}}
      <div class="mb-6 text-lg font-semibold uppercase text-gray-50">
        About
      </div>
      {{-- Links --}}
      <a
        class="my-3 block font-medium text-gray-400 duration-300 hover:text-gray-50"
        href="https://github.com/YilanBoy/docfunc/"
        target="_blank"
        rel="nofollow noopener noreferrer"
      >
        Website Source Code
      </a>
      <a
        class="my-3 block font-medium text-gray-400 duration-300 hover:text-gray-50"
        href="https://portfolio.docfunc.com/"
        target="_blank"
        rel="nofollow noopener noreferrer"
      >
        Author
      </a>
    </div>

    {{-- Col-2 --}}
    <div class="w-1/2 p-5 sm:w-1/3">
      {{-- Title --}}
      <div class="mb-6 text-lg font-semibold uppercase text-gray-50">
        Learning
      </div>

      {{-- Links --}}
      <a
        class="my-3 block font-medium text-gray-400 duration-300 hover:text-gray-50"
        href="https://www.freecodecamp.org/"
        target="_blank"
        rel="nofollow noopener noreferrer"
      >
        freeCodeCamp
      </a>

      <a
        class="my-3 block font-medium text-gray-400 duration-300 hover:text-gray-50"
        href="https://laracasts.com/"
        target="_blank"
        rel="nofollow noopener noreferrer"
      >
        Laracasts
      </a>

      <a
        class="my-3 block font-medium text-gray-400 duration-300 hover:text-gray-50"
        href="https://www.jetbrains.com/academy/"
        target="_blank"
        rel="nofollow noopener noreferrer"
      >
        JetBrains Academy
      </a>
    </div>

    {{-- Col-3 --}}
    <div class="w-1/2 p-5 sm:w-1/3">
      {{-- Title --}}
      <div class="mb-6 text-lg font-semibold uppercase text-gray-50">
        Special Thanks
      </div>

      {{-- Links --}}
      <a
        class="my-3 block font-medium text-gray-400 duration-300 hover:text-gray-50"
        href="https://laravel.com/"
        target="_blank"
        rel="nofollow noopener noreferrer"
      >
        Laravel
      </a>
      <a
        class="my-3 block font-medium text-gray-400 duration-300 hover:text-gray-50"
        href="https://learnku.com/laravel/"
        target="_blank"
        rel="nofollow noopener noreferrer"
      >
        Laravel China
      </a>
      <a
        class="my-3 block font-medium text-gray-400 duration-300 hover:text-gray-50"
        href="https://www.facebook.com/groups/498481680220886/"
        target="_blank"
        rel="nofollow noopener noreferrer"
      >
        Laravel Taiwan
      </a>
      <a
        class="my-3 block font-medium text-gray-400 duration-300 hover:text-gray-50"
        href="https://livewire.laravel.com/"
        target="_blank"
        rel="nofollow noopener noreferrer"
      >
        Laravel Livewire
      </a>
      <a
        class="my-3 block font-medium text-gray-400 duration-300 hover:text-gray-50"
        href="https://tailwindcss.com/"
        target="_blank"
        rel="nofollow noopener noreferrer"
      >
        Tailwind CSS
      </a>
      <a
        class="my-3 block font-medium text-gray-400 duration-300 hover:text-gray-50"
        href="https://getbootstrap.com/"
        target="_blank"
        rel="nofollow noopener noreferrer"
      >
        Bootstrap
      </a>
      <a
        class="my-3 block font-medium text-gray-400 duration-300 hover:text-gray-50"
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
    <div class="m-auto flex max-w-6xl flex-col border-t border-gray-500 px-3 pb-5 pt-5 md:flex-row">
      <div class="mb-2 flex items-center justify-center text-sm text-gray-400 md:mb-0">
        © Copyright 2020-{{ date('Y') }}. All Rights Reserved.
      </div>

      <div class="flex flex-row items-center justify-center space-x-4 md:flex-auto md:justify-end">
        <a
          class="text-2xl text-gray-400 duration-300 hover:text-gray-50"
          href="https://github.com/yilanboy/"
          aria-label="GitHub"
          target="_blank"
          rel="nofollow noopener noreferrer"
        >
          <x-icons.github class="w-6" />
        </a>
        <a
          class="text-2xl text-gray-400 duration-300 hover:text-gray-50"
          href="https://x.com/bVK1uFaMvQkDyPR/"
          aria-label="Twitter"
          target="_blank"
          rel="nofollow noopener noreferrer"
        >
          <x-icons.twitter-x class="w-6" />
        </a>
        <a
          class="text-2xl text-gray-400 duration-300 hover:text-gray-50"
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
