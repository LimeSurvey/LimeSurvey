import { useEffect, useMemo, useState } from 'react'
import { Button } from 'react-bootstrap'
import classNames from 'classnames'

import { createBufferOperation, hasTempId, L10ns } from 'helpers'
import { useBuffer } from 'hooks'
import { ContentEditor } from 'components/UIComponents'
import { MeatballMenu } from 'components/MeatballMenu/MeatballMenu'
import { ReactComponent as DownArrow } from 'assets/icons/down-arrow.svg'

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
  const { addToBuffer } = useBuffer()

  const handleDuplicate = () => {
    duplicateGroup()
  }

  const handleDelete = () => {
    deleteGroup()
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
        <MeatballMenu
          deleteText={'Delete group'}
          duplicateText={'Duplicate group'}
          handleDelete={handleDelete}
          handleDuplicate={handleDuplicate}
          onClick={handleFocusGroup}
          testId="question-group-meatball-menu"
        />
      </div>
    </>
  )
}
