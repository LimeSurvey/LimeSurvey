import { Survey } from './Survey'
import { PersistQueryClientProvider } from '@tanstack/react-query-persist-client'
import { queryClient, persistOptions } from 'query'

export default {
  title: 'General/Survey',
  decorators: [
    (Story) => {
      return (
        <PersistQueryClientProvider
          client={queryClient}
          persistOptions={persistOptions}
        >
          <Story />
        </PersistQueryClientProvider>
      )
    },
  ],
}

const surveyId = '78f91e52-6028-11ed-82e1-7ac846e3af9d'

export const Basic = () => <Survey id={surveyId} />
