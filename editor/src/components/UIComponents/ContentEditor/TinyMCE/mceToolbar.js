import { boldIcon, italicIcon, verticalThreeDots } from './tinyMCEIcons'

export const mceToolbar = (editor) => {
  editor.ui.registry.addIcon('verticalThreeDots', verticalThreeDots)
  editor.ui.registry.addIcon('boldIcon', boldIcon)
  editor.ui.registry.addIcon('italicIcon', italicIcon)

  editor.ui.registry.addToggleButton('customItalic', {
    icon: 'italicIcon',
    onAction: (api) => {
      editor.execCommand('mceToggleFormat', !api.isActive(), 'italic')
    },
    onSetup: (api) => {
      api.setActive(editor.formatter.match('italic'))
      const changed = editor.formatter.formatChanged('italic', (state) =>
        api.setActive(state)
      )
      return () => changed.unbind()
    },
  })

  editor.ui.registry.addToggleButton('customBold', {
    icon: 'boldIcon',
    onAction: (api) => {
      editor.execCommand('mceToggleFormat', api.isActive(), 'bold')
    },
    onSetup: (api) => {
      api.setActive(editor.formatter.match('bold'))
      const changed = editor.formatter.formatChanged('bold', (state) =>
        api.setActive(state)
      )
      return () => changed.unbind()
    },
  })

  editor.ui.registry.addMenuButton('alignmentMenu', {
    icon: 'align-left',
    type: 'menubutton',
    fetch: (callback) => {
      callback([
        {
          type: 'menuitem',
          text: t('Align text left'),
          icon: 'align-left',
          onAction: () => {
            const selectedNode = editor.selection.getNode()
            if (selectedNode) {
              selectedNode.classList.remove('text-start')
              selectedNode.classList.remove('text-end')
              selectedNode.classList.remove('text-center')
              selectedNode.classList.add('text-start')
            }
            editor.focus()
          },
        },
        {
          type: 'menuitem',
          text: t('Align text center'),
          icon: 'align-center',
          onAction: () => {
            const selectedNode = editor.selection.getNode()
            if (selectedNode) {
              selectedNode.classList.remove('text-start')
              selectedNode.classList.remove('text-end')
              selectedNode.classList.remove('text-center')
              selectedNode.classList.add('text-center')
            }
            editor.focus()
          },
        },
        {
          type: 'menuitem',
          text: t('Align text right'),
          icon: 'align-right',
          onAction: () => {
            const selectedNode = editor.selection.getNode()
            if (selectedNode) {
              selectedNode.classList.remove('text-start')
              selectedNode.classList.remove('text-center')
              selectedNode.classList.remove('text-end')
              selectedNode.classList.add('text-end')
            }
            editor.focus()
          },
        },
      ])
    },
  })
}
