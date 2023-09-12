import classNames from 'classnames'

import { ContentEditor } from 'components/UIComponents'

export const Equation = ({
  handleUpdate,
  question,
  language,
  isHovered,
  isFocused,
}) => {
  return (
    <div className="mb-3">
      <h2>
        <ContentEditor
          className={classNames(
            'p-0 m-0',
            question.attributes?.cssclass?.value
          )}
          placeholder={'Write your equation here.'}
          useRichTextEditor={isFocused || isHovered}
          update={(answerExample) => handleUpdate({ answerExample })}
          value={question.answerExample}
          focus={question.tempFocusTitle}
          style={{
            width: question.answerExample ? 'fit-content' : '320px',
            minWidth: '320px',
          }}
          language={language}
          isFocused={isFocused}
          replaceVariables={true}
        />
      </h2>
    </div>
  )
}
