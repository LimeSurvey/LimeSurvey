import React, { useCallback, useEffect, useState } from 'react'
import { OverlayTrigger } from 'react-bootstrap'
import { useParams } from 'react-router-dom'
import copy from 'copy-text-to-clipboard'

import { useBuffer, useSurvey } from 'hooks'
import { createBufferOperation } from 'helpers'
import { ShortcutIcon } from 'components/icons'

export const QuestionContextMenu = ({
  code,
  positionX,
  positionY,
  isToggled,
  contextMenuRef,
  setContextMenu,
}) => {
  const [goToMenu, setGoToMenu] = useState(false)
  const { surveyId } = useParams()
  const { survey, update } = useSurvey(surveyId)
  const { addToBuffer } = useBuffer()

  const buttons = [
    {
      text: t('Show/hide question codes'),
      icon: <ShortcutIcon />,
      shortcut: 'H',
      onClick: (e) => {
        e.stopPropagation()
        let operationAttr = {
          showQNumCode: {
            showCode: !survey.showQNumCode?.showCode,
            showNumber: !survey.showQNumCode?.showNumber,
          },
        }
        update(operationAttr)
        let operation = createBufferOperation(surveyId)
          .survey()
          .update(operationAttr)
        addToBuffer(operation)
        setContextMenu({ position: { x: 0, y: 0 }, toggled: false })
      },
    },
    {
      text: t('Copy question code'),
      icon: <ShortcutIcon />,
      shortcut: 'C',
      onClick: (e) => {
        e.stopPropagation()
        copy(code)
        setContextMenu({ position: { x: 0, y: 0 }, toggled: false })
      },
    },
  ]

  const resetGoToMenu = useCallback(() => {
    setGoToMenu(false)
  }, [])

  useEffect(() => {
    function rightClick(e) {
      if (e.which === 3 || e.button === 3) {
        resetGoToMenu()
      }
    }

    document.addEventListener('scroll', resetGoToMenu, false)
    document.addEventListener('mousedown', rightClick, false)

    return () => {
      document.removeEventListener('scroll', resetGoToMenu, false)
      document.removeEventListener('mousedown', rightClick, false)
    }
  }, [])

  return (
    <menu
      style={{
        top: positionY - 8 + 'px',
        left: positionX + 'px',
      }}
      className={`context-menu ${isToggled ? 'active' : ''}`}
      ref={contextMenuRef}
    >
      {buttons.map((button, index) => {
        function handleClick(e) {
          e.stopPropagation()
          button.onClick(e, code)
        }
        return (
          <React.Fragment key={`${code}-${button?.text}`}>
            {button.seperator && <hr />}

            {button.overlay ? (
              <OverlayTrigger
                trigger="click"
                overlay={button.overlay}
                placement="right-start"
                show={goToMenu}
                onToggle={(show) => {
                  setGoToMenu(show)
                }}
                rootClose
              >
                <button key={index}>
                  {button.text}
                  <span className={'btn-icon'}>
                    {button.icon}
                    {button.shortcut}
                  </span>
                </button>
              </OverlayTrigger>
            ) : (
              <button key={index} onClick={handleClick}>
                {button.text}
              </button>
            )}
          </React.Fragment>
        )
      })}
    </menu>
  )
}
