import React, { useState } from 'react'

// --- Data Structure (Menu Sections) ---
// Note: Icons are now using Remixicon names (ri-)
const menuSections = [
  {
    title: 'Overview',
    icon: 'ri-list-settings-line',
    items: [
      {
        id: 'general',
        label: 'General',
        isGroup: true,
        icon: 'ri-settings-5-line',
      },
      {
        id: 'text_elements',
        label: 'Text elements',
        icon: 'ri-file-text-line',
      },
      {
        id: 'privacy_policy',
        label: 'Privacy policy',
        icon: 'ri-shield-check-line',
      },
      { id: 'theme_options', label: 'Theme options', icon: 'ri-palette-line' },
      {
        id: 'presentation',
        label: 'Presentation',
        icon: 'ri-slideshow-3-line',
      },
      {
        id: 'participant_settings',
        label: 'Participant settings',
        icon: 'ri-user-settings-line',
      },
      {
        id: 'notifications_data',
        label: 'Notifications & data',
        icon: 'ri-notification-3-line',
      },
      {
        id: 'publication_access',
        label: 'Publication & access',
        icon: 'ri-unlock-line',
      },
      {
        id: 'survey_permissions',
        label: 'Survey permissions',
        icon: 'ri-lock-line',
      },
    ],
  },
  {
    title: 'Survey menu',
    icon: 'ri-more-line',
    items: [
      { id: 'overview_questions', label: 'Overview questions & groups' },
      { id: 'participants', label: 'Participants' },
      { id: 'email_templates', label: 'Email templates' },
      { id: 'failed_emails', label: 'Failed email notifications' },
      { id: 'quotas', label: 'Quotas' },
      { id: 'assessments', label: 'Assessments' },
      { id: 'panel_integration', label: 'Panel integration' },
      { id: 'responses', label: 'Responses', isDisabled: true },
      { id: 'statistics', label: 'Statistics', isDisabled: true },
      { id: 'resources', label: 'Resources' },
      { id: 'simple_plugins', label: 'Simple plugins' },
    ],
  },
]

// --- Reusable Menu Item component ---
const MenuItem = ({ item, isActive, onClick, isGroup }) => {
  const [isHovered, setIsHovered] = useState(false)

  // Base classes
  let classes =
    'sidebar__item d-flex align-center cursor-pointer position-relative'

  if (item.isDisabled) {
    classes += ' text-secondary opacity-75 cursor-not-allowed'
  } else if (isGroup) {
    // Override for the distinct "General" group header style
    classes = 'group-header color-dark pt-3 pb-1 ps-2 small fw-semibold'
  } else if (isActive) {
    classes += ' sidebar__item--active color-white fw-bold shadow-sm'
  } else {
    classes += ' color-secondary sidebar__item--hover'
  }

  // Determine if the tooltip should be shown (hovered, and not a group or disabled item)
  const showTooltip = isHovered && !isGroup && !item.isDisabled

  return (
    <div
      className={classes}
      onClick={() => !item.isDisabled && !isGroup && onClick(item.id)}
      onMouseEnter={() => !item.isDisabled && !isGroup && setIsHovered(true)}
      onMouseLeave={() => setIsHovered(false)}
    >
      {item.icon && (
        // Remixicons are used here (ri-)
        <i
          className={`${item.icon} icon-size me-3 ${isActive ? 'color-white' : 'color-success'}`}
        ></i>
      )}

      <span className="small">{item.label}</span>

      {/* Tooltip visible on hover */}
      {showTooltip && (
        <div className="tooltip-custom position-absolute bg-dark color-white small px-3 py-1 rounded shadow-lg text-nowrap z-10">
          {item.label}
          <div className="tooltip-arrow position-absolute"></div>
        </div>
      )}
    </div>
  )
}

// --- Reusable Navigation Section component ---
const NavSection = ({ section, activeItemId, setActiveItemId }) => (
  <div className="mt-4">
    <div className="d-flex align-center small fw-semibold color-muted mb-2 px-2">
      {/* Icon for main section headers */}
      <i className={`${section.icon} me-2 icon-size color-secondary`}></i>
      {section.title}
      {/* Display the vertical ellipsis icon next to "Survey menu" */}
      {section.title === 'Survey menu' && (
        <i className="ri-more-2-fill ms-auto icon-size color-secondary cursor-pointer"></i>
      )}
    </div>
    <div className="d-grid gap-1">
      {section.items.map((item) => (
        <MenuItem
          key={item.id}
          item={item}
          isActive={item.id === activeItemId}
          onClick={setActiveItemId}
          isGroup={item.isGroup}
        />
      ))}
    </div>
  </div>
)

// --- Dedicated Sidebar Component ---
export const Sidebar = ({ activeItemId, setActiveItemId }) => (
  <div className="sidebar-layout border-right bg-white sidebar-shadow">
    {menuSections.map((section) => (
      <NavSection
        key={section.title}
        section={section}
        activeItemId={activeItemId}
        setActiveItemId={setActiveItemId}
      />
    ))}
  </div>
)
