import { ensureSyntaxTree, syntaxTree } from '@codemirror/language'

// Nodes of the HTML syntax tree that markup must not be inserted into: Tags
// with their names and attributes, comments, entities, and the raw text of
// script, style and textarea elements.
const UNSAFE_NODES = new Set([
  'OpenTag',
  'CloseTag',
  'SelfClosingTag',
  'MismatchedCloseTag',
  'IncompleteCloseTag',
  'NoMatchCloseTag',
  'Comment',
  'ProcessingInst',
  'DoctypeDecl',
  'EntityReference',
  'CharacterReference',
  'InvalidEntity',
])

// Raw text nodes: The whole element around them is skipped
const RAW_TEXT_NODES = new Set(['ScriptText', 'StyleText', 'TextareaText'])

// Elements with raw text content. With the nested JavaScript and CSS parsers,
// their content isn't a raw text node.
const RAW_TEXT_ELEMENTS = new Set(['script', 'style', 'textarea'])

/**
 * Returns whether a position is in the content of a script, style or
 * textarea element.
 *
 * @param {import('@codemirror/state').EditorState} state the editor state
 * @param {import('@lezer/common').SyntaxNode} element an Element node
 * @param {number} position the position
 * @returns {boolean} true if the position is after the opening tag of a
 *   script, style or textarea element
 */
const isInRawTextElement = (state, element, position) => {
  const openTag = element.firstChild
  const tagName = openTag?.getChild('TagName')
  return (
    !!tagName &&
    position >= openTag.to &&
    RAW_TEXT_ELEMENTS.has(
      state.sliceDoc(tagName.from, tagName.to).toLowerCase()
    )
  )
}

// Longest time in ms to wait for the parser on very long texts
const PARSE_TIMEOUT = 500

/**
 * Returns the nearest position at or after the given one where HTML can be
 * inserted without breaking existing markup: Never inside a tag name, an
 * attribute, an attribute value, a comment, an entity or the content of a
 * script, style or textarea element.
 *
 * @param {import('@codemirror/state').EditorState} state the editor state,
 *   with the HTML language
 * @param {number} position the wanted position, e.g. the cursor
 * @returns {number} the safe position
 */
export const findSafeInsertPosition = (state, position) => {
  const tree =
    ensureSyntaxTree(state, state.doc.length, PARSE_TIMEOUT) ||
    syntaxTree(state)
  let safePosition = Math.max(0, Math.min(position, state.doc.length))
  // Only nodes around the position, not those ending or starting there
  for (let node = tree.resolve(safePosition, 0); node; node = node.parent) {
    if (RAW_TEXT_NODES.has(node.name) && node.parent) {
      safePosition = Math.max(safePosition, node.parent.to)
    } else if (
      node.name === 'Element' &&
      isInRawTextElement(state, node, safePosition)
    ) {
      safePosition = Math.max(safePosition, node.to)
    } else if (UNSAFE_NODES.has(node.name)) {
      safePosition = Math.max(safePosition, node.to)
    }
  }
  return safePosition
}

/**
 * Inserts HTML into a code editor at a safe position near the given one (see
 * findSafeInsertPosition), as one step that can be undone, places the cursor
 * after it and focuses the editor.
 *
 * @param {import('@codemirror/view').EditorView} view the code editor
 * @param {string} html the HTML to insert
 * @param {number} position the wanted position, e.g. the cursor when the
 *   insert dialog was opened
 * @returns {number} the position the HTML was inserted at
 */
export const insertHtml = (view, html, position) => {
  const insertPosition = findSafeInsertPosition(view.state, position)
  view.dispatch({
    changes: { from: insertPosition, insert: html },
    selection: { anchor: insertPosition + html.length },
    scrollIntoView: true,
    // Not "input.type": Typing right before or after isn't merged into the
    // same undo step
    userEvent: 'input.paste',
  })
  view.focus()
  return insertPosition
}
