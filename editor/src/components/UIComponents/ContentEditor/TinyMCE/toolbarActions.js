import { decodeHtml, L10ns, RemoveHTMLTagsInString } from 'helpers'

export const toolbarActions = (
  editor,
  openHtmlEditorRef,
  codeToQuestionRef = {},
  attributeDescriptionsRef = {},
  questionNumber,
  language,
  surveyHeader
) => {
  editor.ui.registry.addMenuButton('toolbarActions', {
    icon: 'verticalThreeDots',
    type: 'menubutton',
    fetch: (callback) => {
      const codeToQuestion = codeToQuestionRef.current

      const previousAnswerFields = []
      if (questionNumber) {
        const entries = Object.entries(codeToQuestion ?? {})

        for (let i = 0; i < entries.length; i++) {
          const [, { question }] = entries[i]

          if (question.questionNumber >= questionNumber) {
            // we don't break because codeToQuestion is an object, so it the questions might not be sorted by questionNumber
            continue
          }

          const questionTitle = RemoveHTMLTagsInString(
            L10ns({ prop: 'question', language, l10ns: question.l10ns })
          )

          if (question.subquestions?.length) {
            question.subquestions.map((subquestion, index) => {
              const subquestionTitle =
                RemoveHTMLTagsInString(
                  L10ns({
                    prop: 'question',
                    language,
                    l10ns: subquestion.l10ns,
                  })
                ) || `A${index}`

              previousAnswerFields.push({
                title: `${question.title}: [${subquestionTitle}] ${questionTitle}`,
                value: `{${question.title}_${subquestion.title}.shown}`,
              })
            })
          }

          previousAnswerFields.push({
            title: `${question.title}: ${questionTitle}`,
            value: `{${question.title}.shown}`,
          })
        }
      }

      const participantAttributes = Object.entries(
        attributeDescriptionsRef.current ?? {}
      ).map(([key, value]) => ({
        type: 'menuitem',
        text: `${'Participant attribute'}: ${value.description ? value.description : key}`,
        onAction: () =>
          editor.insertContent(`${`{TOKEN:${key.toUpperCase()}}`}`),
      }))

      const previousAnswerItems =
        previousAnswerFields?.map((answerField) => {
          return {
            type: 'menuitem',
            text: decodeHtml(answerField.title),
            onAction: () => editor.insertContent(`${answerField.value}`),
            tooltip: t('My custom button'),
          }
        }) || []

      const questionItems = surveyHeader
        ? []
        : [
            {
              type: 'menuitem',
              text: t('Question ID'),
              onAction: () => editor.insertContent('{QID} '),
            },
            {
              type: 'menuitem',
              text: t('Question ID group'),
              onAction: () => editor.insertContent('{GID} '),
            },
          ]

      callback([
        { type: 'separator', text: t('Font size'), postRerender: true },
        {
          type: 'menuitem',
          text: t('Small'),
          onAction: () => editor.execCommand('FormatBlock', false, 'h3'),
        },
        {
          type: 'menuitem',
          text: t('Medium'),
          onAction: () => editor.execCommand('FormatBlock', false, 'h2'),
        },
        {
          type: 'menuitem',
          text: t('Large'),
          onAction: () => editor.execCommand('FormatBlock', false, 'h1'),
        },
        { type: 'separator' },
        { type: 'separator', text: t('Actions') },
        {
          text: t('Edit as HTML'),
          type: 'menuitem',
          onAction: () => {
            openHtmlEditorRef.current()
          },
        },
        {
          text: t('Placeholder fields'),
          type: 'nestedmenuitem',
          getSubmenuItems: () => [
            {
              type: 'menuitem',
              text: t('Email address participant'),
              onAction: () => editor.insertContent('{TOKEN:EMAIL} '),
            },
            {
              type: 'menuitem',
              text: t('First name participant'),
              onAction: () => editor.insertContent('{TOKEN:FIRSTNAME} '),
            },
            {
              type: 'menuitem',
              text: t('Last name participant'),
              onAction: () => editor.insertContent('{TOKEN:LASTNAME} '),
            },
            ...participantAttributes,
            ...questionItems,
            {
              type: 'menuitem',
              text: t('Survey ID'),
              onAction: () => editor.insertContent('{SID} '),
            },
            {
              type: 'menuitem',
              text: t('Survey expiration date'),
              onAction: () => editor.insertContent('{EXPIRY} '),
            },
            { text: t('Fields based on previous answers'), type: 'separator' },
            ...previousAnswerItems,
          ],
        },
      ])
    },
  })
}
