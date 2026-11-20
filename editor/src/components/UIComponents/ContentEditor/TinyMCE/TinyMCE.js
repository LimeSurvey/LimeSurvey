import React, { useEffect, useMemo, useRef, useState } from 'react'
import { Editor } from '@tinymce/tinymce-react'
import {
  RandomNumber,
  removePlaceholderBadges,
  wrapPlaceholdersInBadges,
} from 'helpers'
import beautify from 'js-beautify'

import { ContentHtmlEditor } from '../HtmlEditorModal/ContentHtmlEditor'
import { mceToolbar } from './mceToolbar'
import { toolbarActions } from './toolbarActions'
import { PluginSlot } from 'plugins/PluginSlot'
import { PLUGIN_SLOTS } from 'plugins/slots'

const FORCED_ROOT_BLOCK = 'p'

export const TinyMCE = ({
  testId,
  disabled = false,
  onBlur,
  onFocus,
  onKeyDown = () => {},
  handleOnChange,
  placeholder,
  value = '',
  focus,
  showToolbar = false,
  questionNumber,
  codeToQuestion = [],
  attributeDescriptions = {},
  surveyHeader = false,
  language,
}) => {
  const editorRef = useRef(null)
  const [firstLoad, setFirstLoad] = useState(true)
  const [isDisabled, setIsDisabled] = useState(disabled)
  const [editorValue, setEditorValue] = useState(
    wrapPlaceholdersInBadges(value)
  )
  const [isFocused, setIsFocused] = useState(false)
  const codeToQuestionRef = useRef(codeToQuestion)
  const attributeDescriptionsRef = useRef(attributeDescriptions)
  const editorValueRef = useRef(editorValue)

  // The HTML shown in the HTML editor, null while it is closed
  const [htmlEditorValue, setHtmlEditorValue] = useState(null)

  const openHtmlEditorRef = useRef()
  // If we don't use a ref, the value will be outdated and things will break.
  openHtmlEditorRef.current = () => {
    const currentHtml = editorValueRef.current

    const formattedHTML = beautify.html(currentHtml, {
      indent_size: 2,
      wrap_line_length: 120,
      preserve_newlines: true,
    })

    setHtmlEditorValue(formattedHTML)
  }

  const saveHtml = (newHtmlContent) => {
    setHtmlEditorValue(null)
    setEditorValue(newHtmlContent)
    handleOnChange(newHtmlContent)
    editorValueRef.current = newHtmlContent
  }

  const id = useMemo(() => {
    return `${RandomNumber(0, 99999)}-${RandomNumber(0, 99999)}-${RandomNumber(0, 99999)}`
  }, [])

  const onEditorChange = (newValue) => {
    if (firstLoad || disabled || !isFocused) {
      return
    }

    handleOnChange(newValue)
    editorValueRef.current = newValue
  }

  const updateTitle = (newValue) => {
    if (firstLoad || disabled) {
      return
    }
    setEditorValue(newValue)
    handleOnChange(newValue)
    editorValueRef.current = newValue
  }

  const normalizeContent = () => {
    if (isFocused) {
      return
    }

    const normalizedContent = removePlaceholderBadges(editorValueRef.current)

    setEditorValue(normalizedContent)
  }

  const transformTokensToBadges = () => {
    if (isFocused) {
      return
    }

    const transformedContent = wrapPlaceholdersInBadges(editorValueRef.current)

    setEditorValue(transformedContent)
  }

  const handleOnFocus = () => {
    setIsDisabled(false)
    setIsFocused(true)
    normalizeContent()
    onFocus()
  }

  const handleOnBlur = () => {
    setIsDisabled(disabled || false)
    setIsFocused(false)
    transformTokensToBadges()
    onBlur()
  }

  useEffect(() => {
    codeToQuestionRef.current = codeToQuestion
    attributeDescriptionsRef.current = attributeDescriptions
  }, [codeToQuestion, attributeDescriptions])

  useEffect(() => {
    if (editorRef.current) {
      if (firstLoad) {
        setFirstLoad(false)
      }

      if (focus) {
        editorRef.current.focus()
      }
    }
  }, [editorRef.current])

  return (
    <>
      <Editor
        onInit={(evt, editor) => {
          editorRef.current = editor
          setFirstLoad(false)
          const contentArea = editor.getBody()
          contentArea.setAttribute('data-testid', testId)
        }}
        tinymceScriptSrc={`${process.env.PUBLIC_URL}/tinymce/js/tinymce/tinymce.min.js`}
        onEditorChange={onEditorChange}
        id={id}
        disabled={isDisabled}
        initialValue={editorValue}
        onFocus={handleOnFocus}
        onBlur={handleOnBlur}
        onMouseEnter={normalizeContent}
        onMouseLeave={transformTokensToBadges}
        init={{
          setup: (editor) => {
            mceToolbar(editor)
            editor.on('keydown', onKeyDown)
            toolbarActions(
              editor,
              openHtmlEditorRef,
              codeToQuestionRef,
              attributeDescriptionsRef,
              questionNumber,
              language,
              surveyHeader
            )
          },
          placeholder,
          menubar: false,
          branding: false,
          inline: true,
          license_key: 'gpl',
          valid_elements: '*[*]',
          valid_styles: '*[*]',
          formats: {
            underline: { inline: 'u', exact: true },
          },
          plugins: ['link'],
          // Pasted or dropped image files would otherwise be embedded as huge base64 data URIs into the text
          paste_data_images: false,
          verify_html: false,
          // Keep URLs as written: TinyMCE would otherwise rewrite image paths
          // relative to the editor page (/editor/), breaking them in the survey
          convert_urls: false,
          disabled,
          toolbar: showToolbar
            ? 'alignmentMenu customBold customItalic customUnderline link toolbarActions'
            : false,
          selector: id,
          forced_root_block: FORCED_ROOT_BLOCK,
        }}
      />
      {htmlEditorValue !== null && (
        <ContentHtmlEditor
          value={htmlEditorValue}
          onSave={saveHtml}
          onHide={() => setHtmlEditorValue(null)}
        />
      )}
      <PluginSlot
        slotName={PLUGIN_SLOTS.CONTENT_EDITOR}
        value={editorValueRef.current}
        onChange={updateTitle}
        focused={isFocused}
      />
    </>
  )
}
