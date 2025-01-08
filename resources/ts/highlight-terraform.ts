// https://github.com/highlightjs/highlightjs-terraform/blob/master/terraform.js
import { HLJSApi } from 'highlight.js';

export default function (hljs: HLJSApi) {
    const KEYWORDS = {
        keyword: [
            'resource',
            'variable',
            'provider',
            'output',
            'locals',
            'module',
            'data',
            'terraform',
            'backend',
            'for',
            'in',
            'if',
            'run',
        ],
        literal: ['true', 'false', 'null'],
        // remove the situation like data.aws_instance.main
        $pattern: /[a-zA-Z]+(?!\.)/,
    };

    // the ':' in Conditional Expressions
    // condition ? true_val : false_val
    const QUESTION_MARK_IN_EXPRESSION = {
        scope: 'keyword',
        match: /\?/,
    };

    // the ':' in Conditional Expressions
    // condition ? true_val : false_val
    const COLON_IN_EXPRESSION = {
        scope: 'keyword',
        match: /:/,
    };

    const ARROW_EXPRESSION = {
        scope: 'keyword',
        match: /=>/,
    };

    const OPERATORS = {
        scope: 'operator',
        match: /[><+\-*\/]|==|<=|>=|!=/,
    };

    // 1 or 1.2
    const NUMBERS = {
        scope: 'number',
        match: /\b\d+(\.\d+)?\b/,
    };

    // "string"
    // "string and ${variable}"
    const STRINGS = {
        scope: 'string',
        begin: /"/,
        end: /"/,
        contains: [
            {
                scope: 'subst',
                begin: /\$\{/,
                end: /}/,
            },
        ],
    };

    // somethingLikeThis(
    const FUNCTION = {
        scope: 'title.function',
        match: /[a-zA-Z0-9_]+(?=\()/,
    };

    // somethingLikeThis =
    const ATTRIBUTE = {
        scope: 'attr',
        match: /\s[a-zA-Z0-9_]+\s*(?==)/,
    };

    // somethingLikeThis {
    const BLOCK_ATTRIBUTE = {
        scope: 'keyword',
        match: /[a-zA-Z0-9_]+\s(?={)/,
    };

    const LEFT_BRACE = {
        scope: 'punctuation',
        match: /\{/,
    };

    const RIGHT_BRACE = {
        scope: 'punctuation',
        match: /}/,
    };

    const LEFT_BRACKET = {
        scope: 'punctuation',
        match: /\[/,
    };

    const RIGHT_BRACKET = {
        scope: 'punctuation',
        match: /]/,
    };

    return {
        case_insensitive: false,
        aliases: ['tf', 'hcl', 'terraform', 'opentofu'],
        keywords: KEYWORDS,
        contains: [
            hljs.COMMENT(/#/, /$/),
            QUESTION_MARK_IN_EXPRESSION,
            COLON_IN_EXPRESSION,
            ARROW_EXPRESSION,
            OPERATORS,
            NUMBERS,
            STRINGS,
            FUNCTION,
            ATTRIBUTE,
            BLOCK_ATTRIBUTE,
            LEFT_BRACE,
            RIGHT_BRACE,
            LEFT_BRACKET,
            RIGHT_BRACKET,
        ],
    };
}
