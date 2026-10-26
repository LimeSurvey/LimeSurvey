import React from 'react'

export const LoadingIndicator = ({ isLoadingSurvey }) => {
  return (
    <div
      style={{ height: '100vh' }}
      className="d-flex flex-column justify-content-center align-items-center"
    >
      <span style={{ width: 48, height: 48 }} className="loader mb-4"></span>
      <h1>
        {isLoadingSurvey
          ? t('Loading survey...')
          : t('Loading translations...')}
      </h1>
    </div>
  )
}
