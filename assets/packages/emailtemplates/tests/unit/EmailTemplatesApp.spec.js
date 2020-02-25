import { shallowMount, createLocalVue, config } from '@vue/test-utils'
import _ from 'lodash';
import Vue from 'vue';
import Vuex from 'vuex';

import EmailTemplatesApp from '_@/EmailTemplatesApp.vue'
import Loader from '_@/helperComponents/loader.vue';

import mockState from '../mocks/mockState.js';
import mockActions from '../mocks/mockActions.js';
import realMutations from '_@/storage/mutations.js';
import availableFilesList from '../mocks/availableFilesList.json';

config.stubs['lsckeditor'] = '<div class="test--selector--lsckeditor"/>';
config.stubs['modals-container'] = '<div class="test--selector--modals-container"/>';
config.stubs['x-test'] = true;

global.LS = {
    ld: _,
    data: {
        csrfTokenName : 'xxx',
        csrfToken : 'xxx',
    },
    EventBus: new Vue(),
    notifyFader: ()=>{}
};
const translate =  {
    methods: {
        translate(value) {
            return value;
        }
    },
    filters: {
        translate: (value) => {
            return value;
        }
    }
};

const localVue = createLocalVue();

const mockAjax = jest.fn(
    (settings) => {
        settings.success(availableFilesList, 400, []);
    }
);

localVue.use(Vuex);
localVue.component('loader-widget', Loader);
localVue.mixin(translate);

global.EmailTemplateData = {
    getFileUrl: "getFiles",
    surveyid: 0,
    isNewSurvey: false
};
global.$ = jest.fn(() => {
    return {
        on: ()=>{},
        trigger: ()=>{}
    }
});

describe("EmailTemplates basic behaviour", () => {
    let store;
    let wrapper;
    const actions = {
        getDataSet : jest.fn(()=>mockActions.getDataSet),
        saveData :  jest.fn(()=>mockActions.saveData),
    };
    let mutations = realMutations;
   
    beforeEach(() => {    
        global.LS.EventBus = new Vue();

        store = new Vuex.Store({
                    state: mockState,
                    mutations,
                    actions,
                    getters: {
                        surveyid: () => 0
                    }
                });
    
        wrapper = shallowMount(EmailTemplatesApp, {
            mocks: {
                $log: {
                    log: ()=>{}
                }
            },
            store,
            localVue
        });
    });

    test('It needs to run the getter in creation', () => {
        expect(actions.getDataSet).toHaveBeenCalled();
    });

    test('It needs to render the sidebar', () => {
        expect(wrapper.find('#emailtemplates--type-select-sidebar').exists()).toBe(true);
    });

    test('It needs to render the editor body', () => {
        expect(wrapper.find('#emailtemplates--type-select-editorbody').exists()).toBe(true);
    });

    test('It needs to render the editor window', () => {
        expect(wrapper.find('#EmailTemplates--editor-container').exists()).toBe(true);
    });
});

describe("EmailTemplates hotkeys", () => {
    let store;
    let wrapper;
    const actions = {
        getDataSet : jest.fn(mockActions.getDataSet),
        saveData :  jest.fn(mockActions.saveData),
    };
    let mutations;
   
    beforeEach(() => {
        global.LS.EventBus = new Vue();

        mutations = {};
        _.forEach(realMutations, (fn, key) => {
            mutations[key] = jest.fn(fn);
        });
    
        store = new Vuex.Store({
                    state: mockState,
                    mutations,
                    actions,
                    getters: {
                        surveyid: () => 0
                    }
                });
    
        wrapper = shallowMount(EmailTemplatesApp, {
            mocks: {
                $log: {
                    log: ()=>{},
                    error: ()=>{}
                }
            },
            store,
            localVue
        });
    });
/**
 *          Mousetrap.bind('ctrl+right', this.chooseNextLanguage);
 *          Mousetrap.bind('ctrl+left', this.choosePreviousLanguage);
 *          Mousetrap.bind('ctrl+up', this.choosePreviousTemplateType);
 *          Mousetrap.bind('ctrl+down', this.chooseNextTemplateType);
 *          Mousetrap.bind('ctrl+shift+s', this.submitCurrentState);
 *          Mousetrap.bind('ctrl+alt+d', () => {this.$store.commit('toggleDebugMode');});
 */
    test("go to next language", () => {
        wrapper.vm.chooseNextLanguage();
        expect(mutations.nextLanguage).toHaveBeenCalled();
    });

    test("go to previous language", () => {
        wrapper.vm.$store.commit('setActiveLanguage', 'en');
        wrapper.vm.choosePreviousLanguage();
        expect(mutations.previousLanguage).toHaveBeenCalled();
    });

    test("go to next language not possible, do nothing", () => {
        wrapper.vm.$store.commit('setActiveLanguage', 'en');
        wrapper.vm.chooseNextLanguage();
        expect(mutations.nextLanguage).toHaveBeenCalled()
        && expect(wrapper.vm.$store.state.activeLanguage).toBe('en');
    });

    test("go to previous language not possible, do nothing", () => {
        wrapper.vm.choosePreviousLanguage();
        expect(mutations.previousLanguage).toHaveBeenCalled()
        && expect(wrapper.vm.$store.state.activeLanguage).toBe('de');
    });

    test("go to next template type", () => {
        wrapper.vm.chooseNextTemplateType();
        expect(mutations.nextTemplateType).toHaveBeenCalled();
    });

    test("go to previous template type", () => {
        wrapper.vm.$store.commit('setCurrentTemplateType', 'reminder');
        wrapper.vm.choosePreviousTemplateType();
        expect(mutations.previousTemplateType).toHaveBeenCalled();
    });

    test("go to next template type not possible, do nothing", () => {
        wrapper.vm.$store.commit('setCurrentTemplateType', 'admin_detailed_notification');
        wrapper.vm.chooseNextTemplateType();
        expect(mutations.nextTemplateType).toHaveBeenCalled()
        && expect(wrapper.vm.$store.state.currentTemplateType).toBe('admin_detailed_notification');
    });

    test("go to previous template type not possible, do nothing", () => {
        wrapper.vm.choosePreviousTemplateType();
        expect(mutations.previousTemplateType).toHaveBeenCalled()
        && expect(wrapper.vm.$store.state.currentTemplateType).toBe('invitation');
    });

    test("should submit on sendEvent (success)", () => {
        global.LS.EventBus.$emit('componentFormSubmit');
        expect(actions.saveData).toHaveBeenCalled();
    });

    test('stripHtml strips html', () => {
        const test = "<script>console.log('This should not be executed');</script>"
        const stripped = wrapper.vm.stripScripts(test)
        expect(stripped).toMatch(`<pre>[script]
console.log('This should not be executed');
[/script]</pre>`);
    });

    test('nl2br changes nl to br', () => {
        const test = `A test
to see the change myself`;
        const stripped = wrapper.vm.nl2br(test)
        expect(stripped).toMatch(`A test<br />
to see the change myself`);
    });
    test('nl2br returning empty string for undefined', () => {
        let test;
        const stripped = wrapper.vm.nl2br(test)
        expect(stripped).toMatch(``);
    });
});

