// plugins available slots to inject custom content/logic into
export const PLUGIN_SLOTS = {
  TOP_BAR_RIGHT: 'topbar:right',
  SHARING_PANEL_EXTRA_MENU: 'sharingpanel:extra:menu',
  SURVEY_SETTINGS_BLOCK_TOKENS_BOTTOM: 'surveysettingsblock:tokens:bottom',
  SHARING_OVERVIEW_CARD_BOTTOM: 'sharingoverview:card:bottom',
  SHARING_OVERVIEW_SOCIAL_MEDIA_CARD_BOTTOM:
    'sharingoverview:socialmediacard:bottom',
  TRANSLATIONS_PANEL: 'translations:panel:content',
  FEEDBACK_FORM_OPEN: 'feedback:form:open',
  CONTENT_EDITOR: 'content:editor:extra', // extra logic for editable content ( contentEditable & tinymce)
}
