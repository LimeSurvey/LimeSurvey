import React from 'react'
import {
  BackwardNavigation,
  ShowProgressBar,
  LoadEndUrl,
  Format,
} from './Attributes'

export const PresentationSettings = () => {
  return (
    <div className="mt-5 p-4 bg-white">
      <BackwardNavigation />
      <ShowProgressBar />
      <LoadEndUrl />
      <Format />
    </div>
  )
}
