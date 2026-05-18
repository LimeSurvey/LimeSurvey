import { RouterProvider } from 'react-router-dom'

import { I18Provider } from './providers/I18nextProvider'
import { i18nInstance } from 'i18nInit'
import routes from 'routes'

import { createRouter } from 'appInstrumentation'

export const RouterContainer = () => {
  const router = createRouter(routes)

  return (
    <I18Provider i18n={i18nInstance}>
      <RouterProvider router={router} />
    </I18Provider>
  )
}
