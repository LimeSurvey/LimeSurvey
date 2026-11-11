import { useEffect, useMemo, useState } from 'react'

import { useAppState, useLabelSets, useSaveLabelSet } from 'hooks'
import { childrenToLabels, Entities, STATES } from 'helpers'
import { ComponentModal } from 'components/Modals'
import { Input, Select, ToggleButtons } from 'components/UIComponents'
import {
  showErrorMessage,
  showSuccessMessage,
} from 'components/ConditionDesigner/utils'

/**
 * Saves the answer options or subquestions of a question as a new label set,
 * or overwrites an existing label set.
 */
export const SaveLabelSetModal = ({
  show = false,
  onHide = () => {},
  _children = [],
  entityType = Entities.answer,
  surveyLanguages = [],
}) => {
  const [name, setName] = useState('')
  const [languages, setLanguages] = useState(surveyLanguages)
  const [overwrite, setOverwrite] = useState(false)
  const [overwriteLid, setOverwriteLid] = useState(null)
  const [allLanguages] = useAppState(STATES.ALL_AVAILABLE_LANGUAGES)
  const [userDetails] = useAppState(STATES.USER_DETAIL)
  const { saveLabelSet, isSaving } = useSaveLabelSet()
  const { labelSets: updatableLabelSets, isFetching } = useLabelSets({
    updatable: true,
    enabled: show && overwrite,
  })

  useEffect(() => {
    if (show) {
      setName('')
      setLanguages(surveyLanguages)
      setOverwrite(false)
      setOverwriteLid(null)
    }
  }, [show])

  const languageNames = allLanguages?.[userDetails?.lang]
  const languageOptions = useMemo(
    () =>
      surveyLanguages.map((language) => ({
        value: language,
        label: languageNames?.[language]?.description || language,
      })),
    [surveyLanguages, languageNames]
  )

  const overwriteOptions = useMemo(
    () =>
      updatableLabelSets.map((labelSet) => ({
        value: labelSet.lid,
        label: labelSet.name,
      })),
    [updatableLabelSets]
  )

  const isAnswer = entityType === Entities.answer
  const isValid =
    _children.length > 0 &&
    languages.length > 0 &&
    (overwrite ? !!overwriteLid : name.trim() !== '')

  const handleSave = async () => {
    if (!isValid) {
      return
    }

    try {
      await saveLabelSet({
        lid: overwrite ? overwriteLid : undefined,
        name: name.trim(),
        languages,
        labels: childrenToLabels(_children, entityType, languages),
      })
      showSuccessMessage(
        overwrite
          ? t('Label set successfully updated.')
          : t('Label set successfully saved.')
      )
      onHide()
    } catch (error) {
      showErrorMessage(error?.message || t('Could not save the label set.'))
    }
  }

  return (
    <ComponentModal
      show={show}
      onHide={onHide}
      title={t('Save as new label set')}
      modalClassname="label-set-modal"
      componentClassname="label-set-modal-body"
      Component={
        <div data-testid="save-label-set-modal">
          <p className="label-set-modal-description">
            {isAnswer
              ? t('Save the answer options of this question as a label set.')
              : t('Save the subquestions of this question as a label set.')}
          </p>
          <div className="label-set-modal-fields">
            {!overwrite && (
              <Input
                labelText={t('Label set name')}
                value={name}
                update={(value) => setName(value)}
                dataTestId="save-label-set-name"
              />
            )}
            <Select
              labelText={t('Language')}
              options={languageOptions}
              value={languageOptions.filter((option) =>
                languages.includes(option.value)
              )}
              update={(value) => setLanguages(value)}
              isMultiselect
              dataTestId="save-label-set-languages"
            />
            <ToggleButtons
              id="save-label-set-overwrite"
              labelText={t('Overwrite an existing label set')}
              toggleOptions={[
                { name: t('Yes'), value: true },
                { name: t('No'), value: false },
              ]}
              value={overwrite}
              update={(value) => setOverwrite(value)}
            />
            {overwrite && (
              <Select
                labelText={t('Overwrite label set')}
                options={overwriteOptions}
                value={overwriteLid}
                defaultValue={null}
                update={(value) => setOverwriteLid(value)}
                placeholder={isFetching ? t('Loading...') : undefined}
                disabled={isFetching}
                dataTestId="save-label-set-overwrite-select"
              />
            )}
          </div>
        </div>
      }
      useFooter
      confirmButtonText={t('Save label set')}
      onConfirm={handleSave}
      isLoading={isSaving}
      confirmDisabled={!isValid}
    />
  )
}
