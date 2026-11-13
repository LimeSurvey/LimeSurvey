import { Button, Form, OverlayTrigger, Popover } from 'react-bootstrap'
import { useState } from 'react'
import { useAppState, useBuffer, useFocused } from 'hooks'
import { Entities, Operations, STATES } from 'helpers'

import { QuestionTypeSelector } from 'components/QuestionTypeSelector'
import { ArrowDownIcon } from 'components/icons'
import { getQuestionTypeInfo } from '../../QuestionTypes'

export const QuestionTypeAttribute = ({
  value,
  update,
  activeDisabled = false,
}) => {
  const [isAddingQuestionOrGroup, setIsAddingQuestionOrGroup] = useState(false)
  const [isSurveyActive] = useAppState(STATES.IS_SURVEY_ACTIVE)
  const [hasSurveyUpdatePermission] = useAppState(
    STATES.HAS_SURVEY_UPDATE_PERMISSION
  )
  const { operationsBuffer } = useBuffer()
  const { focused } = useFocused()
  // Locked until a queued type change is sent, so a second change can't merge into it
  const isTypeChangeQueued = !!operationsBuffer?.getOperation(
    focused?.qid,
    Operations.update,
    Entities.question
  )?.props?.type
  const disabled =
    (isSurveyActive && activeDisabled) ||
    !hasSurveyUpdatePermission ||
    isTypeChangeQueued

  const questionTypeSelectorPopover = (
    <Popover
      id="question-type-selector-popover"
      bsPrefix="question-type-attribute-selector-container"
    >
      <QuestionTypeSelector
        callBack={(typeInfo) =>
          update({
            type: typeInfo.type,
            questionThemeName: typeInfo.questionThemeName,
          })
        }
        attributeTypeSelector={true}
      />
    </Popover>
  )

  const questionTypeKey = Object.keys(getQuestionTypeInfo()).find(
    (key) => getQuestionTypeInfo()[key].theme === value
  )

  const questionTypeTitle = getQuestionTypeInfo()[questionTypeKey]?.title

  return (
    <div className="question-type-general-settings" data-testid="question-type">
      <Form.Label className="d-block attribute-label">{t('Type')}</Form.Label>
      <OverlayTrigger
        trigger="click"
        offset={[0, 4]}
        placement={'bottom'}
        overlay={questionTypeSelectorPopover}
        show={isAddingQuestionOrGroup}
        onToggle={(show) => {
          setIsAddingQuestionOrGroup(show)
        }}
        rootClose
      >
        <Button
          variant="outline-dark w-100 d-flex align-items-center justify-content-between overlay-trigger-button"
          disabled={disabled}
        >
          <div className="text-start">{questionTypeTitle}</div>
          <div>
            <ArrowDownIcon />
          </div>
        </Button>
      </OverlayTrigger>
    </div>
  )
}
