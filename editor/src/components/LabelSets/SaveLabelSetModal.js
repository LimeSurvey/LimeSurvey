import { useEffect, useMemo, useState } from 'react'

import { useAppState, useLabelSets, useSaveLabelSet } from 'hooks'
import { childrenToLabels, Entities, STATES } from 'helpers'
import { ComponentModal } from 'components/Modals'
import { Input, Select, ToggleButtons } from 'components/UIComponents'
import {
  showErrorMessage,
  showSuccessMessage,
} from 'components/ConditionDesigner/utils'

import { LabelSetListError } from './LabelSetListError'

const NAME_INPUT_ID = 'save-label-set-name-input'
const LANGUAGE_INPUT_ID = 'save-label-set-languages-input'
const OVERWRITE_INPUT_ID = 'save-label-set-overwrite-input'

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
  const {
    labelSets: updatableLabelSets,
    isFetching,
    isError,
    refetch,
  } = useLabelSets({
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
  const getLanguageName = (language) =>
    languageNames?.[language]?.description || language
  const languageOptions = useMemo(
    () =>
      surveyLanguages.map((language) => ({
        value: language,
        label: getLanguageName(language),
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

  // Like the classic editor, overwriting replaces all labels and drops the
  // translations of languages that are not selected, so say what will be lost
  const overwriteTarget = overwrite
    ? updatableLabelSets.find(
        (labelSet) => Number(labelSet.lid) === Number(overwriteLid)
      )
    : null
  const removedLanguages = (overwriteTarget?.languages ?? []).filter(
    (language) => !languages.includes(language)
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
          ? t('Label set successfully updated')
          : t('Label set successfully saved.')
      )
      onHide()
    } catch (error) {
      showErrorMessage(error?.message || t('Error'))
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
                id={NAME_INPUT_ID}
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
              inputId={LANGUAGE_INPUT_ID}
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
            {overwrite && isError && !isFetching && (
              <LabelSetListError
                onRetry={refetch}
                testId="save-label-set-list-error"
              />
            )}
            {overwrite && !(isError && !isFetching) && (
              <Select
                labelText={t('Overwrite label set')}
                options={overwriteOptions}
                value={overwriteLid}
                defaultValue={null}
                update={(value) => setOverwriteLid(value)}
                placeholder={isFetching ? t('Loading...') : undefined}
                disabled={isFetching}
                dataTestId="save-label-set-overwrite-select"
                inputId={OVERWRITE_INPUT_ID}
              />
            )}
            {overwriteTarget && (
              <div
                className="label-set-modal-warning"
                role="alert"
                data-testid="save-label-set-overwrite-warning"
              >
                {/* Same warnings as the classic editor, plus the names of the lost languages */}
                {overwriteTarget.hasAssessmentValues && (
                  <p data-testid="save-label-set-assessment-warning">
                    {t(
                      'The existing label set has assessment values assigned.'
                    )}{' '}
                    <strong>
                      {t(
                        'If you replace the label set the existing assessment values will be lost.'
                      )}
                    </strong>
                  </p>
                )}
                {removedLanguages.length > 0 && (
                  <div data-testid="save-label-set-removed-languages">
                    <p>
                      {t(
                        'The existing label set has different/more languages.'
                      )}{' '}
                      <strong>
                        {t(
                          'If you replace the label set these translations will be lost.'
                        )}
                      </strong>
                    </p>
                    <ul className="label-set-modal-removed-languages">
                      {removedLanguages.map((language) => (
                        <li key={language}>{getLanguageName(language)}</li>
                      ))}
                    </ul>
                  </div>
                )}
                {!overwriteTarget.hasAssessmentValues &&
                  removedLanguages.length === 0 && (
                    <p>
                      {t(
                        'You are about to replace an existing label set with the current answer options.'
                      )}
                    </p>
                  )}
              </div>
            )}
          </div>
        </div>
      }
      useFooter
      confirmButtonText={t('Save label set')}
      onConfirm={handleSave}
      isLoading={isSaving}
      confirmDisabled={!isValid}
      onEntered={() => document.getElementById(NAME_INPUT_ID)?.focus()}
    />
  )
}
