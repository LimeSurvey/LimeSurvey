import React from 'react'
import classNames from 'classnames'
import { useParams } from 'react-router-dom'
import { XLg } from 'react-bootstrap-icons'
import { Button } from 'react-bootstrap'

import { SideBarHeader } from 'components/SideBar'
import { createBufferOperation, STATES } from 'helpers'
import { useAppState, useBuffer, useFocused, useSurvey } from 'hooks'

import { groupAttributes } from './groupAttributes'
import { GroupAttribute } from './GroupAttribute'

export const GroupSettings = ({ gid }) => {
  const { addToBuffer } = useBuffer()

  const { surveyId } = useParams()
  const { survey, update } = useSurvey(surveyId)
  const [language] = useAppState(STATES.ACTIVE_LANGUAGE, 'en')
  const { groupIndex, unFocus } = useFocused()

  const handleUpdate = (key, value) => {
    const updatedQuestionGroups = [...survey.questionGroups]
    if (key === 'randomizationGroup' || key === 'gRelevance') {
      updatedQuestionGroups[groupIndex][key] = value
    } else {
      updatedQuestionGroups[groupIndex].l10ns[language][key] = value
    }

    update({
      questionGroups: updatedQuestionGroups,
    })

    const operation = createBufferOperation(gid).questionGroup().update({
      questionGroup: updatedQuestionGroups[groupIndex],
      questionGroupL10n: updatedQuestionGroups[groupIndex].l10ns,
    })

    addToBuffer(operation)
  }

  return (
    <div className={classNames('right-sidebar-settings')}>
      <SideBarHeader className="right-side-bar-header primary">
        <div>{t('Group settings')}</div>
        <Button variant="link" style={{ padding: 0 }} onClick={unFocus}>
          <XLg stroke={'black'} fontWeight={800} color="black" size={15} />
        </Button>
      </SideBarHeader>

      {groupAttributes.map((attribute) => {
        const keys = attribute.attributePath
          .replace('[language]', language)
          .split('.')
        const propName = keys[keys.length - 1]

        const value = keys.reduce(
          (acc, key) => acc && acc[key],
          survey.questionGroups[groupIndex]
        )

        return (
          <GroupAttribute
            key={`${attribute.attributePath}-group-attribute`}
            {...attribute}
            handleUpdate={handleUpdate}
            propName={propName}
            value={value}
          />
        )
      })}
    </div>
  )
}