describe("UI interaction testing", ()=>{
    let store;
    let wrapper;
    const actions = {
        getDataSet : jest.fn(mockActions.getDataSet),
        saveData :  jest.fn(mockActions.saveData),
    };
    let mutations;
   
    beforeEach(() => {
        global.LS.EventBus = new Vue();

        mutations = {};
        _.forEach(realMutations, (fn, key) => {
            mutations[key] = jest.fn(fn);
        });
    
        store = new Vuex.Store({
                    state: mockState,
                    mutations,
                    actions,
                    getters: {
                        surveyid: () => 0
                    }
                });
    
        wrapper = shallowMount(EmailTemplatesApp, {
            mocks: {
                $log: {
                    log: ()=>{},
                    error: ()=>{}
                }
            },
            store,
            localVue
        });
    });

    test("Validate content button", ()=>{
        wrapper.vm.validateCurrentContent = jest.fn();
        wrapper.find("#EmailTemplates--actionbutton-validateCurrentContent").trigger('click');
        expect(wrapper.vm.validateCurrentContent).toHaveBeenCalled();
    });

    test("Reset to default button", ()=>{
        wrapper.vm.resetCurrentContent = jest.fn();
        wrapper.find("#EmailTemplates--actionbutton-resetCurrentContent").trigger('click');
        expect(wrapper.vm.resetCurrentContent).toHaveBeenCalled();
    });

    test("Add files button", ()=>{
        wrapper.vm.addFileToCurrent = jest.fn();
        wrapper.find("#EmailTemplates--actionbutton-addFileToCurrent").trigger('click');
        expect(wrapper.vm.addFileToCurrent).toHaveBeenCalled();
    });
});

describe("Failing ajax calls", ()=>{
    let store;
    let wrapper;
    const actions = {
        getDataSet : jest.fn(mockActions.getDataSet),
        saveData :  jest.fn(mockActions.saveData),
    };
    let mutations;
   
    //console.log("State ->", mockState);
    beforeEach(() => {
        global.LS.EventBus = new Vue();

        mutations = {};
        _.forEach(realMutations, (fn, key) => {
            mutations[key] = jest.fn(fn);
        });
    
        store = new Vuex.Store({
                    state: mockState,
                    mutations,
                    actions,
                    getters: {
                        surveyid: () => 0
                    }
                });
    
        wrapper = shallowMount(EmailTemplatesApp, {
            mocks: {
                $log: {
                    log: ()=>{},
                    error: ()=>{}
                }
            },
            store,
            localVue
        });
    });
    
    test('It needs to run the getter in creation (failing)', () => {
        expect(actions.getDataSet).toHaveBeenCalled() 
        && expect(wrapper.vm.$log.error).toHaveBeenCalled();
    });

    test("should submit on sendEvent (failure)", () => {
        global.LS.EventBus.$emit('componentFormSubmit');
        expect(actions.saveData).toHaveBeenCalled()
        && expect(wrapper.vm.$log.error).toHaveBeenCalled();
    });
})
