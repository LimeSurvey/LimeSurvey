import { useQuery } from '@tanstack/react-query'

import { queryClient } from 'query'

export const useSurvey = (id) => {
  let { data } = useQuery(
    ['survey', id],
    async () => {
      if (process.env.REACT_APP_DEMO_MODE) {
        // temporary way to have multiple surveys
        const link = id
          ? './data/survey-detail-empty.json'
          : './data/survey-detail.json'

        const res = await fetch(link)
        return await res.json()
      }
    },
    {
      staleTime: Infinity,
      cacheTime: Infinity,
    }
  )

  const update = (updateData) => {
    let updatedSurvey = { ...data.survey, ...updateData }
    if (data.survey.isSaved) {
      updatedSurvey = { ...data.survey, ...updateData, isSaved: false }
    }
    return queryClient.setQueriesData(['survey', id], {
      survey: updatedSurvey,
    })
  }

  // const persistCurrentState = async () =>
  //   fetch('/data/survey.json', {
  //     method: 'POST',
  //     body: JSON.stringify(survey),
  //   })
  const persistCurrentState = () => {
    queryClient.setQueryData(['survey', id], { ...data.survey })
  }

  return {
    survey: data?.survey || {},
    update,
    save: persistCurrentState,
    language: data?.survey?.language,
  }
}
