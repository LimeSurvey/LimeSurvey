import React, {
  useEffect,
  useLayoutEffect,
  useState,
  useRef,
  useCallback,
} from 'react'
import { useParams } from 'react-router-dom'
import classNames from 'classnames'
import Button from 'react-bootstrap/Button'

import { useSurvey } from 'hooks'
import { ArrowDownIcon } from 'components/icons'
import { ContentEditor, Dropdown, TooltipContainer } from 'components'
import { RemoveHTMLTagsInString } from 'helpers'

import { QuestionContextMenu } from './QuestionContextMenu'

const noop = () => {}

export const SideBarRow = ({
  icon,
  title,
  titlePlaceholder,
  code,
  children,
  testId = '',
  /* Also used as a test id for the menu toggle */
  menuToggleId = '',
  /* Also used as a test id for the menu toggle */
  menuId = '',
  provided = {},
  onRowClick = noop,
  onTitleClick = noop,
  isQuestionGroup,
  isPinned = false,
  isOpen: _isOpen = false,
  className = '',
  isFocused = false,
  menuItems = [],
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

  const rowRef = useRef(null)
  const codeRef = useRef(null)
  const [isCodeTruncated, setCodeTruncated] = useState(false)

  const rowType = isQuestionGroup ? 'group' : isPinned ? 'pinned' : 'question'
  // The drag library passes null (not undefined) when dragging is disabled
  const isDraggable = !!provided.dragHandleProps
  const showCodeBadge =
    rowType === 'question' && showQNumCode?.showCode && !!code

  useLayoutEffect(() => {
    const el = codeRef.current
    setCodeTruncated(!!el && el.scrollWidth > el.clientWidth)
  }, [code, showCodeBadge])

  const menuLabel = isQuestionGroup
    ? t('Question group menu')
    : t('Question menu')

  // Clicks are not stopped so document-level outside-click handlers (other
  // open menus, context menu) still fire; controls inside the row are skipped here.
  // Portaled menu content bubbles through the React tree but is not inside the row.
  const handleRowClick = (e) => {
    const isRowControl = e.target.closest?.(
      '.structure-item-menu, .structure-item-arrow'
    )
    if (
      !e.defaultPrevented &&
      e.currentTarget.contains(e.target) &&
      !isRowControl
    ) {
      const handler = isQuestionGroup ? onTitleClick : onRowClick
      handler(e)
    }
  }

  const handleRowKeyDown = (e) => {
    const isOwnTarget =
      e.target === e.currentTarget ||
      e.target.classList.contains('structure-item-main')
    const isActivationKey = e.key === 'Enter' || (e.key === ' ' && !isDraggable)
    if (!isOwnTarget || !isActivationKey) {
      return
    }
    e.preventDefault()
    if (isQuestionGroup) {
      onTitleClick(e)
    } else {
      rowRef.current?.click()
    }
  }

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

      <div onContextMenu={handleOnContextMenu}>
        <div
          className={classNames(
            `structure-item structure-item-${rowType} ${className}`,
            { 'structure-item-selected': isFocused }
          )}
          data-testid={testId}
          ref={rowRef}
          role={isPinned ? 'button' : undefined}
          tabIndex={isPinned ? 0 : undefined}
          aria-current={isFocused ? 'true' : undefined}
          onClick={handleRowClick}
          onKeyDown={handleRowKeyDown}
        >
          {isQuestionGroup && (
            <Button
              data-testid={`sidebar-row-toggler-button`}
              variant="link"
              aria-label={isOpen ? t('Collapse group') : t('Expand group')}
              aria-expanded={isOpen}
              onClick={() => setOpen(!isOpen)}
              className={classNames('structure-item-arrow p-0', {
                'rotate-270': !isOpen,
              })}
            >
              <ArrowDownIcon />
            </Button>
          )}
          <div
            className="structure-item-main"
            style={{ cursor: isDraggable ? 'grab' : 'pointer' }}
            // Without a drag handle the main area must stay keyboard-reachable itself
            role={!isPinned && !isDraggable ? 'button' : undefined}
            tabIndex={!isPinned && !isDraggable ? 0 : undefined}
            {...provided.dragHandleProps}
          >
            <span className="structure-item-icon">
              {typeof icon === 'string' ? <img src={icon} alt="" /> : icon}
            </span>
            <ContentEditor
              className={classNames('structure-item-title', {
                'question-group': !code,
                'question-code': showQNumCode?.showCode,
              })}
              data-placeholder={titlePlaceholder}
              value={RemoveHTMLTagsInString(title)}
              disabled={true}
            />
          </div>
          {showCodeBadge && (
            // The badge has a fixed width, so show the full code only when it is cut off
            <TooltipContainer tip={code} showTip={isCodeTruncated}>
              <div
                ref={codeRef}
                className="structure-item-code"
                data-testid="question-code-badge"
              >
                {code}
              </div>
            </TooltipContainer>
          )}
          {menuItems.length > 0 && (
            <div className="structure-item-menu">
              <Dropdown
                className="meatball-dropdown"
                testId={menuId}
                align="start"
                portalMenu
                menuItems={menuItems}
                toggleSettings={{
                  iconClassName: 'ri-more-fill',
                  variant: 'light',
                  id: menuToggleId,
                  testId: menuToggleId,
                  title: '',
                  ariaLabel: menuLabel,
                }}
              />
            </div>
          )}
        </div>
        {isOpen && <div style={{ paddingLeft: '18px' }}>{children}</div>}
      </div>
    </div>
  )
}
