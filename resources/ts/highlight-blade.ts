import { HLJSApi } from 'highlight.js';

export default function (hljs: HLJSApi) {
    return {
        aliases: ['blade'],
        case_insensitive: false,
        subLanguage: 'xml',
        contains: [
            hljs.COMMENT(/\{\{--/, /--}}/),

            // output with HTML escaping
            {
                scope: 'template-variable',
                subLanguage: 'php',
                begin: /\{\{/,
                end: /}}/,
                excludeEnd: false,
            },

            // output with no HTML escaping
            {
                scope: 'template-variable',
                subLanguage: 'php',
                begin: /\{!!/,
                end: /!!}/,
                excludeEnd: false,
            },

            // directly inserted PHP code
            {
                begin: /@php/,
                beginScope: 'keyword',
                end: /@endphp/,
                endScope: 'keyword',
                subLanguage: 'php',
            },

            // blade syntax
            {
                scope: 'keyword',
                match: /@[a-zA-Z]+/,
            },

            // parameter in blade syntax
            {
                begin: /(?<=@[a-zA-Z]+\s?)\(/,
                excludeBegin: true,
                end: /\)/,
                excludeEnd: true,
                subLanguage: 'php',
            },
        ],
    };
}
