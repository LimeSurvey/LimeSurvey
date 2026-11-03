import React from 'react'

import { ToggleButtons } from 'components'

import { FILE_UPLOADED } from '../../utils'

const fileUploadedOptions = () => [
  { name: t('Yes'), value: FILE_UPLOADED.YES },
  { name: t('No'), value: FILE_UPLOADED.NO },
]

// File upload → "did they upload a file?" Yes / No segmented toggle.
export const FileUploadedToggle = ({ id, value, onChange }) => (
  <ToggleButtons
    id={id}
    toggleOptions={fileUploadedOptions()}
    value={value}
    onChange={onChange}
  />
)
