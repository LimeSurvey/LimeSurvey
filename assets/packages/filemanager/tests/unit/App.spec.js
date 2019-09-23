//Import vue and the Component
import {
    shallowMount,
    createLocalVue
} from '@vue/test-utils';

import Vuex from 'vuex';

import FileManagerApp from '../../src/App.vue';

import VueXState from '../../src/storage/state.js';
import VueXMutations from '../../src/storage/mutations.js';

import mockFolderList from '../mocks/folderList.json';
import mockFileList from '../mocks/fileList.json';
import mockFileList2 from '../mocks/fileList2.json';

const localVue = createLocalVue();
localVue.use(Vuex);

// Check the ajax calls
describe("FileManagerApp basics", () => {
    it('has a mounted hook', () => {
        expect(typeof FileManagerApp.mounted).toBe('function');
    });

    // Evaluate loading to be set to true on start
    it('is loading on beforemount', () => {
        expect(typeof FileManagerApp.data).toBe('function')
        const defaultData = FileManagerApp.data()
        expect(defaultData.loading).toBe(true);
    });
});

describe("FileManagerApp fulfilled promises on startup", () => {
    let actions;
    let store;

    beforeAll(() => {
        actions = {
            getFolderList: jest.fn((ctx) => {
                return new Promise((resolve, reject) => {
                    ctx.commit('setFolderList', mockFolderList);
                    resolve(mockFolderList);
                });
            }),
            getFileList: jest.fn((ctx) => {
                return new Promise((resolve, reject) => {
                    ctx.commit('setFileList', (ctx.state.currentFolder == 'generalfiles' ? mockFileList : mockFileList2));
                    resolve((ctx.state.currentFolder == 'generalfiles' ? mockFileList : mockFileList2));
                });
            }),
            folderSelected: jest.fn(),
            deleteFile: jest.fn(),
            applyTransition: jest.fn(),
        };

        store = new Vuex.Store({
            state: VueXState,
            mutations: VueXMutations,
            actions
        });
    });

    console.log(store);


    it('dispatches getFolderList action', () => {
        const fileManagerWrapper = shallowMount(FileManagerApp, {
            store,
            localVue
        });
        expect(actions.getFolderList).toHaveBeenCalled();
    });

    it('dispatches getFileList action', () => {
        const fileManagerWrapper = shallowMount(FileManagerApp, {
            store,
            localVue
        });
        expect(actions.getFolderList).toHaveBeenCalled();
    });

    // Evaluate loading to be set to false after mount
    it('stopped the loading animation after mount', () => {
        const fileManagerWrapper = shallowMount(FileManagerApp, {
            store,
            localVue
        });
        expect(fileManagerWrapper.vm.loading).toBe(false);
    }, 1500);

    it('renders correct html', () => {
        const fileManagerWrapper = shallowMount(FileManagerApp, {
            store,
            localVue
        });
        expect(fileManagerWrapper.html()).toContain('<div id="filemanager-app" class="row">');
    });

});
