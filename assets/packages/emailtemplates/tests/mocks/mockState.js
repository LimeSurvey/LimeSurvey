import templateData from './templateList.json';

export default {
    templateTypes: templateData.templateTypes,
    templateTypeContents: templateData.templateTypeContents,
    languages: templateData.languages,
    survey: {},
    debugMode: false,
    activeLanguage: 'de',
    useHtml: true,
    currentTemplateType: 'invitation',
    permissions: templateData.permissions
}
