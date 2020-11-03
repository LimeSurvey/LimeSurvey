import {
    shallowMount,
    createLocalVue
} from '@vue/test-utils';

import Vuex from 'vuex';

import FileListComponent from '../../src/components/FileList.vue';
import VueXMutations from '../../src/storage/mutations.js';
import MockState from '../mocks/mockState.js';
import MockActions from '../mocks/mockActions.js';

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

describe("file representation changes", () => {
    let fileListMount;
    let state = Object.assign({}, MockState);
    beforeEach(() => {
        const  actions = MockActions;
        const store = new Vuex.Store({
            state,
            mutations: VueXMutations,
            actions
        });
    
        fileListMount = shallowMount(FileListComponent, {
            stubs: {
                ModalsContainer: '<div class="stubbed" />',
                LoaderWidget: '<div id="filemanager-loader-widget" />'
            },
            propsData: { 
                cols: 8,
                loading: false
            },
            mocks: {
                $log: {log: jest.fn()}
            },
            store,
            localVue
        }); 
    }); 

    test("Should have file visualization set to table by default", () => {
        expect(fileListMount.vm.fileviz).toBe('tablerep');
    })

    test("Should change file visualisation to icons", () => {
       fileListMount.find('#FileManager--change-filewiz-to-iconrep').trigger('click');
       expect(fileListMount.vm.fileviz).toBe('iconrep');
    })

    test("Should change file visualisation to table", () => {
       fileListMount.find('#FileManager--change-filewiz-to-iconrep').trigger('click');
       fileListMount.find('#FileManager--change-filewiz-to-tablerep').trigger('click');
       expect(fileListMount.vm.fileviz).toBe('tablerep');
    })

    it('should contain search bar', () => {
        let searchBar = fileListMount.find('#file-search-bar');
        expect(searchBar).toBeDefined;
    });
});
