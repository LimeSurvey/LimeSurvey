import { useState } from 'react'

import { useAppState, useTranslationsService } from './index'
import { STATES } from '../helpers'

/* eslint-disable no-console */
/**
 * Custom hook to fetch and store all available languages for the language selects
 * including users choice of UI language and all survey languages.
 *
 * - Fetches missing languages using `translationsService.getAllLanguages()`.
 * - Ensures no duplicate or already fetched languages are reloaded.
 * - Updates the global state ALL_AVAILABLE_LANGUAGES with fetched languages.
 *
 * @param {string[]} surveyLanguages - List of languages related to the survey.
 * @returns {object} languages - The state containing all available languages.
 */
export const useSetAllLanguages = () => {
  const [languages, setLanguages] = useAppState(STATES.ALL_AVAILABLE_LANGUAGES)
  const [userDetail] = useAppState(STATES.USER_DETAIL)
  const { translationsService } = useTranslationsService()
  const [isLoading, setIsLoading] = useState(false)

  const fetchAllLanguages = (surveyLanguages = []) => {
    if (!userDetail?.lang || process.env.STORYBOOK_DEV) {
      return
    }

    if (!isLoading) {
      setIsLoading(true)
      const languagesToFetch = [userDetail.lang, ...surveyLanguages].filter(
        (lang, index, self) =>
          !(languages || {})[lang] && self.indexOf(lang) === index
      )

      if (languagesToFetch.length === 0) {
        setIsLoading(false)
        return
      }

      Promise.all(
        languagesToFetch.map((lang) =>
          translationsService
            .getAllLanguages(lang)
            .then((allLanguages) => ({ [lang]: allLanguages }))
            .catch((error) => {
              console.error(`Error fetching languages for ${lang}:`, error)
              return null
            })
        )
      )
        .then((results) => {
          const newLanguages = results.reduce((acc, result) => {
            if (result) {
              return { ...acc, ...result }
            }
            return acc
          }, {})
          setLanguages((prevLanguages) => ({
            ...prevLanguages,
            ...newLanguages,
          }))
        })
        .catch((error) => {
          console.error('Error in fetching languages:', error)
        })
        .finally(() => {
          setIsLoading(false)
        })
    }
  }

  return { fetchAllLanguages }
}
