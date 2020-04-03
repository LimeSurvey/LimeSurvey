import {shallowMount, createLocalVue} from '@vue/test-utils';
import Vue from 'vue';
import Vuex from 'vuex';
import _ from 'lodash';
import AbstractRepresentation from '../../src/helperComponents/abstractRepresentation.vue';
import VueXMutations from '../../src/storage/mutations.js';
import VueXGetters from '../../src/storage/getters.js';
import MockState from '../mocks/mockState.js';
import MockActions from '../mocks/mockActions.js';

const localVue = createLocalVue();
localVue.use(Vuex);
global.LS = {
	EventBus: new Vue(),
};
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

describe('AbstractRepresentation Tests', () => {
    const actions = _.merge({}, MockActions);
    const state   = _.merge({}, MockState);
    const store   = new Vuex.Store({
        state,
        mutations: VueXMutations,
        actions,
        getters: VueXGetters
    });

    const abstractRepresentation = shallowMount(
        AbstractRepresentation,
        {
            store,
            localVue
        }
    );
    
    describe('Files', () => {

        beforeEach(() => {
            const files = MockState.fileList;

        });
        it('is empty', () => {
            abstractRepresentation.attributes().files = [];
            expect(abstractRepresentation.attributes().files).toBeNotDefined;
        });

        it('is not empty', () => {
            expect(abstractRepresentation.attributes().files).toBeDefined;
        });
        
        it.skip('contains 2 files', () => {
            expect(abstractRepresentation.attributes().files.length).toBe(2);
        });

        it.skip('should select all files as marked', () => {
            abstractRepresentation.attributes().files = [''];
        });

        it.skip('should delete file', () => {
            // TODO: Set up dependencies for current test.
        });

        it.skip('should copy file', () => {
            // TODO: Set up dependencies for current test.
        });

        it.skip('should move file', () => {
            // TODO: Set up dependencies for current test.
        });

        it.skip('should cancel transit', () => {
            // TODO: Set up dependencies for current test.
        });
    });
});
