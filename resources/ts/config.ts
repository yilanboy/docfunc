// use Tailwind CSS class names
export const label = {
    BASE_CLASS_NAME: [
        'rounded-md',
        'h-8',
        'flex',
        'items-center',
        'justify-center',
        'text-zinc-50',
        'bg-emerald-500',
        'dark:bg-gray-500',
        'text-sm',
        'font-semibold',
        'font-jetbrains-mono',
        'px-2',
        'opacity-100',
        'lg:opacity-0',
        'lg:group-hover:opacity-100',
        'transition-all',
        'duration-200',
    ],
};

type LanguagesSetting = {
    label: string;
    backgroundColor: string;
    color: string;
};

export const languageSettings: { [Name: string]: LanguagesSetting } = {
    text: {
        label: 'Text',
        backgroundColor: 'white',
        color: 'black',
    }, // The default language.
    bash: {
        label: 'Bash',
        backgroundColor: '#e95420',
        color: 'white',
    },
    blade: {
        label: 'Blade',
        backgroundColor: '#ff2d20',
        color: 'white',
    },
    c: {
        label: 'C',
        backgroundColor: '#00599d',
        color: 'white',
    },
    cs: {
        label: 'C#',
        backgroundColor: '#39008d',
        color: 'white',
    },
    cpp: {
        label: 'C++',
        backgroundColor: '#00599d',
        color: 'white',
    },
    css: {
        label: 'CSS',
        backgroundColor: '#2965f1',
        color: 'white',
    },
    dart: {
        label: 'Dart',
        backgroundColor: '#2bb6f5',
        color: 'white',
    },
    docker: {
        label: 'Docker',
        backgroundColor: '#1d5ee9',
        color: 'white',
    },
    go: {
        label: 'Go',
        backgroundColor: '#00a9d2',
        color: 'white',
    },
    hcl: {
        label: 'HCL',
        backgroundColor: '#5f3add',
        color: 'white',
    },
    html: {
        label: 'HTML',
        backgroundColor: '#dd4b25',
        color: 'white',
    },
    ini: {
        label: 'INI',
        backgroundColor: 'white',
        color: 'black',
    },
    java: {
        label: 'Java',
        backgroundColor: '#f89820',
        color: 'white',
    },
    javascript: {
        label: 'JavaScript',
        backgroundColor: '#f0db4f',
        color: '#323330',
    },
    json: {
        label: 'JSON',
        backgroundColor: '#f0db4f',
        color: '#323330',
    },
    kotlin: {
        label: 'Kotlin',
        backgroundColor: '#b125ea',
        color: 'white',
    },
    nginx: {
        label: 'Nginx',
        backgroundColor: '#009900',
        color: 'white',
    },
    php: {
        label: 'PHP',
        backgroundColor: '#787cb5',
        color: 'white',
    },
    python: {
        label: 'Python',
        backgroundColor: '#ffd43b',
        color: '#646464',
    },
    ruby: {
        label: 'Ruby',
        backgroundColor: '#d30001',
        color: 'white',
    },
    rust: {
        label: 'Rust',
        backgroundColor: '#ce412b',
        color: 'white',
    },
    shell: {
        label: 'Shell',
        backgroundColor: '#e95420',
        color: 'white',
    },
    svelte: {
        label: 'Svelte',
        backgroundColor: '#f73c00',
        color: 'white',
    },
    sql: {
        label: 'SQL',
        backgroundColor: '#2d99d6',
        color: 'white',
    },
    swift: {
        label: 'Swift',
        backgroundColor: '#f05138',
        color: 'white',
    },
    toml: {
        label: 'TOML',
        backgroundColor: 'white',
        color: 'black',
    },
    typescript: {
        label: 'TypeScript',
        backgroundColor: '#007acc',
        color: 'white',
    },
    xml: {
        label: 'XML',
        backgroundColor: 'white',
        color: 'black',
    },
    yaml: {
        label: 'YAML',
        backgroundColor: 'white',
        color: 'black',
    },
};

export const button = {
    BASE_CLASS_NAME: [
        'size-8',
        'flex',
        'justify-center',
        'items-center',
        'text-zinc-50',
        'bg-emerald-500',
        'dark:bg-lividus-600',
        'rounded-md',
        'text-lg',
        'hover:bg-emerald-600',
        'dark:hover:bg-lividus-500',
        'active:bg-emerald-500',
        'dark:active:bg-lividus-500',
        'opacity-100',
        'lg:opacity-0',
        'lg:group-hover:opacity-100',
        'transition-all',
        'duration-200',
        'cursor-pointer',
    ],
};

export const icon = {
    ARROWS_ANGLE_EXPAND: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrows-angle-expand" viewBox="0 0 16 16">
    <path fill-rule="evenodd" d="M5.828 10.172a.5.5 0 0 0-.707 0l-4.096 4.096V11.5a.5.5 0 0 0-1 0v3.975a.5.5 0 0 0 .5.5H4.5a.5.5 0 0 0 0-1H1.732l4.096-4.096a.5.5 0 0 0 0-.707m4.344-4.344a.5.5 0 0 0 .707 0l4.096-4.096V4.5a.5.5 0 1 0 1 0V.525a.5.5 0 0 0-.5-.5H11.5a.5.5 0 0 0 0 1h2.768l-4.096 4.096a.5.5 0 0 0 0 .707"/>
</svg>`,
    CHECK: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
    <path d="M12.736 3.97a.733.733 0 0 1 1.047 0c.286.289.29.756.01 1.05L7.88 12.01a.733.733 0 0 1-1.065.02L3.217 8.384a.757.757 0 0 1 0-1.06.733.733 0 0 1 1.047 0l3.052 3.093 5.4-6.425z"/>
</svg>`,
    CLIPBOARD: `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M10 1.5a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5zm-5 0A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5v1A1.5 1.5 0 0 1 9.5 4h-3A1.5 1.5 0 0 1 5 2.5zm-2 0h1v1A2.5 2.5 0 0 0 6.5 5h3A2.5 2.5 0 0 0 12 2.5v-1h1a2 2 0 0 1 2 2V14a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V3.5a2 2 0 0 1 2-2"/>
</svg>`,
};
