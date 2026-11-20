import React from 'react'
import { useParams } from 'react-router-dom'

import { useFileService } from 'hooks'

import { HtmlEditorModal } from './HtmlEditorModal'

/**
 * The HTML editor of a text in a survey, with "Insert image". Render it only
 * while it is open: The file service needs the login data, which every text
 * field would otherwise load.
 *
 * @param {string} value the HTML when the editor opens
 * @param {Function} onSave called with the edited HTML
 * @param {Function} onHide called when the editor is closed without saving
 * @returns {JSX.Element} the editor
 */
const SurveyHtmlEditor = ({ value, onSave, onHide }) => {
  const { fileService } = useFileService()

  return (
    <HtmlEditorModal
      show
      value={value}
      onSave={onSave}
      onHide={onHide}
      fileService={fileService}
    />
  )
}

/**
 * The HTML editor of a text: With "Insert image" inside a survey, without it
 * elsewhere (there is no survey to save the image in).
 *
 * @param {string} value the HTML when the editor opens
 * @param {Function} onSave called with the edited HTML
 * @param {Function} onHide called when the editor is closed without saving
 * @returns {JSX.Element} the editor
 */
export const ContentHtmlEditor = ({ value, onSave, onHide }) => {
  const { surveyId } = useParams()

  return surveyId ? (
    <SurveyHtmlEditor value={value} onSave={onSave} onHide={onHide} />
  ) : (
    <HtmlEditorModal show value={value} onSave={onSave} onHide={onHide} />
  )
}
