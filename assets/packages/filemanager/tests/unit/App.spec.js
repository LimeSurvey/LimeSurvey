//Import vue and the Component
import {
    shallowMount,
    createLocalVue
} from '@vue/test-utils';

import Vuex from 'vuex';
import FlushPromises from 'flush-promises';

import FileManagerApp from '../../src/App.vue';

import VueXState from '../../src/storage/state.js';
import VueXMutations from '../../src/storage/mutations.js';
import MockActions from '../mocks/mockActions.js';
import FailingMockActions from '../mocks/failingMockActions.js';

const localVue = createLocalVue();
localVue.use(Vuex);

localVue.mixin({
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
});

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
    let store;
    const  actions = MockActions;

    beforeAll(() => {
        store = new Vuex.Store({
            state: VueXState,
            mutations: VueXMutations,
            actions
        });
    });

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

    it('renders correct html', () => {
        const fileManagerWrapper = shallowMount(FileManagerApp, {
            store,
            localVue
        });
        expect(fileManagerWrapper.html()).toContain('<div id="filemanager-app" class="row">');
    });

    // Evaluate loading to be set to false after mount
    it('stopped the loading animation after mount', async () => {
        const fileManagerWrapper = shallowMount(FileManagerApp, {
            store,
            localVue
        });
        await FlushPromises();
        expect(fileManagerWrapper.vm.loading).toBe(false);
    }, 1500);

});

describe("FileManagerApp failing promises on startup", () => {
    let store;
    const  actions = FailingMockActions;

    beforeAll(() => {
        store = new Vuex.Store({
            state: VueXState,
            mutations: VueXMutations,
            actions
        });
    });
})
