//Import vue and the Component
import {
    shallowMount,
    createLocalVue,
    config
} from '@vue/test-utils';
import _ from 'lodash';
import Vuex from 'vuex';
import FlushPromises from 'flush-promises';

import FileManagerApp from '../../src/App.vue';

import VueXState from '../../src/storage/state.js';
import VueXMutations from '../../src/storage/mutations.js';
import MockActions from '../mocks/mockActions.js';
import FailingMockActions from '../mocks/failingMockActions.js';

config.stubs['x-test'] = true;

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
    test('has a mounted hook', () => {
        expect(typeof FileManagerApp.mounted).toBe('function');
    });

    // Evaluate loading to be set to true on start
    test('is loading on beforemount', () => {
        expect(typeof FileManagerApp.data).toBe('function')
        const defaultData = FileManagerApp.data()
        expect(defaultData.loading).toBe(true);
    });
});

describe("FileManagerApp fulfilled promises on startup", () => {
    let store;
    let fileManagerWrapper;
    const  actions = MockActions;

    beforeAll(() => {
        store = new Vuex.Store({
            state: VueXState,
            mutations: VueXMutations,
            actions
        });
    });

    beforeEach(() => {
        fileManagerWrapper = shallowMount(FileManagerApp, {
            store,
            localVue,
            mocks: {
                $log: {log: jest.fn(), error: jest.fn()}
            },
        });
    })

    test('dispatches getFolderList action', () => {
        expect(actions.getFolderList).toHaveBeenCalled();
    });

    test('dispatches getFileList action', () => {
        expect(actions.getFolderList).toHaveBeenCalled();
    });

    test('renders correct html', () => {
        expect(fileManagerWrapper.html()).toContain('<div id="filemanager-app" class="row">');
    });

    // Evaluate loading to be set to false after mount
    test('stopped the loading animation after mount', async () => {
        await FlushPromises();
        expect(fileManagerWrapper.vm.loading).toBe(false);
    }, 1500);

});

describe("FileManagerApp failing promises on startup", () => {
    let store;
    let fileManagerWrapper;
    const actions = FailingMockActions;
    const notifyFader = jest.fn();
    
    global.LS = {
        notifyFader: notifyFader
    };

    beforeAll(() => {

        store = new Vuex.Store({
            state: VueXState,
            mutations: VueXMutations,
            actions
        });
    });

    beforeEach(() => {
        fileManagerWrapper = shallowMount(FileManagerApp, {
            store,
            localVue,
            mocks: {
                $log: {log: jest.fn(), error: jest.fn()}
            },
        });
    })
    
    test('dispatches getFolderList action', () => {
        expect(actions.getFolderList).toHaveBeenCalled()
        && expect(fileManagerWrapper.vm.hasError).toBe(true);
    });

    test('dispatches getFileList action', () => {
        expect(actions.getFolderList).toHaveBeenCalled()
        && expect(fileManagerWrapper.vm.hasError).toBe(true);
    });
    
    test('dispatches error notification to be shown', () => {
        expect(notifyFader).toHaveBeenCalled();
    });

    // Evaluate loading to be set to false after mount
    test('stopped the loading animation after mount', async () => {
        await FlushPromises();
        expect(fileManagerWrapper.vm.loading).toBe(false)
        && expect(fileManagerWrapper.vm.hasError).toBe(true);
    }, 1500);
})

describe("FileManagerApp ", () => {
    const actions = MockActions;
    let store;

    beforeEach(() => {
        const state = _.clone(VueXState);
        state.currentFolder = null;
        store = new Vuex.Store({
            state,
            mutations: VueXMutations,
            actions
        });
    });

    test("Selected folder should be the first in the call after markup", async () => {
        const fileManagerWrapper = shallowMount(FileManagerApp, {
            store,
            localVue,
            mocks: {
                $log: {log: jest.fn(), error: jest.fn()}
            },
        });
        await FlushPromises();
        expect(fileManagerWrapper.vm.$store.state.currentFolder).toBe(VueXState.currentFolder);
    })

    test("Selected folder should be the set one by prop", async () => {
        const fileManagerWrapper = shallowMount(FileManagerApp, {
            store,
            localVue,
            mocks: {
                $log: {log: jest.fn(), error: jest.fn()}
            },
            propsData: {
                presetFolder: "upload/global"
            }
        });
        await FlushPromises();
        expect(fileManagerWrapper.vm.$store.state.currentFolder).toBe("upload/global");
    })
    
})
