import React from 'react'
import { format } from 'util'

export const FileRejectionItems = ({ fileRejections }) => (
  <ul>
    {fileRejections.map(({ file, errors }) => {
      return (
        <li key={file.path}>
          {file.path} - {format(t('%s bytes'), file.size)}
          <ul>
            {errors.map((e) => (
              <li className="text-danger" key={e.code}>
                {e.message}
              </li>
            ))}
          </ul>
        </li>
      )
    })}
  </ul>
)
