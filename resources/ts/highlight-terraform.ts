// https://github.com/highlightjs/highlightjs-terraform/blob/master/terraform.js
import { HLJSApi } from 'highlight.js';

export default function(hljs: HLJSApi) {
    const KWS = [
        'resource',
        'variable',
        'provider',
        'output',
        'locals',
        'module',
        'data',
        'terraform',
        'for',
        'in',
        'if'
    ];

    const LITERAL = ['true', 'false', 'null'];

    const KEYWORDS = {
        keyword: KWS,
        literal: LITERAL
    };

    const NUMBERS = {
        scope: 'number',
        begin: /\b\d+(\.\d+)?/
    };

    const STRINGS = {
        scope: 'string',
        begin: /"/,
        end: /"/,
        contains: [
            {
                scope: 'variable',
                begin: /\${/,
                end: /}/
            }
        ]
    };

    const FUNCTION = {
        scope: 'title.function',
        match: /[a-zA-Z0-9_]+(?=\()/
    };

    const ATTRIBUTE = {
        scope: 'attr',
        match: /[a-zA-Z0-9_]+\s*(?==)/
    };

    const BLOCK_ATTRIBUTE = {
        scope: 'keyword',
        match: /[a-zA-Z0-9_]+\s*(?={)/
    };

    const PARAMETER = {
        scope: 'params',
        begin: /(?<==\s)(?!true\b|false\b|null\b)(\[.*?]|[\w.]+)/
    };

    const LEFT_BRACE = {
        scope: 'punctuation',
        match: /\{/
    };

    const RIGHT_BRACE = {
        scope: 'punctuation',
        match: /}/
    };

    const LEFT_BRACKET = {
        scope: 'punctuation',
        match: /\[/
    };

    const RIGHT_BRACKET = {
        scope: 'punctuation',
        match: /]/
    };

    const EQUALS = {
        scope: 'operator',
        match: /=/
    };

    return {
        case_insensitive: false,
        aliases: ['tf', 'hcl'],
        keywords: KEYWORDS,
        contains: [
            hljs.COMMENT(/#/, /$/),
            NUMBERS,
            STRINGS,
            FUNCTION,
            ATTRIBUTE,
            BLOCK_ATTRIBUTE,
            PARAMETER,
            EQUALS,
            LEFT_BRACE,
            RIGHT_BRACE,
            LEFT_BRACKET,
            RIGHT_BRACKET
        ]
    };
}
