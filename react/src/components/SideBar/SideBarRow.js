import React, { useState } from 'react'
import classNames from 'classnames'
import Button from 'react-bootstrap/Button'

import { ContentEditor } from 'components/UIComponents'
import { ArrowDownIcon } from 'components/icons'

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
  showQuestionCode,
}) => {
  const [isOpen, setOpen] = useState(false)

  return (
    <div onClick={onRowClick} data-testid={testId}>
      <div
        className="sidebar-row ps-1"
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
          {children && (
            <Button
              data-testid="sidebar-row-toggler-button"
              variant="link"
              onClick={() => setOpen(!isOpen)}
              className={classNames('p-0', {
                'rotate-270': !isOpen,
              })}
            >
              <ArrowDownIcon />
            </Button>
          )}
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
            })}
            style={{
              marginLeft: '10px',
              maxWidth: showQuestionCode ? '180px' : '200px',
            }}
            onClick={onTitleClick}
          />
          {showQuestionCode && (
            <div className="sidebar-row-tag px-1 bg-white">{code}</div>
          )}
        </div>
        <div
          className={classNames(
            'd-flex',
            'flex-grow-1',
            'align-items-center',
            'justify-content-end'
          )}
        >
          <span
            className={classNames('sidebar-meatball-menu', {
              'opacity-100': showMeatballButton,
            })}
          >
            {meatballButton}
          </span>
        </div>
      </div>
      {isOpen && <div style={{ paddingLeft: '18px' }}>{children}</div>}
    </div>
  )
}
