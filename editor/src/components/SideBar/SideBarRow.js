import React, { useEffect, useState, useRef, useCallback } from 'react'
import { useParams } from 'react-router-dom'
import classNames from 'classnames'
import Button from 'react-bootstrap/Button'

import { useSurvey } from 'hooks'
import { ContentEditor } from 'components/UIComponents'
import { ArrowDownIcon } from 'components/icons'
import { TooltipContainer } from 'components'

import { QuestionContextMenu } from './QuestionContextMenu'

export const SideBarRow = ({
  icon,
  title,
  titlePlaceholder,
  code,
  meatballButton,
  children,
  style,
  testId = '',
  provided = {},
  onTitleClick = () => {},
  onRowClick = () => {},
  showMeatballButton,
  isQuestionGroup,
  isOpen: _isOpen = false,
  className = '',
  isFocused = false,
}) => {
  const [isOpen, setOpen] = useState(_isOpen)
  const { surveyId } = useParams()
  const { survey } = useSurvey(surveyId)
  const showQNumCode = survey.showQNumCode

  useEffect(() => {
    if (!isOpen) {
      setOpen(_isOpen)
    }
  }, [_isOpen])

  const contextMenuRef = useRef(null)
  const [contextMenu, setContextMenu] = useState({
    position: { x: 0, y: 0 },
    toggled: false,
  })

  function handleOnContextMenu(e) {
    if (!isQuestionGroup) {
      e.preventDefault()
      const maxWidth = 300
      const posX = e.clientX
      const posY = e.clientY

      if (posX + 150 > maxWidth)
        setContextMenu({
          position: { x: maxWidth - 150, y: e.clientY },
          toggled: true,
        })
      else setContextMenu({ position: { x: posX, y: posY }, toggled: true })
    }
  }

  const resetContextMenu = useCallback(() => {
    setContextMenu({ position: { x: 0, y: 0 }, toggled: false })
  }, [])

  useEffect(() => {
    function handler(e) {
      if (
        contextMenuRef.current &&
        !contextMenuRef.current.contains(e.target)
      ) {
        resetContextMenu()
      }
    }

    function rightClick(e) {
      if (e.which === 3 || e.button === 3) {
        resetContextMenu()
      }
    }

    document.addEventListener('click', handler)
    document.addEventListener('mousedown', rightClick, false)
    document.addEventListener('scroll', resetContextMenu, false)

    return () => {
      document.removeEventListener('click', handler)
      document.removeEventListener('mousedown', rightClick, false)
      document.removeEventListener('scroll', resetContextMenu, false)
    }
  }, [])

  return (
    <div>
      <QuestionContextMenu
        code={code}
        contextMenuRef={contextMenuRef}
        positionX={contextMenu.position.x}
        positionY={contextMenu.position.y}
        isToggled={contextMenu.toggled}
        setContextMenu={setContextMenu}
      />

      <div
        onClick={onRowClick}
        onContextMenu={handleOnContextMenu}
        data-testid={testId}
      >
        <div
          className={classNames(`sidebar-row ps-1 ${className}`, {
            'focus-bg-purple text-white': isFocused,
          })}
          style={{
            ...style,
          }}
        >
          <div
            style={{
              cursor: provided.dragHandleProps ? 'grab' : 'pointer',
            }}
            className={classNames(
              'sidebar-row-title-container d-flex align-items-center'
            )}
            {...provided.dragHandleProps}
          >
            <Button
              data-testid={`sidebar-row-toggler-button`}
              variant="link"
              onClick={() => setOpen(!isOpen)}
              className={classNames('p-0', {
                'rotate-270': !isOpen,
                'd-none': !isQuestionGroup,
              })}
            >
              <ArrowDownIcon />
            </Button>
            {typeof icon === 'string' ? (
              <img
                className={classNames({
                  'question-group': !code,
                })}
                src={icon}
                alt="sidebar row item icon"
              />
            ) : (
              icon
            )}
            <ContentEditor
              disabled={true}
              placeholder={titlePlaceholder}
              value={title}
              className={classNames('sidebar-row-title', {
                'question-group': !code,
                'question-code': showQNumCode?.showCode,
              })}
              onClick={onTitleClick}
            />
          </div>
          <div
            className={classNames(
              'd-flex',
              'flex-grow-1',
              'align-items-center',
              'justify-content-end'
            )}
          >
            <div>
              {!isQuestionGroup && showQNumCode?.showCode && code && (
                // when the length is more than 5 then its shorted with dots
                <TooltipContainer tip={code} showTip={code?.length > 5}>
                  <div className="sidebar-row-tag mx-1 bg-white label-s">
                    {code}
                  </div>
                </TooltipContainer>
              )}
            </div>
            <span
              className={classNames('sidebar-meatball-menu my-1', {
                'opacity-100': showMeatballButton,
              })}
            >
              {meatballButton}
            </span>
          </div>
        </div>
        {isOpen && <div style={{ paddingLeft: '18px' }}>{children}</div>}
      </div>
    </div>
  )
}
