import { RichTextPlugin as LexicalRichTextPlugin } from '@lexical/react/LexicalRichTextPlugin'
import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext'
import { ContentEditable } from '@lexical/react/LexicalContentEditable'
import { RemoveHTMLTagsInString } from 'helpers'

export const RichTextPlugin = ({ value, placeholder }) => {
  const [editor] = useLexicalComposerContext()
  const valueWithoutHTML = RemoveHTMLTagsInString(value)

  return (
    <LexicalRichTextPlugin
      placeholder={
        <h2
          style={{
            position: 'absolute',
            color: '#6e748c',
            left: '4px',
            top: '2px',
            width: '350px',
          }}
          data-placeholder={valueWithoutHTML ? '' : placeholder}
          onClick={() => editor.focus()}
        >
          {''}
        </h2>
      }
      contentEditable={
        <h2 style={{ minWidth: '100%' }}>
          <ContentEditable className="content-area" />
        </h2>
      }
    />
  )
}
