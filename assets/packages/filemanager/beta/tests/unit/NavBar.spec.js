import { shallowMount, createLocalVue } from '@vue/test-utils';
import Vue from 'vue';
import Vuex from 'vuex';

import NavBarComponent from '../../src/components/NavBar.vue';
import VueXMutations from '../../src/storage/mutations.js';
import MockState from '../mocks/mockState.js';
import MockActions from '../mocks/mockActions.js';
import MockModal from '../mocks/mockActions.js';

const localVue = createLocalVue();
global.LS = {
    EventBus: new Vue(),
};
global.$ = jest.fn(() => {
    return {
        on: ()=>{},
        trigger: ()=>{}
    }
});
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

describe("NavBar basic behaviour", () => {
    const  actions = MockActions;
    const store = new Vuex.Store({
        state: Object.assign({}, MockState),
        mutations: VueXMutations,
        actions
    });
    const mockOpenModal = jest.fn((component, o1,o2,oEvent) => {});
    const navBarMount = shallowMount(NavBarComponent, {
        store,
        localVue,
        mocks: {
            $modal: {
                show: mockOpenModal
            }
        }
    });

    test('should not have a file in tranist', () => {
        expect(navBarMount.vm.fileInTransit).toBe(false);
    });

    test('should contain current folder name', () => {
        expect(navBarMount.html()).toContain('<span class="navbar-brand">' + MockState.currentFolder  +'</span>');
    });

    test('should contain upload buttons', () => {
        const hasUploadButton = navBarMount.find('#FileManager--button-upload').exists();
        expect(hasUploadButton).toBe(true);
    });
    
    test('should contain download buttons', () => {
        const hasUploadButton = navBarMount.find('#FileManager--button-download').exists();
        expect(hasUploadButton).toBe(true);
    });

    test('should NOT contain transit cancel button', () => {
        const transitCancelButton = navBarMount.find('#FileManager--button-fileInTransit--cancel');
        expect(transitCancelButton.exists()).toBeFalsy();
    });

    test('should contain transit submit button', () => {
        const transitSubmitButton = navBarMount.find('#FileManager--button-fileInTransit--submit');
        expect(transitSubmitButton.exists()).toBeFalsy();
    });

    test('should trigger the upload modal on click', () => {
        const uploadButton = navBarMount.find('#FileManager--button-upload');
        uploadButton.trigger('click')
        expect(mockOpenModal).toHaveBeenCalled();
    });
});

describe("NavBar on file in transit action - moving", () => {
    const  actions = MockActions;
    const state = Object.assign({}, MockState)

    state.fileInTransit = { "isImage": true  };
    state.transitType = 'move';

    const store = new Vuex.Store({
        state: state,
        mutations: VueXMutations,
        actions
    });

    const navBarMount = shallowMount(NavBarComponent, {
        store,
        localVue
    });

    test('should have a file in tranist', () => {
        expect(navBarMount.vm.fileInTransit).toBe(true);
    });

    test('should contain transit submit button', () => {
        const transitSubmitButton = navBarMount.find('#FileManager--button-fileInTransit--submit');
        expect(transitSubmitButton.html()).toContain('<a href="#">Move</a>');
    });

    test('should contain transit cancel button', () => {
        const transitCancelButton = navBarMount.find('#FileManager--button-fileInTransit--cancel');
        expect(transitCancelButton.html()).toContain('<a href="#">Cancel Move</a>');
    });

    test('should apply transit on click', () => {
        const transitSubmitButton = navBarMount.find('#FileManager--button-fileInTransit--submit>a');
        transitSubmitButton.trigger('click');
        expect(actions.applyTransition).toHaveBeenCalled()
        && expect(navBarMount.vm.fileInTransit).toBe(false);
    });
    
});

describe("NavBar on file in transit action - copying", () => {
    const  actions = MockActions;
    const state = Object.assign({}, MockState)

    state.fileInTransit = { "isImage": true };
    state.transitType = 'copy';

    const store = new Vuex.Store({
        state: state,
        mutations: VueXMutations,
        actions
    });

    const navBarMount = shallowMount(NavBarComponent, {
        store,
        localVue
    });

    test('should have a file in tranist', () => {
        expect(navBarMount.vm.fileInTransit).toBe(true);
    });

    test('should contain transit submit button', () => {
        const transitSubmitButton = navBarMount.find('#FileManager--button-fileInTransit--submit');
        expect(transitSubmitButton.html()).toContain('<a href="#">Copy</a>');
    });

    test('should contain transit cancel button', () => {
        const transitCancelButton = navBarMount.find('#FileManager--button-fileInTransit--cancel');
        expect(transitCancelButton.html()).toContain('<a href="#">Cancel Copy</a>');
    });

    test('should apply transit on click', () => {
        const transitSubmitButton = navBarMount.find('#FileManager--button-fileInTransit--submit>a');
        transitSubmitButton.trigger('click');

        expect(actions.applyTransition).toHaveBeenCalled()
        && expect(navBarMount.vm.fileInTransit).toBe(true);
    });
    
});

describe("NavBar in transit cancel", () => {
    const  actions = MockActions;
    const state = Object.assign({}, MockState)

    state.fileInTransit = { "isImage": true };
    state.transitType = 'move';

    const store = new Vuex.Store({
        state: state,
        mutations: VueXMutations,
        actions
    });

    const navBarMount = shallowMount(NavBarComponent, {
        store,
        localVue
    });

    test('should have a file in tranist', () => {
        expect(navBarMount.vm.fileInTransit).toBe(true);
    });

    test("should mutate fileInTransit on cancel", () => {
        navBarMount.vm.cancelTransit();
        expect(navBarMount.vm.fileInTransit).toBe(false);
    });
});
