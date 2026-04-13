import React from 'react'
import Skeleton from 'react-loading-skeleton'

export const QuestionSkeleton = React.memo(({ height }) => {
  return (
    <div style={{ height }}>
      <Skeleton height="24px" width="80%" style={{ marginBottom: '5px' }} />
      <Skeleton height="16px" count={3} />
    </div>
  )
})
QuestionSkeleton.displayName = 'QuestionSkeleton'
