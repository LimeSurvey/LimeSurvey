import { useQuery } from '@tanstack/react-query'

import { queryClient } from 'query'
import { useAuth } from 'hooks'

export const useSurvey = (id) => {
  const auth = useAuth()

  let { data } = useQuery(
    ['survey', id],
    async () => {
      if (process.env.REACT_APP_DEMO_MODE) {
        const url = id
          ? './data/survey-detail.json'
          : './data/survey-detail-empty.json'

        const res = await fetch(url)
        return await res.json()
      } else {
        const url = 'http://ls-ce/index.php/rest/v1/survey-detail/' + id
        if (auth && auth.token) {
          let headers = {
              mode: 'cors',
              Authorization: 'Bearer ' + auth.token
          }
          const res = await fetch(url, {headers})
          return await res.json()
        }
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
