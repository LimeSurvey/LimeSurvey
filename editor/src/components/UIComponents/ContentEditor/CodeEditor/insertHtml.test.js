import { EditorState } from '@codemirror/state'
import { EditorView } from '@codemirror/view'
import { history, undo } from '@codemirror/commands'

import { htmlExtensions } from './CodeEditor'
import { findSafeInsertPosition, insertHtml } from './insertHtml'

const DOC =
  '<p class="intro" title="a b">Hello &amp; welcome</p>' +
  '<!-- note --><script>var a = "<b>"</script><br/>End'

const createState = (doc = DOC) =>
  EditorState.create({ doc, extensions: htmlExtensions })

// Position right after the first occurrence of the text
const after = (text, doc = DOC) => doc.indexOf(text) + text.length

describe('findSafeInsertPosition', () => {
  test.each([
    ['in a tag name', '<p', '<p class="intro" title="a b">'],
    ['in an attribute name', '<p cla', '<p class="intro" title="a b">'],
    ['in an attribute value', 'class="in', '<p class="intro" title="a b">'],
    ['in a closing tag', 'welcome</', 'welcome</p>'],
    ['in an entity', 'Hello &am', 'Hello &amp;'],
    ['in a comment', '<!-- no', '<!-- note -->'],
    ['in a script', 'var a', '</script>'],
    ['in a self-closing tag', '<br', '<br/>'],
  ])('moves out of the markup when the cursor is %s', (_, cursorAfter, end) => {
    expect(findSafeInsertPosition(createState(), after(cursorAfter))).toBe(
      after(end)
    )
  })

  test.each([
    ['in text', 'Hello'],
    ['between two tags', '<!-- note -->'],
    ['at the end', 'End'],
  ])('keeps the cursor position %s', (_, cursorAfter) => {
    const position = after(cursorAfter)
    expect(findSafeInsertPosition(createState(), position)).toBe(position)
  })

  test('keeps the start of the text', () => {
    expect(findSafeInsertPosition(createState(), 0)).toBe(0)
  })
})

describe('insertHtml', () => {
  test('inserts after the tag, as one step that can be undone', () => {
    const view = new EditorView({
      state: EditorState.create({
        doc: DOC,
        extensions: [...htmlExtensions, history()],
      }),
    })
    const image = '<img src="/upload/a.png" alt="A" width="10" height="5">'

    const position = insertHtml(view, image, after('class="in'))

    expect(position).toBe(after('<p class="intro" title="a b">'))
    expect(view.state.doc.toString()).toBe(
      DOC.slice(0, position) + image + DOC.slice(position)
    )
    expect(view.state.selection.main.head).toBe(position + image.length)

    undo(view)
    expect(view.state.doc.toString()).toBe(DOC)
    view.destroy()
  })
})
