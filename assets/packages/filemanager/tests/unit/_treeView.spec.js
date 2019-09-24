import {
    shallowMount,
    createLocalVue
} from '@vue/test-utils';

import Vuex from 'vuex';

import TreeViewComponent from '../../src/components/subcomponents/_treeView.vue';
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

describe("basic rendering", () => {
    const  actions = MockActions;
    const state = Object.assign({}, MockState);
    const store = new Vuex.Store({
        state,
        mutations: VueXMutations,
        actions
    });

    const treeViewMount = shallowMount(TreeViewComponent, {
        stubs: {
            ModalsContainer: '<div class="stubbed" />',
            LoaderWidget: '<div id="filemanager-loader-widget" />'
        },
        propsData: { 
            folders: MockState.folderList,
            initiallyCollapsed: true,
            loading: true
        },
        mocks: {
            $log: {log: jest.fn()}
        },
        store,
        localVue
    }); 

    it("Is collapsed on startup", () => {
        expect(treeViewMount.vm.collapsed).toBe(true);
    });

    it("Has Folder classes correctly set no children", () => {
        let classesSet = treeViewMount.vm.getHtmlClasses(MockState.folderList[1]);
        expect(classesSet).toBe("ls-flex ls-flex-row scoped-tree-folder ls-space bottom-5");
    });
    it("Has Folder classes correctly set with children and selected", () => {
        let classesSet = treeViewMount.vm.getHtmlClasses(MockState.folderList[0]);
        expect(classesSet).toBe("ls-flex ls-flex-row scoped-tree-folder ls-space bottom-5 scoped-has-children text-bold scoped-selected");
    });

    it("Is rendered correctly", () => {
        expect(treeViewMount.html()).toContain('<i class="fa fa-folder-open fa-lg"></i>')
        && expect(treeViewMount.html()).toContain('<span class="scope-apply-hover">generalfiles</span>')
        && expect(treeViewMount.html()).toContain('<button class="btn btn-xs btn-default"><i class="fa fa-caret-down fa-lg"></i></button>');
    });
})

describe("emitting and changes", () => {
    const actions = MockActions;
    const state = Object.assign({}, MockState);
    let treeViewMount;
    beforeEach(() => {
        const store = new Vuex.Store({
            state,
            mutations: VueXMutations,
            actions
        });

        treeViewMount = shallowMount(TreeViewComponent, {
            stubs: {
                ModalsContainer: '<div class="stubbed" />',
                LoaderWidget: '<div id="filemanager-loader-widget" />'
            },
            propsData: { 
                folders: MockState.folderList,
                initiallyCollapsed: true,
                loading: true
            },
            mocks: {
                $log: {log: jest.fn()}
            },
            store,
            localVue
        }); 
    });

    it("Should emit a change on folder selected", () => {
        treeViewMount.vm.selectFolder(MockState.folderList[1]);
        expect(actions.folderSelected).toHaveBeenCalled();
    });

    it("Should change classes when selected folder changes", () => {
        treeViewMount.vm.$store.commit('setCurrentFolder', MockState.folderList[1].folder);
        let classesSet = treeViewMount.vm.getHtmlClasses(MockState.folderList[1]);
        expect(classesSet).toBe("ls-flex ls-flex-row scoped-tree-folder ls-space bottom-5 scoped-selected");
    });


});
