import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { PersistQueryClientProvider } from '@tanstack/react-query-persist-client'

import surveyData from 'helpers/data/survey-detail.json'
import { queryClient, persistOptions } from '../src/queryClient'
import { I18Provider } from '../src/providers'
import { i18nInstance } from '../src/i18nInit'
import { StoryWrapper } from '../src/sbook/helpers/fixtures/StoryWrapper'
import 'themes/index.scss'

export const preview = {
  decorators: [
    (Story, context) => {
      return (
        <MemoryRouter
          initialEntries={[`/survey/${surveyData.surveyId}/structure/`]}
        >
          <PersistQueryClientProvider
            client={queryClient}
            persistOptions={persistOptions}
          >
            <I18Provider language={'en'} i18n={() => i18nInstance('en')}>
              <Routes>
                <Route
                  path="/survey/:surveyId/:panel?/:menu?"
                  element={<StoryWrapper Story={Story} context={context} />}
                />
              </Routes>
            </I18Provider>
          </PersistQueryClientProvider>
        </MemoryRouter>
      )
    },
  ],

  options: {
    storySort: {
      order: [''],
    },
  },

  tags: ['autodocs', 'autodocs'],
}

export default preview
