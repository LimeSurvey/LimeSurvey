console.log('Lsadminsidepanel')
import React from 'react'
import { createRoot } from 'react-dom/client'

import './scss/adminsidepanelmain.scss'

// NOTE: Import your main application component here.
// We assume this component (which contains your Sidebar logic) is defined in
// a separate file (e.g., 'App.jsx') and bundled by Vite.
// Replace './App' with the correct path to your main component.
import App from './App'

/**
 * Main function exposed globally to initialize the React application.
 * This function serves as the bridge between the legacy PHP environment
 * and the modern React application.
 * * @param {string|number} userId - The global user ID.
 * @param {string|number} surveyId - The survey ID being edited.
 */
const Lsadminsidepanel = (userId, surveyId) => {
  const container = document.getElementById('vue-sidebar-container')

  if (container) {
    // Use modern React 18 createRoot API to mount the application
    const root = createRoot(container)

    root.render(
      // <-- Here we use 'root'
      <React.StrictMode>
        {/* Render the main App component, passing necessary props */}
        <App initialUserId={userId} initialSurveyId={surveyId} />
      </React.StrictMode>
    )

    console.log(
      `React Sidebar mounted successfully for User: ${userId}, Survey: ${surveyId}`
    )
  } else {
    console.error('Target container #root not found. Cannot mount React app.')
  }

  // Expose namespace utility (if needed by the surrounding PHP application)
  if (
    window.LS &&
    window.LS.adminCore &&
    typeof window.LS.adminCore.addToNamespace === 'function'
  ) {
    // This assumes LS object is available globally.
    window.LS.adminCore.addToNamespace({}, 'adminsidepanel')
  }

  return {}
}

// --- Global Execution Block (Runs the initializer on DOM Ready) ---

// Use vanilla JavaScript's DOMContentLoaded event listener to replace $(document).ready()
document.addEventListener('DOMContentLoaded', () => {
  let surveyId = 'newSurvey'
  let userId = 0

  console.log(document)

  // --- Retrieve dynamic parameters from global window objects ---
  // Access global variables directly without jQuery
  if (window.LS) {
    userId = window.LS.globalUserId || 0
    if (window.LS.parameters) {
      // Prioritize $GET, then keyValuePairs for surveyid
      // Using optional chaining (?) for safe property access
      surveyId =
        window.LS.parameters.$GET?.surveyid ||
        window.LS.parameters.keyValuePairs?.surveyid ||
        surveyId
    }
  }
  if (window.SideMenuData) {
    // Override if SideMenuData provides a specific surveyid
    surveyId = window.SideMenuData.surveyid || surveyId
  }

  // Expose the initializer function globally (or ensure it's defined)
  window.adminsidepanel = window.adminsidepanel || Lsadminsidepanel

  // Run the initializer
  if (window.adminsidepanel) {
    window.adminsidepanel(userId, surveyId)
  }
})
