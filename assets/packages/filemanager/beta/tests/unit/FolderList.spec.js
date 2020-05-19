import {
    shallowMount,
    createLocalVue
} from '@vue/test-utils';

import Vuex from 'vuex';

import FolderListComponent from '../../src/components/FolderList.vue';
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

describe("it should switch loading", () => {
    let folderListMount;
    beforeEach(() => {
        const  actions = MockActions;
        const store = new Vuex.Store({
            state: Object.assign({}, MockState),
            mutations: VueXMutations,
            actions
        });
    
        folderListMount = shallowMount(FolderListComponent, {
            
            propsData: { 
                cols: 4,
                loading: false
            },
            mocks: {
                $log: {log: jest.fn()}
            },
            store,
            localVue
        }); 
    });

    test("Should have rendered with a with of 4 columns", () => {
        expect(folderListMount.html()).toContain('<div class="scoped-folder-list col-md-4">');
    });
    
    test("Should emit to change the loading attribute to true", () => {
        folderListMount.vm.setLoading(true)
        //This is somehow how this method workd it always wraps stuff in arrays
        expect(folderListMount.emitted().setLoading[0][0]).toBe(true);
    });

    test("Should emit to change the loading attribute to false", () => {
        folderListMount.vm.setLoading(false)
        //This is somehow how this method workd it always wraps stuff in arrays
        expect(folderListMount.emitted().setLoading[0][0]).toBe(false);
    });

    it('should contain search bar', () => {
        let searchBar = folderListMount.find('#folder-search-bar');
        expect(searchBar).toBeDefined;
    });

    it('should contain pagination', () => {
        let pagination = folderListMount.find('#ls-ba pager');
        expect(pagination).toBeDefined;
    });
})

