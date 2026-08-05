import { useEffect, useMemo, useState } from 'react'
import { useParams } from 'react-router-dom'
import { Button } from 'react-bootstrap'
import classNames from 'classnames'

import { createBufferOperation, hasTempId, L10ns, getSiteUrl } from 'helpers'
import { useBuffer } from 'hooks'
import { ContentEditor } from 'components/UIComponents'
import { Dropdown } from 'components/UIComponents/Dropdown/Dropdown'
import { ReactComponent as DownArrow } from 'assets/icons/down-arrow.svg'
import { EyeIcon } from 'components/icons'

import { TestValidation } from './QuestionGroupSchema'

export const QuestionGroupHeader = ({
  questionGroup,
  duplicateGroup,
  deleteGroup,
  handleFocusGroup,
  setShowQuestions,
  showQuestions,
  handleUpdate,
  language,
  onErrors,
  questionsLength = 0,
  isFocused,
}) => {
  const [errors, setErrors] = useState('')
  const { surveyId } = useParams()
  const { addToBuffer } = useBuffer()

  const handleDuplicate = () => {
    duplicateGroup()
  }

  const handleDelete = () => {
    deleteGroup()
  }

  const menuItems = [
    {
      type: 'header',
      label: t('Group actions'),
    },
    {
      type: 'item',
      label: t('Duplicate group'),
      icon: 'ri-file-copy-line',
      onClick: handleDuplicate,
      testId: 'duplicate-button',
    },
    {
      type: 'item',
      label: t('Delete group'),
      icon: 'ri-delete-bin-line',
      onClick: handleDelete,
      className: 'text-danger',
      testId: 'delete-button',
    },
  ]
  const openQuestionGroupPreview = () => {
    const previewUrl = getSiteUrl(
      `/index.php/survey/index/action/previewgroup/sid/${surveyId}/gid/${questionGroup.gid}/lang/${language}`
    )
    window.open(previewUrl, '_blank')
  }

  const focusTitle = useMemo(
    () => isFocused && hasTempId(questionGroup.gid),
    [isFocused]
  )

  const updateTitle = (groupName) => {
    const operation = createBufferOperation(questionGroup.gid)
      .questionGroupL10n()
      .update({
        [language]: { groupName },
      })

    handleUpdate({ groupName })
    addToBuffer(operation)
  }

  useEffect(() => {
    onErrors(errors)
  }, [errors, onErrors])

  return (
    <>
      <div
        className={classNames('header d-flex justify-content-between mb-2', {
          'error-focus': errors,
        })}
      >
        <div
          className={classNames(
            'title d-flex flex-grow-1 align-items-center gap-1'
          )}
          id={`question-group-${questionGroup.gid}`}
        >
          <Button
            variant="outline"
            data-testid="collapse-button-question-group"
            onClick={() => setShowQuestions(!showQuestions)}
            className={classNames('p-0 pb-1 transition-all', {
              'rotate-180 ': showQuestions,
            })}
          >
            <DownArrow style={{ transform: 'rotate(-180deg)' }} />
          </Button>
          <ContentEditor
            value={L10ns({
              prop: 'groupName',
              language,
              l10ns: questionGroup.l10ns,
            })}
            update={(groupName) => updateTitle(groupName)}
            placeholder={t('Group title')}
            setErrors={setErrors}
            testValidation={TestValidation}
            onClick={handleFocusGroup}
            className="header"
            focus={focusTitle}
          />
          {questionsLength >= 0 ? (
            <span onClick={handleFocusGroup} className="questions-length ms-2">
              {questionsLength}
            </span>
          ) : (
            <></>
          )}
        </div>
        <div
          className={classNames('cursor-pointer me-2 d-flex', {})}
          data-testid="question-footer-preview-icon"
          onClick={openQuestionGroupPreview}
        >
          <EyeIcon
            className={classNames('footer-icon ', {
              'active-icon fill-current': isFocused,
            })}
            width={20}
            height={20}
          />
        </div>
        <Dropdown
          className="question-group-actions-dropdown"
          testId="question-group-meatball-menu"
          menuItems={menuItems}
          toggleSettings={{
            iconClassName: 'ri-more-fill',
            variant: 'light',
            id: 'question-group-actions-menu',
            testId: 'meatball-menu-button',
            title: '',
          }}
        />
      </div>
    </>
  )
}
