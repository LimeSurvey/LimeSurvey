import router from 'router'
import { RouterProvider } from 'react-router-dom'
import ThemeProvider from 'react-bootstrap/ThemeProvider'
import { ReactQueryDevtools } from '@tanstack/react-query-devtools'
import { PersistQueryClientProvider } from '@tanstack/react-query-persist-client'
import { CookiesProvider } from 'react-cookie';
import { queryClient, persistOptions } from 'query'


import 'bootstrap/dist/css/bootstrap.min.css'
import 'themes/index.scss'

function App() {
  if (
    process.env.NODE_ENV !== 'development' &&
    !localStorage.getItem('authorized')
  ) {
    const text = window.prompt('Enter your password to continue:', '')
    if (text === 'limesurvey@123') {
      localStorage.setItem('authorized', true)
    } else {
      window.location.reload()
    }
  }

  return (
    <ThemeProvider breakpoints={['lg', 'xl']}>
      <PersistQueryClientProvider
        client={queryClient}
        persistOptions={persistOptions}
      >
        <CookiesProvider defaultSetOptions={{ path: '/' }}>
            <RouterProvider router={router} />
        </CookiesProvider>
        <ReactQueryDevtools initialIsOpen={false} />
      </PersistQueryClientProvider>
    </ThemeProvider>
  )
}

export default App
