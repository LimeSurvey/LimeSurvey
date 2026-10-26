import { Entities } from 'helpers'
import { Form } from 'react-bootstrap'

/**
 * @param attribute {object}
 * @param keyPath {string}
 */
export const handleTextareaType = (attribute = {}, keyPath) => {
  return {
    keyPath: `themesettings.${keyPath}`,
    entity: Entities.themeSettings,
    props: {
      id: keyPath,
      mainText: t(attribute.title),
      title: t(attribute.title),
      label: t(attribute.title),
      rows: +attribute.rows,
      as: 'textarea',
      childComponent: Form.Control,
      childOnNewLine: true,
      noPermissionDisabled: true,
    },
  }
}
