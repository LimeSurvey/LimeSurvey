export const menuSections = [
  {
    title: 'Overview',
    icon: 'bi-list-task',
    items: [
      { id: 'general', label: 'General', isGroup: true, icon: 'bi-gear' },
      {
        id: 'text_elements',
        label: 'Text elements',
        icon: 'bi-file-text',
        showTooltip: true,
      },
      {
        id: 'privacy_policy',
        label: 'Privacy policy',
        icon: 'bi-shield-check',
      },
      { id: 'theme_options', label: 'Theme options', icon: 'bi-palette' },
      { id: 'presentation', label: 'Presentation', icon: 'bi-tv' },
      {
        id: 'participant_settings',
        label: 'Participant settings',
        icon: 'bi-person-gear',
      },
      {
        id: 'notifications_data',
        label: 'Notifications & data',
        icon: 'bi-bell',
      },
      {
        id: 'publication_access',
        label: 'Publication & access',
        icon: 'bi-unlock',
      },
      {
        id: 'survey_permissions',
        label: 'Survey permissions',
        icon: 'bi-lock',
      },
    ],
  },
  {
    title: 'Survey menu',
    icon: 'bi-three-dots',
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
