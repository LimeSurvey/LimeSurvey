import React from 'react'
import {
  AllowedFileTypes,
  MaximumFileSizeAllowed,
  MaxNumberFiles,
  MinNumberFiles,
} from '../Attributes'

export const FileUploadSettings = ({ question, handleUpdate }) => (
  <div>
    <MaxNumberFiles
      min={question?.attributes?.minNumberFiles?.value}
      maxNumberFiles={{ ...question?.attributes?.maxNumberFiles }}
      update={(changes) =>
        handleUpdate({
          maxNumberFiles: {
            ...question.attributes?.maxNumberFiles,
            ...changes,
          },
        })
      }
    />
    <div className="mt-3">
      <MinNumberFiles
        max={question?.attributes?.maxNumberFiles?.value}
        minNumberFiles={{ ...question?.attributes?.minNumberFiles }}
        update={(changes) =>
          handleUpdate({
            minNumberFiles: {
              ...question.attributes?.minNumberFiles,
              ...changes,
            },
          })
        }
      />
    </div>
    <div className="mt-3">
      <AllowedFileTypes
        allowedFileTypes={{ ...question?.attributes?.allowedFileTypes }}
        update={(changes) =>
          handleUpdate({
            allowedFileTypes: {
              ...question.attributes?.allowedFileTypes,
              ...changes,
            },
          })
        }
      />
    </div>
    <div className="mt-3">
      <MaximumFileSizeAllowed
        maximumFileSizeAllowed={{
          ...question?.attributes?.maximumFileSizeAllowed,
        }}
        update={(changes) =>
          handleUpdate({
            maximumFileSizeAllowed: {
              ...question.attributes?.maximumFileSizeAllowed,
              ...changes,
            },
          })
        }
      />
    </div>
  </div>
)
