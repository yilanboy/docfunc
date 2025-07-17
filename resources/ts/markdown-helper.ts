declare global {
    interface Window {
        tabToFourSpaces: (event: Event) => void;
    }
}

function tabToFourSpaces(event: Event): void {
    const markdownEditor = event.target;

    if (!(markdownEditor instanceof HTMLTextAreaElement)) {
        return;
    }

    const TAB_SPACE = '    ';
    const start = markdownEditor.selectionStart;
    const end = markdownEditor.selectionEnd;
    const value = markdownEditor.value;

    // Find line boundaries for the selection
    // If lastIndexOf finds a newline: returns the index of \n, then +1 gives us the character right after it (start of next line)
    // If lastIndexOf doesn't find a newline: returns -1, then -1 + 1 = 0 (start of the string, which is the first line)
    let lineStart = value.lastIndexOf('\n', start - 1) + 1;
    let lineEnd = value.indexOf('\n', end);
    if (lineEnd === -1) {
        lineEnd = value.length;
    }

    const lines = value.substring(lineStart, lineEnd).split('\n');
    const indentedLines = lines.map((line) => TAB_SPACE + line);

    markdownEditor.value =
        value.substring(0, lineStart) +
        indentedLines.join('\n') +
        value.substring(lineEnd);
    markdownEditor.selectionStart = start + TAB_SPACE.length;
    markdownEditor.selectionEnd = end + TAB_SPACE.length * lines.length;
}

window.tabToFourSpaces = tabToFourSpaces;
