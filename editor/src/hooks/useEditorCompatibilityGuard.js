import { useEffect } from 'react'

import { getSiteUrl } from 'helpers'

import { useSurvey } from './useSurvey'

/**
 * Redirects to the classic survey overview when the survey's theme is not
 * supported by the new editor.
 *
 * The editor only supports 'fruity_twentythree' and themes extending it.
 *
 * Only returns true once the survey is known to be incompatible. While the
 * survey is still loading it returns false, so callers keep rendering their
 * normal loading state instead of blanking the screen.
 *
 * @param {string} surveyId survey id from the route params
 * @returns {boolean} true when the survey must not be shown in the editor
 */
export const useEditorCompatibilityGuard = (surveyId) => {
  const { survey } = useSurvey(surveyId)

  // The survey query key is not scoped to the survey id, so right after a
  // route change the cache can still hold the previously opened survey.
  // Acting on that payload would redirect the wrong survey, so wait until
  // the loaded survey actually matches the route.
  const isSurveyForRoute =
    survey?.sid !== undefined && Number(survey.sid) === Number(surveyId)

  const isIncompatible = isSurveyForRoute && survey.isEditorCompatible === false

  useEffect(() => {
    if (isIncompatible) {
      window.location.replace(
        getSiteUrl('/surveyAdministration/view/surveyid/' + surveyId)
      )
    }
  }, [isIncompatible, surveyId])

  return isIncompatible
}
