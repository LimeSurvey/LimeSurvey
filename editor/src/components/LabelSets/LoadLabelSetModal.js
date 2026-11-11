import { useEffect, useMemo, useState } from 'react'

import { useLabelSets, useLabelSetService } from 'hooks'
import { ComponentModal } from 'components/Modals'
import { Select } from 'components/UIComponents'
import { showErrorMessage } from 'components/ConditionDesigner/utils'

/**
 * Lets the user pick a label set and hands its labels to onLoad.
 * Only label sets that contain all survey languages are listed.
 */
export const LoadLabelSetModal = ({
  show = false,
  onHide = () => {},
  onLoad = () => {},
  languages = [],
}) => {
  const [selectedLid, setSelectedLid] = useState(null)
  const [isLoading, setIsLoading] = useState(false)
  const labelSetService = useLabelSetService()
  const { labelSets, isFetching } = useLabelSets({
    languages,
    enabled: show,
  })

  useEffect(() => {
    if (show) {
      setSelectedLid(null)
    }
  }, [show])

  const options = useMemo(
    () =>
      labelSets.map((labelSet) => ({
        value: labelSet.lid,
        label: labelSet.name,
      })),
    [labelSets]
  )

  const handleLoad = async () => {
    if (!selectedLid) {
      return
    }

    setIsLoading(true)
    try {
      const labelSet = await labelSetService.getLabelSet(selectedLid)
      onLoad(labelSet?.labels ?? [])
      onHide()
    } catch (error) {
      showErrorMessage(error?.message || t('Could not load the label set.'))
    } finally {
      setIsLoading(false)
    }
  }

  const hasNoLabelSets = !isFetching && labelSets.length === 0

  return (
    <ComponentModal
      show={show}
      onHide={onHide}
      title={t('Load label set')}
      modalClassname="label-set-modal"
      componentClassname="label-set-modal-body"
      Component={
        <div data-testid="load-label-set-modal">
          <p className="label-set-modal-description">
            {t('Select your label set for this question.')}
          </p>
          {hasNoLabelSets ? (
            <p
              className="label-set-modal-empty"
              data-testid="load-label-set-empty"
            >
              {t("There are no label sets for this survey's languages.")}
            </p>
          ) : (
            <Select
              labelText={t('Label set')}
              options={options}
              value={selectedLid}
              defaultValue={null}
              update={(value) => setSelectedLid(value)}
              placeholder={
                isFetching ? t('Loading...') : t('Please select label set...')
              }
              disabled={isFetching}
              dataTestId="load-label-set-select"
            />
          )}
        </div>
      }
      useFooter
      confirmButtonText={t('Load label set')}
      onConfirm={handleLoad}
      isLoading={isLoading}
      confirmDisabled={!selectedLid}
    />
  )
}
