import React, { useEffect } from 'react'
import { RouterProvider } from 'react-router-dom'
import ThemeProvider from 'react-bootstrap/ThemeProvider'
import { PersistQueryClientProvider } from '@tanstack/react-query-persist-client'
import { ReactQueryDevtools } from '@tanstack/react-query-devtools'
import routes from 'routes'
import { queryClient, persistOptions } from 'queryClient'
import { I18Provider } from './providers/I18nextProvider'
import { i18nInstance } from 'i18nInit'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'themes/index.scss'
import 'remixicon/fonts/remixicon.css'
import { VersionInfoService } from 'services'
import { getApiUrl, URLS } from 'helpers'

import {
  createRouter,
  AppErrorBoundary,
  withAppProfiler,
  initInstrumentation,
} from 'appInstrumentation'

initInstrumentation()
const router = createRouter(routes)

function App() {
  useEffect(() => {
    const fetchVersionInfoInterval = setInterval(() => {
      const versionInfoService = new VersionInfoService(getApiUrl())
      versionInfoService
        .getVersionInfo()
        .then(({ data: { needsDbUpdate } }) => {
          if (needsDbUpdate) {
            alert(
              t(
                'An update is available. You will be redirected to the admin panel to perform the update.'
              )
            )
            window.location.href = URLS.ADMIN
          }
        })
        .catch(() => {
          // ignore: update is not required
        })
    }, 10 * 1000)

    return () => clearInterval(fetchVersionInfoInterval)
  }, [])

  return (
    <AppErrorBoundary>
      <ThemeProvider breakpoints={['lg', 'xl']}>
        <PersistQueryClientProvider
          client={queryClient}
          persistOptions={persistOptions}
        >
          <I18Provider i18n={i18nInstance}>
            <RouterProvider router={router} />
          </I18Provider>
          <div className="d-none">
            {process.env.REACT_APP_RELEASE}@{process.env.REACT_APP_COMMIT_HASH}
          </div>
          <ReactQueryDevtools initialIsOpen={false} />
        </PersistQueryClientProvider>
      </ThemeProvider>
    </AppErrorBoundary>
  )
}

export default withAppProfiler(App)
