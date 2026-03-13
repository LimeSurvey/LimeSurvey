import React from 'react'

import { DropZone } from 'components/UIComponents'
import { Entities } from 'helpers'
import { useFileService } from 'hooks'

const ThemeOptionsImageUpload = ({ previewUrl, update, setting }) => {
  const EMPTY_ZONE_TEXT = t('Drop image here or click here to select a file')
  const { fileService } = useFileService()

  const onChange = (filePath) => {
    if (!filePath) {
      return
    }
    const nativeKey = setting?.keyPath.split('.').pop() // brandlogo | backgroundimage
    const fileName = filePath.split('/').pop()
    const updateValue = `image::survey::${fileName}`

    const settingOptions = {
      entity: Entities.themeSettings,
      keyPath: `themesettings.${nativeKey}`,
      type: 'dropdown',
      formatUpdateValue: () => {
        const customSettingUpdate = {
          updateValueKey: nativeKey,
          updateOperationKey: nativeKey,
          updateValue: updateValue,
          operationValue: fileName,
          filePath,
        }

        customSettingUpdate.secondaryKey = nativeKey
        customSettingUpdate.secondaryValue = updateValue

        return customSettingUpdate
      },
    }
    update(updateValue, settingOptions)
  }

  // to update imageFileList ( used for select ) when new image is uploaded
  const onDelete = () => {
    const nativeKey = setting?.keyPath.split('.').pop() // brandlogo | backgroundimage
    const updateValue = null

    const settingOptions = {
      entity: Entities.themeSettings,
      keyPath: `themesettings.${nativeKey}`,
      formatUpdateValue: () => {
        return {
          updateValueKey: nativeKey,
          updateOperationKey: nativeKey,
          updateValue,
          operationValue: updateValue,
        }
      },
    }

    update(updateValue, settingOptions)
  }

  return (
    <div className="mt-3">
      <DropZone
        fileService={fileService}
        emptyZoneText={EMPTY_ZONE_TEXT}
        previewUrlInit={
          previewUrl ? process.env.REACT_APP_SITE_URL + previewUrl : null
        }
        cleanPreviewOnInitValueChange={true}
        onChange={onChange}
        onDelete={onDelete}
        previewCover={false}
      />
    </div>
  )
}

export default ThemeOptionsImageUpload
