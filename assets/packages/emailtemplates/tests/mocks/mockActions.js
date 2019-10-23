
import TemplateList from './templateList.json';

export default {
    getDataSet: (context) => {
        return new Promise((resolve) => {
            context.commit('setUseHtml', TemplateList.useHtml);
            context.commit('setTemplateTypes', TemplateList.templateTypes);
            context.commit('setCurrentTemplateType', _.keys(TemplateList.templateTypes)[0]);
            context.commit('setLanguages', TemplateList.languages);
            context.commit('setActiveLanguage', _.keys(TemplateList.languages)[0]);
            context.commit('setTemplateTypeContents', TemplateList.templateTypeContents);
            context.commit('setPermissions', TemplateList.permissions);
            resolve();
        });
    },
    saveData: (context) => Promise.resolve({
        data: {
            message: '',
            redirect: ''
        }
    })
};
