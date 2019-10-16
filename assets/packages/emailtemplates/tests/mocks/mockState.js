import templateData from './templateList.json';

export default {
    templateTypes: templateData.templateTypes,
    templateTypeContents: templateData.templateTypeContents,
    languages: templateData.languages,
    survey: {},
    debugMode: false,
    activeLanguage: 'en',
    useHtml: true,
    currentTemplateType: 'invite',
    permissions: templateData.permissions
}
