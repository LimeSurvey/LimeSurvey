import { createHashRouter } from 'react-router-dom'

import { Editor, Home } from 'pages'

const router = createHashRouter([
  {
    path: '/',
    element: <Home />,
  },
  {
    path: '/editor',
    element: <Editor />,
  },
  {
    path: '/editor/:id',
    element: <Editor />,
  },
])

export default router
