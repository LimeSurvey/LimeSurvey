// plugins available slots to inject custom content/logic into
export const PLUGIN_SLOTS = {
  TOP_BAR_RIGHT: 'topbar:right',
  EDITOR_TOP: 'editor:top',
  EDITOR_LAYOUT_EXTRA: 'editor:layout:extra',
  SHARING_PANEL_EXTRA_MENU: 'sharingpanel:extra:menu',
  SURVEY_SETTINGS_BLOCK_TOKENS_BOTTOM: 'surveysettingsblock:tokens:bottom',
  SHARING_OVERVIEW_BOTTOM_LEFT: 'sharingoverview:bottom:left',
  SHARING_OVERVIEW_BOTTOM_RIGHT: 'sharingoverview:bottom:right',
  TRANSLATIONS_PANEL: 'translations:panel:content',
  FEEDBACK_FORM_OPEN: 'feedback:form:open',
  CONTENT_EDITOR: 'content:editor:extra', // extra logic for editable content ( contentEditable & tinymce)
}
