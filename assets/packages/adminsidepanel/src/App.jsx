import { useState } from 'react'
import { menuSections } from './utils/menuSections'
import { Sidebar } from './components/Sidebar'

import './scss/index.scss'

// --- Main Application Component ---
const App = ({ initialUserId, initialSurveyId }) => {
  // State for the currently active menu item
  const [activeItemId, setActiveItemId] = useState('text-elements')

  // You can use initialUserId and initialSurveyId here for logic/state setup
  console.log(`App initialized with Survey ID: ${initialSurveyId}`)

  return (
    <div className="sidebar-container">
      {/* Custom CSS to replace SCSS for a single file component */}
      <style>
        {`
                    @import url('https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css');

                    :root {
                        --color-success: #198754; 
                        --color-white: #ffffff;
                        --color-secondary: #6c757d;
                        --color-muted: #868e96;
                        --color-bg-light: #f8f9fa;
                    }

                    .font-semibold { font-weight: 600; }
                    .font-bold { font-weight: 700; }
                    .text-color-secondary { color: var(--color-secondary); }
                    .text-color-muted { color: var(--color-muted); }
                    .color-success { color: var(--color-success); }
                    .color-white { color: var(--color-white); }
                    .hover-bg-light:hover { background-color: var(--color-bg-light); }
                    .item-padding { padding: 0.5rem 1rem; }
                    .transition-colors { transition: background-color 0.2s, color 0.2s; }
                    
                    .active-item-bg {
                        background-color: var(--color-success);
                        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
                    }

                    .sidebar-container {
                        width: 250px;
                        padding: 1rem;
                        border-right: 1px solid #dee2e6;
                        background-color: var(--color-white);
                        height: 100vh;
                        display: flex;
                        flex-direction: column;
                        gap: 1rem;
                    }
                    
                    .tooltip-custom {
                        /* Match Sidebar item height to ensure vertical centering */
                        transform: translate(110%, -50%); /* Adjust to float right */
                        top: 50%;
                        left: auto;
                        right: 0;
                        margin-left: 0.5rem; /* Space between item and tooltip */
                        pointer-events: none; /* Allows clicks to go through */
                        opacity: 0.95;
                        z-index: 1050; /* Above typical content */
                    }
                `}
      </style>

      <div className="d-flex align-items-center mb-4">
        <i className="ri-settings-3-fill me-2 fs-4 color-success"></i>
        <h5 className="mb-0 font-bold">Survey Settings</h5>
      </div>

      <Sidebar activeItemId={activeItemId} setActiveItemId={setActiveItemId} />
    </div>
  )
}

export default App
