import React from 'react'
import { SettingsWrapper } from 'components/UIComponents'
import { ShowComment, ShowTitle } from './Attributes'

export const FileMetaDataSettings = ({
  question,
  handleUpdate,
  isAdvanced = false,
}) => (
  <SettingsWrapper title="File metadata" isAdvanced={isAdvanced}>
    <ShowTitle
      showTitle={{ ...question?.attributes?.showTitle }}
      update={(changes) =>
        handleUpdate({
          showTitle: {
            ...question.attributes?.showTitle,
            ...changes,
          },
        })
      }
    />
    <div className="mt-3">
      <ShowComment
        showComment={{ ...question?.attributes?.showComment }}
        update={(changes) =>
          handleUpdate({
            showComment: {
              ...question.attributes?.showComment,
              ...changes,
            },
          })
        }
      />
    </div>
  </SettingsWrapper>
)
