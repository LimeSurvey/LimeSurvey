import {
    shallowMount,
    createLocalVue
} from '@vue/test-utils'
import _ from 'lodash';
import Vuex from 'vuex';
import FileSelectModal from '_@/components/FileSelectModal.vue'
import Loader from '_@/helperComponents/loader.vue';

import mockState from '../mocks/mockState.js';
import mutations from '_@/storage/mutations.js';
import availableFilesList from '../mocks/availableFilesList.json';

global.LS = {
    ld: _,
    data: {
        csrfTokenName : 'xxx',
        csrfToken : 'xxx',
    }
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
    surveyid: 0
};
global.$ = {
    ajax: mockAjax
};

describe('FileSelectModal.vue', () => {
    let store;
    let wrapper;
    let testableSetAttachment;
    
    
    beforeEach(() => {
        testableSetAttachment = jest.fn(mutations.setAttachementForTypeAndLanguage);
        const mockedMutations = _.extend(mutations, {
            setAttachementForTypeAndLanguage: testableSetAttachment
        });
    
        store = new Vuex.Store({
                    state: mockState,
                    mutations: mockedMutations,
                    actions: {}
                });
        
    
        wrapper = shallowMount(FileSelectModal, {
            mocks: {
                $log: {
                    log: ()=>{}
                }
            },
            store,
            localVue
        });
    })

    test('calls ajax method for a file list on mount ', () => {
        expect(mockAjax).toHaveBeenCalled();
    });

    test('has a file list on mount', () => {
        expect(wrapper.vm.availableFilesList[0].hash).toMatch("62a62fcf2b28ae7009feee5ae6c4f816")
    });

    test('show a selected file correctly', () => {
        const fileTiles = wrapper.findAll('.scoped-file-tile');
        fileTiles.at(0).find('.emailtemplates--imagecontainer').trigger('click');
        expect(wrapper.findAll('.scope-selected-file').length).toBe(1)
        && expect(wrapper.vm.selectedFiles[0].hash).toMatch("62a62fcf2b28ae7009feee5ae6c4f816")
    });

    test('hide selected file correctly', () => {
        wrapper.vm.selectedFiles = availableFilesList;
        const fileTiles = wrapper.findAll('.scope-selected-file');
        fileTiles.wrappers.forEach((wrapContainer) => { wrapContainer.find('.emailtemplates--imagecontainer').trigger('click'); });
        expect(wrapper.findAll('.scope-selected-file').length).toBe(0)
    });
    
    test('Shows no files warning when no files are available', () => {
        wrapper.vm.availableFilesList = {};
        expect(wrapper.html()).toContain('<p class="well"> No files in the survey folder</p>');
    });

    test('save selected file correctly', () => {
        wrapper.vm.selectedFiles = availableFilesList;
        const saveAttachmentButton = wrapper.find('#emailtemplates--save-attachements');
        saveAttachmentButton.trigger('click');
        expect(testableSetAttachment).toHaveBeenCalled();
    });
})
