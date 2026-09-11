import React, { useMemo } from 'react'

import { useFocused } from 'hooks'
import { Select } from 'components'
import { getSurveyParticipantAttributes } from 'helpers'

import { getConditionTypeInfo } from '../utils'

export const ParticipantDataSelect = ({
  index,
  condition,
  updateCondition,
  type = 'source',
  survey,
}) => {
  const { focused } = useFocused()

  const participantDataOptions = useMemo(
    () => getSurveyParticipantAttributes(survey),
    [survey]
  )
  const selectedOption = useMemo(() => {
    return (
      participantDataOptions.find(({ value }) =>
        type === 'source'
          ? value === condition.cfieldname
          : value === condition.value
      ) || null
    )
  }, [participantDataOptions, condition.cfieldname, condition.value, type])

  const handleTokenChange = ({ value }) => {
    if (type === 'target') {
      updateCondition(index, 'value', value)
      return
    }

    const updates = {
      qid: focused.qid,
      cqid: 0,
      cfieldname: value,
      cquestions: value,
      targetType: getConditionTypeInfo().TARGET.CONSTANT,
    }

    Object.keys(updates).forEach((key) =>
      updateCondition(index, key, updates[key])
    )
  }

  return (
    <div className="mb-3">
      <Select
        id={`participant-data-select-${index}-${type}`}
        options={participantDataOptions}
        value={selectedOption?.value || null}
        onChange={handleTokenChange}
        placeholder={t('Participant data')}
      />
    </div>
  )
}
