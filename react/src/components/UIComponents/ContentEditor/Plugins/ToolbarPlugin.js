import { useEffect, useState } from 'react'
import classNames from 'classnames'
import { Button, FormGroup, OverlayTrigger } from 'react-bootstrap'
import {
  FORMAT_TEXT_COMMAND,
  $getSelection,
  $isRangeSelection,
  SELECTION_CHANGE_COMMAND,
  COMMAND_PRIORITY_EDITOR,
} from 'lexical'
import { $createHeadingNode } from '@lexical/rich-text'
import { $setBlocksType, $selectAll } from '@lexical/selection'
import { useLexicalComposerContext } from '@lexical/react/LexicalComposerContext'

import boldIcon from 'assets/icons/bold-icon.svg'
import italicIcon from 'assets/icons/italic-icon.svg'
import verticalMeatballMenu from 'assets/icons/vertical-meatball.svg'
import { HtmlPopup, SwalAlert } from 'helpers'
import { StringBuilder } from 'components/StringBuilder/StringBuilder'
import { useAppState } from 'hooks'

export const ToolbarPlugin = ({ showToolbar, value }) => {
  const [showMenu, setShowMenu] = useState(false)
  const [selection, setSelection] = useState({})
  const [editor] = useLexicalComposerContext()
  const [codeToQuestion] = useAppState('codeToQuestion', {})

  const handleHeadings = (heading) => {
    editor.update(() => {
      const selection = $getSelection()
      $selectAll(selection)
      if ($isRangeSelection(selection)) {
        $setBlocksType(selection, () => $createHeadingNode(heading))
      }
    })
  }

  const handleStringBuilder = (builderValue, startingTag, closingTag) => {
    editor.update(() => {
      const selection = $getSelection()
      if (startingTag === -1 || closingTag === -1) {
        selection.insertText(builderValue)
        return
      }

      selection.anchor.offset = closingTag + 1
      selection.focus.offset = startingTag
      selection.removeText()

      if (builderValue.length > 2) {
        selection.insertText(builderValue)
      }
    })
    SwalAlert.close()
  }

  useEffect(() => {
    editor.registerCommand(
      SELECTION_CHANGE_COMMAND,
      () => {
        setSelection($getSelection())
      },
      COMMAND_PRIORITY_EDITOR
    )
  }, [editor])

  const fontMenu = (
    <div className="content-editor-meatball-menu">
      <p className="font-size toolbar-header p-2 pb-0 text-left">FONT SIZE</p>
      <Button
        onClick={() => handleHeadings('h3')}
        variant="outline-dark"
        className="p-2 mt-2 small"
      >
        Small
      </Button>
      <Button
        variant="outline-dark"
        className="d-flex align-items-center p-2 medium"
        onClick={() => handleHeadings('h2')}
      >
        Medium
      </Button>
      <Button
        variant="outline-dark"
        className="d-flex align-items-center p-2 mb-2 large"
        onClick={() => handleHeadings('h1')}
      >
        Large
      </Button>
      <hr />
      <p className="actions toolbar-header p-2 pb-0 text-left mt-2">ACTIONS</p>
      <Button
        variant="outline-dark"
        className="action-button d-flex align-items-center p-2 mt-2 "
      >
        Dynamic text blocks
      </Button>
      <Button
        variant="outline-dark"
        className="action-button d-flex align-items-center p-2"
      >
        Global presets
      </Button>
    </div>
  )

  return (
    <div
      className={classNames('content-editor-toolbar', {
        'disabled opacity-0': !showToolbar,
      })}
    >
      <FormGroup
        className="content-editor-toolbar-form-group "
        onClick={(e) => e.preventDefault()}
      >
        <Button
          className="toolbar-button d-flex justify-content-center align-items-center"
          variant="outline-dark"
          onClick={() => {
            editor.dispatchCommand(FORMAT_TEXT_COMMAND, 'bold')
          }}
        >
          <img src={boldIcon} alt="bold icon" />
        </Button>
        <Button
          className="toolbar-button d-flex justify-content-center align-items-center"
          variant="outline-dark"
          onClick={() => {
            editor.dispatchCommand(FORMAT_TEXT_COMMAND, 'italic')
          }}
        >
          <img src={italicIcon} alt="italic icon" />
        </Button>
        <Button
          className="toolbar-button d-flex justify-content-center align-items-center"
          variant=""
          onClick={() => {
            HtmlPopup({
              html: (
                <StringBuilder
                  codeToQuestion={codeToQuestion}
                  selection={selection}
                  value={value}
                  onConfirm={handleStringBuilder}
                />
              ),
              title: 'Equation',
            })
          }}
        >
          [X]
        </Button>
        <OverlayTrigger
          trigger="click"
          overlay={fontMenu}
          placement="right-start"
          show={showMenu}
          onToggle={(show) => {
            setShowMenu(show)
          }}
          rootClose
        >
          <Button
            variant="outline-dark"
            className="toolbar-button d-flex meatball-icon justify-content-center align-items-center"
          >
            <img src={verticalMeatballMenu} alt="meatball icon" />
          </Button>
        </OverlayTrigger>
      </FormGroup>
    </div>
  )
}
