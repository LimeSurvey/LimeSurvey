import React from 'react'

import { htmlPopup, Toast } from 'helpers'
import { CheckIcon, ExclamationIcon } from 'components/icons'

import {
  ConditionOverwriteConfirmation,
  ConditionScriptOverwriteConfirmation,
  DeleteScenarioOverlay,
  ResetAllConditionsOverlay,
  UnsavedChangesOverlay,
} from '../Overlays'

export const showSuccessMessage = (
  message = t('Condition applied successfully')
) => {
  Toast({
    message: message,
    position: 'bottom-center',
    className: 'generic-toast green-left-mark',
    leftIcon: (
      <CheckIcon
        className={`fill-current text-white rounded-circle bg-dark me-1`}
      />
    ),
  })
}

export const showWarningMessage = (message, position = 'bottom-right') => {
  if (!message) return

  Toast({
    message: message,
    position: position,
    className: 'generic-toast yellow-left-mark',
    leftIcon: <ExclamationIcon />,
  })
}

export const showUnsavedChangesOverlay = (onIgnoreChanges, message = null) => {
  htmlPopup({
    html: (
      <UnsavedChangesOverlay
        onIgnoreChanges={() => {
          onIgnoreChanges()
        }}
        message={message}
      />
    ),
    title: '',
    showCloseButton: true,
    width: '807px',
  })
}

export const showDeleteScenarioOverlay = (scenarioId, onConfirmDelete) => {
  htmlPopup({
    html: (
      <DeleteScenarioOverlay
        scenarioId={scenarioId}
        onConfirmDelete={() => {
          onConfirmDelete(scenarioId)
        }}
      />
    ),
    title: '',
    showCloseButton: true,
    width: '807px',
  })
}

export const showResetAllConditionsOverlay = ({ onConfirmDelete }) => {
  htmlPopup({
    html: <ResetAllConditionsOverlay onConfirmDelete={onConfirmDelete} />,
    title: '',
    showCloseButton: true,
    width: '807px',
  })
}

export const showConditionOverwriteConfirmationOverlay = ({
  onConfirm,
  onCancel = () => {},
}) => {
  htmlPopup({
    html: (
      <ConditionOverwriteConfirmation
        onConfirmChanges={onConfirm}
        onCancelChanges={onCancel}
      />
    ),
    title: '',
    showCloseButton: true,
    width: '807px',
  })
}

export const showConditionScriptOverwriteConfirmationOverlay = ({
  script,
  onConfirm,
  onCancel = () => {},
}) => {
  htmlPopup({
    html: (
      <ConditionScriptOverwriteConfirmation
        script={script}
        onConfirmChanges={onConfirm}
        onCancelChanges={onCancel}
      />
    ),
    title: '',
    showCloseButton: true,
    width: '807px',
  })
}
