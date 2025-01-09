import type { HLJSApi, Language } from 'highlight.js';

export default function (hljs: HLJSApi): Language {
    // {{ $escaped ? 'true' : 'false' }}
    const ESCAPED_TEMPLATE_VARIABLE = {
        begin: /(?<begin>\{\{)/,
        beginScope: 'template-variable',
        end: /(?<end>}})/,
        endScope: 'template-variable',
        subLanguage: 'php',
    };

    // {{ $unescaped ? '<p>true</p>' : '<p>false</p>' }}
    const UNESCAPED_TEMPLATE_VARIABLE = {
        begin: /(?<begin>\{!!)/,
        beginScope: 'template-variable',
        end: /(?<end>!!})/,
        endScope: 'template-variable',
        subLanguage: 'php',
    };

    // @php
    //     $foo = 'bar';
    // @endphp
    const RAW_PHP = {
        begin: /(?<begin>@php)/,
        beginScope: 'keyword',
        end: /(?<end>@endphp)/,
        endScope: 'keyword',
        subLanguage: 'php',
    };

    // :blade-value="$phpVar"
    const BLADE_COMPONENT_ATTRIBUTE = {
        begin: /(?<=\s)(?<begin>:[\w-]+=")/,
        excludeBegin: true,
        end: /(?<end>")/,
        excludeEnd: true,
        subLanguage: 'php',
    };

    // @somethingLikeThis
    const BLADE_DIRECTIVES = {
        scope: 'keyword',
        match: /(?<match>@[a-zA-Z]+)/,
    };

    // @foreach ($list as $item)
    // or
    // @foreach($list as $item)
    const STATEMENT_AFTER_BLADE_DIRECTIVES = {
        begin: /(?<=@[a-zA-Z]+\s?)(?<begin>\()/,
        excludeBegin: true,
        end: /(?<end>\))/,
        excludeEnd: true,
        subLanguage: 'php',
    };

    return {
        aliases: ['blade'],
        case_insensitive: false,
        subLanguage: 'xml',
        contains: [
            hljs.COMMENT(/\{\{--/, /--}}/),
            ESCAPED_TEMPLATE_VARIABLE,
            UNESCAPED_TEMPLATE_VARIABLE,
            RAW_PHP,
            BLADE_COMPONENT_ATTRIBUTE,
            BLADE_DIRECTIVES,
            STATEMENT_AFTER_BLADE_DIRECTIVES,
        ],
    };
}
