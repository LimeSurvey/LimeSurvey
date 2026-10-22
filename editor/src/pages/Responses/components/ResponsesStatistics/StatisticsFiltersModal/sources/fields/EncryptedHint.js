import React from 'react'

export const EncryptedHint = () => (
  <div className="text-muted responses-statistics-filters-row-hint">
    {t(
      'This field is encrypted and can only be searched by exact match. Please enter the exact value you are looking for.'
    )}
  </div>
)
