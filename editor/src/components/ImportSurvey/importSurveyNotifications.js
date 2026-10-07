import React from 'react'

import { ExclamationMark } from 'components/icons'
import SmallCheckMarkIcon from 'components/icons/SmallCheckMarkIcon'
import { Toast } from 'helpers'

const BASE_OPTIONS = {
  position: 'top-center',
}

export const showImportError = (message) =>
  Toast({
    ...BASE_OPTIONS,
    message,
    className: 'import-survey-toast import-survey-toast--error',
    leftIcon: <ExclamationMark />,
    duration: 8000,
  })

export const showImportSuccess = () =>
  Toast({
    ...BASE_OPTIONS,
    message: t('Survey imported successfully'),
    className: 'import-survey-toast import-survey-toast--success',
    leftIcon: <SmallCheckMarkIcon />,
    rightIcon: '',
    duration: 5000,
  })
