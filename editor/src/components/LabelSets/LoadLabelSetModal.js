import { useEffect, useMemo, useRef, useState } from 'react'

import { useLabelSets, useLabelSetService } from 'hooks'
import { ComponentModal } from 'components/Modals'
import { Select } from 'components/UIComponents'
import { showErrorMessage } from 'components/ConditionDesigner/utils'

import { LabelSetListError } from './LabelSetListError'

const SELECT_INPUT_ID = 'load-label-set-select-input'

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
  // Bumped whenever the modal opens or closes, so a load that finishes later is ignored
  const loadRequestId = useRef(0)
  const labelSetService = useLabelSetService()
  const { labelSets, isFetching, isError, refetch } = useLabelSets({
    languages,
    enabled: show,
  })

  useEffect(() => {
    loadRequestId.current++
    setIsLoading(false)
    if (show) {
      setSelectedLid(null)
    }
  }, [show])

  // The select is disabled while loading, so focus it again once the list is there
  const focusSelect = () => document.getElementById(SELECT_INPUT_ID)?.focus()
  useEffect(() => {
    if (show && !isFetching) {
      focusSelect()
    }
  }, [show, isFetching])

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

    const requestId = ++loadRequestId.current
    const isCurrentRequest = () => requestId === loadRequestId.current

    setIsLoading(true)
    try {
      const labelSet = await labelSetService.getLabelSet(selectedLid)
      if (!isCurrentRequest()) {
        return
      }
      onLoad(labelSet?.labels ?? [])
      onHide()
    } catch (error) {
      if (isCurrentRequest()) {
        showErrorMessage(error?.message || t('Error'))
      }
    } finally {
      if (isCurrentRequest()) {
        setIsLoading(false)
      }
    }
  }

  const renderList = () => {
    if (isError && !isFetching) {
      return (
        <LabelSetListError
          onRetry={refetch}
          testId="load-label-set-list-error"
        />
      )
    }

    if (!isFetching && labelSets.length === 0) {
      return (
        <p className="label-set-modal-empty" data-testid="load-label-set-empty">
          {t('No label sets found.')}
        </p>
      )
    }

    return (
      <Select
        labelText={t('Label set')}
        options={options}
        value={selectedLid}
        defaultValue={null}
        update={(value) => setSelectedLid(value)}
        placeholder={isFetching ? t('Loading...') : t('Please choose...')}
        disabled={isFetching}
        dataTestId="load-label-set-select"
        inputId={SELECT_INPUT_ID}
      />
    )
  }

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
          {/* The list is fetched when the modal opens */}
          <div aria-live="polite">{renderList()}</div>
        </div>
      }
      useFooter
      confirmButtonText={t('Load label set')}
      onConfirm={handleLoad}
      isLoading={isLoading}
      confirmDisabled={!selectedLid || isError}
      onEntered={focusSelect}
    />
  )
}
