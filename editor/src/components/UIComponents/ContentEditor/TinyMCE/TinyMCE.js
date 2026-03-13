import React, { useEffect, useMemo, useRef, useState } from 'react'
import { Editor } from '@tinymce/tinymce-react'
import { htmlPopup, RandomNumber } from 'helpers'
import beautify from 'js-beautify'

import { CodeEditor } from '../CodeMirror/CodeMirror'
import { mceToolbar } from './mceToolbar'
import { toolbarActions } from './toolbarActions'

const FORCED_ROOT_BLOCK = 'p'

export const TinyMCE = ({
  testId,
  disabled = false,
  onBlur,
  onFocus,
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
    value.replace(/(\{[^{}]+\})/g, '<badge>$1</badge>')
  )
  const [isFocused, setIsFocused] = useState(false)
  const codeToQuestionRef = useRef(codeToQuestion)
  const attributeDescriptionsRef = useRef(attributeDescriptions)
  const editorValueRef = useRef(editorValue)

  const openHtmlEditorRef = useRef()
  // If we don't use a ref, the value will be outdated and things will break.
  openHtmlEditorRef.current = () => {
    const currentHtml = editorValueRef.current

    const formattedHTML = beautify.html(currentHtml, {
      indent_size: 2,
      wrap_line_length: 120,
      preserve_newlines: true,
    })

    htmlPopup({
      title: t('Edit HTML'),
      html: <CodeEditor value={formattedHTML} />,
      showCloseButton: true,
      showCancelButton: true,
      showConfirmButton: true,
      confirmButtonText: 'Save',
      cancelButtonText: 'Cancel',
      width: '80vw',
    }).then((result) => {
      if (result.isConfirmed) {
        const newHtmlContent =
          document.getElementById('code-mirror').dataset.value

        setEditorValue(newHtmlContent)
        handleOnChange(newHtmlContent)
        editorValueRef.current = newHtmlContent
      }
    })
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

  const normalizeContent = () => {
    if (isFocused) {
      return
    }

    const normalizedContent = editorValueRef.current.replace(
      /<badge>(\{[^{}]+\})<\/badge>/g,
      '$1'
    )

    setEditorValue(normalizedContent)
  }

  const transformTokensToBadges = () => {
    if (isFocused) {
      return
    }

    const transformedContent = editorValueRef.current.replace(
      /(\{[^{}]+\})/g,
      '<badge>$1</badge>'
    )

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
        plugins: ['link'],
        verify_html: false,
        disabled,
        toolbar: showToolbar
          ? 'alignmentMenu customBold customItalic link toolbarActions'
          : false,
        selector: id,
        forced_root_block: FORCED_ROOT_BLOCK,
      }}
    />
  )
}
