import React from 'react'
import { Link } from 'react-router-dom'

export const Home = () => {
  return (
    <div>
      <h1>{'Home'} {process.env.REACT_APP_DEMO_MODE ? 'Demo' :  ''}</h1>
      <ul>
        <li>
          <Link to="/editor">Editor</Link>
        </li>
      </ul>
    </div>
  )
}
