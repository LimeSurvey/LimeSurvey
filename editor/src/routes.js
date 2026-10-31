import { Outlet } from 'react-router-dom'
import { Editor } from 'pages'
import AuthGate from 'components/AuthGate'

import { Unauthorized, NotFound } from './pages/Errors'
import { Responses } from 'pages/Responses/Responses'
import { SharingPanel } from 'pages/SharingPanel/SharingPanel'
import { EditorContextController, SurveyWorkspace } from 'components'
import { PermissionsProvider } from 'providers/PermissionsProvider'
import { TopBar } from 'components/TopBar'
import { useAppState } from 'hooks'
import { STATES } from 'helpers'
import { PluginSlot } from 'plugins/PluginSlot'
import { PLUGIN_SLOTS } from 'plugins/slots'

const RootLayout = () => {
  const [topbarConfig] = useAppState(STATES.TOPBAR_CONFIG)

  return (
    <>
      {topbarConfig && <TopBar {...topbarConfig} />}
      <Outlet />
    </>
  )
}

const GuardedSurveyRoute = ({ children }) => (
  <AuthGate>
    <PluginSlot slotName={PLUGIN_SLOTS.APP_ROUTE_GUARD} fallback={children}>
      {children}
    </PluginSlot>
  </AuthGate>
)

const routes = [
  {
    element: <RootLayout />,
    children: [
      // Demo mode exposes a bare /survey route without a surveyId — only included when REACT_APP_DEMO_MODE=true
      ...(process.env.REACT_APP_DEMO_MODE === 'true'
        ? [
            {
              path: '/survey',
              element: (
                <AuthGate>
                  <Editor />
                </AuthGate>
              ),
            },
          ]
        : []),
      {
        path: '/responses/:surveyId/:panel?/:menu?',
        element: (
          <GuardedSurveyRoute>
            <PermissionsProvider>
              <Responses />
            </PermissionsProvider>
          </GuardedSurveyRoute>
        ),
      },
      {
        path: '/survey/:surveyId/:panel?/:menu?',
        element: (
          <GuardedSurveyRoute>
            <PermissionsProvider>
              <SurveyWorkspace>
                <EditorContextController>
                  <Outlet />
                  <Editor />
                </EditorContextController>
              </SurveyWorkspace>
            </PermissionsProvider>
          </GuardedSurveyRoute>
        ),
      },
      {
        path: '/sharing/:surveyId/:panel?/:menu?',
        element: (
          <GuardedSurveyRoute>
            <PermissionsProvider>
              <SurveyWorkspace>
                <SharingPanel />
              </SurveyWorkspace>
            </PermissionsProvider>
          </GuardedSurveyRoute>
        ),
      },
      {
        path: '/401',
        element: <Unauthorized />,
      },
      {
        path: '/404',
        element: <NotFound />,
      },
      {
        path: '*',
        element: <NotFound />,
      },
    ],
  },
]

export default routes
