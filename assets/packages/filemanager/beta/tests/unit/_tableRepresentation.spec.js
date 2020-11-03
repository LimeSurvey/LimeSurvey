import { shallowMount, createLocalVue } from '@vue/test-utils';
import Vuex from 'vuex';
import Vue from 'vue';
import _ from 'lodash';

import TableRepComponent from '../../src/components/subcomponents/_tableRepresentation.vue';
import VueXMutations from '../../src/storage/mutations.js';
import VueXGetters from '../../src/storage/getters.js';
import MockState from '../mocks/mockState.js';
import MockActions from '../mocks/mockActions.js';

const localVue = createLocalVue();
localVue.use(Vuex);
global.LS = {
    EventBus: new Vue(),
};
global.$ = jest.fn(() => {
    return {
        on: ()=>{},
        trigger: ()=>{},
        tooltip: ()=>{},
    }
});
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

describe("correct display", () => {
    const actions = _.merge({}, MockActions);
    const state = _.merge({}, MockState);
    const store = new Vuex.Store({
        state,
        mutations: VueXMutations,
        actions,
        getters: VueXGetters
    });

    const tableRepMount = shallowMount(TableRepComponent, {
        propsData: { 
            loading: false,
            currentPage: 0
        },
        mocks: {
            $dialog: {
                confirm: jest.fn((txt) => {
                return Promise.resolve();
            })},
            $log: {log: jest.fn()}
        },
        store,
        localVue
    }); 

    test("Has the correct table head", () => {
        expect(tableRepMount.find('.head-row').html()).toContain('<div class="ls-flex ls-flex-column col-4 cell">File name</div>') 
        && expect(tableRepMount.find('.head-row').html()).toContain('<div class="ls-flex ls-flex-column col-1 cell">Type</div> ') 
        && expect(tableRepMount.find('.head-row').html()).toContain('<div class="ls-flex ls-flex-column col-2 cell">Size</div> ') 
        && expect(tableRepMount.find('.head-row').html()).toContain('<div class="ls-flex ls-flex-column col-3 cell">Mod time</div> ') 
        && expect(tableRepMount.find('.head-row').html()).toContain('<div class="ls-flex ls-flex-row col-2 cell">Action</div>')
    });

    test.each(_.toArray(MockState.fileList))(
        "Has an image block for every file rendered",
        (file) => {
            const tableRowContainer = tableRepMount.find('#file-row-' + file.hash);
            expect(tableRowContainer.find('.FileManager--file-action-cancelTransit').exists()).toBe(false);
        }
    );

    test.each(_.toArray(MockState.fileList))(
        "Has an image block for every file rendered", 
        (file) => {
            const tableRowContainer = tableRepMount.find('#file-row-'+file.hash)
        expect(tableRowContainer.find('.FileManager--file-action-delete').exists()).toBe(true);
    });

    test.each(_.toArray(MockState.fileList))(
        "Has an image block for every file rendered", 
        (file) => {
            const tableRowContainer = tableRepMount.find('#file-row-'+file.hash)
        expect(tableRowContainer.find('.FileManager--file-action-startTransit-copy').exists()).toBe(true);
    });

    test.each(_.toArray(MockState.fileList))(
        "Has an image block for every file rendered", 
        (file) => {
            const tableRowContainer = tableRepMount.find('#file-row-'+file.hash)
        expect(tableRowContainer.find('.FileManager--file-action-startTransit-move').exists()).toBe(true);
    });
    
    test("has a working byte-filter", () => {
        let shouldBeKB = TableRepComponent.filters.bytes(1025);
        let shouldBeMB = TableRepComponent.filters.bytes(1048577);
        expect(shouldBeKB).toBe('1 KB')
        && expect(shouldBeMB).toBe('1 MB');
    });

});

describe("File transit actions", () => {
    
    const actions = _.merge({},MockActions);
    const fileInTransit = MockState.fileList['firstPicture.jpg'];
    
    let tableRepMount;
    beforeEach(() => {
        const state = _.merge({},MockState);
        
        const store = new Vuex.Store({
            state,
            mutations: VueXMutations,
            actions,
            getters: VueXGetters
        });

        tableRepMount = shallowMount(TableRepComponent, {
            propsData: { 
                loading: false,
                currentPage: 0
            },
            mocks: {
                $dialog: {
                    confirm: jest.fn((txt) => {
                    return Promise.resolve();
                })},
                $log: {log: jest.fn()}
            },
            store,
            localVue
        }); 

    }); 

    test("Should start transit after clicking on 'copy'", () => {
        const fileRowWrapper = tableRepMount.find("#file-row-" + fileInTransit.hash);
        const copyButton = fileRowWrapper.find('.FileManager--file-action-startTransit-copy');
        copyButton.trigger('click');
        expect(tableRepMount.vm.$store.state.fileList['firstPicture.jpg'].inTransit).toBe(true);  
    });

    test("Should start transit after clicking on 'move'", () => {
        const fileRowWrapper = tableRepMount.find("#file-row-" + fileInTransit.hash);
        const copyButton = fileRowWrapper.find('.FileManager--file-action-startTransit-move');
        copyButton.trigger('click');
        expect(tableRepMount.vm.$store.state.fileList['firstPicture.jpg'].inTransit).toBe(true);  
    });
});

describe("File in transit actions", () => {
    
    const actions = _.merge({},MockActions);
    const fileInTransit = MockState.fileList['firstPicture.jpg'];
    const fileNotTransit = MockState.fileList['secondPicture.jpg'];
    const state = _.merge({},MockState);
    
    let tableRepMount;
    beforeEach(() => {
        
        state.fileList['firstPicture.jpg'].inTransit = true;
        state.transitType = "move";

        const store = new Vuex.Store({
            state,
            mutations: VueXMutations,
            actions,
            getters: VueXGetters
        });

        tableRepMount = shallowMount(TableRepComponent, {
            propsData: { 
                loading: false,
                currentPage: 0
            },
            mocks: {
                $dialog: {
                    confirm: jest.fn((txt) => {
                    return Promise.resolve();
                })},
                $log: {log: jest.fn()}
            },
            store,
            localVue
        }); 
    }); 

    test("Should show cancel transit button when a transit starts", () => {
        const fileRowWrapper = tableRepMount.find("#file-row-" + fileInTransit.hash);
        expect(fileRowWrapper.find('.FileManager--file-action-cancelTransit').exists()).toBe(true);
    });
    test("Should cancel the transit after clickong 'cancelTransit'", () => {
        const fileRowWrapper = tableRepMount.find("#file-row-" + fileInTransit.hash);
        fileRowWrapper.find('.FileManager--file-action-cancelTransit').trigger('click');
        expect(tableRepMount.vm.inTransit(fileInTransit)).toBe(false);
    })

    test("Should mark in transit file as inTransit", () => {
        expect(tableRepMount.vm.inTransit(tableRepMount.vm.files['firstPicture.jpg'])).toBe(true);
    })
    test("Should mark notInTransitFile as notInTransit", () => {
        expect(tableRepMount.vm.inTransit(tableRepMount.vm.files['secondPicture.jpg'])).toBe(false);
    })

    test("Should have the correct classes for a file in transit", () => {
        const fileRowClasses = tableRepMount.vm.fileClass(tableRepMount.vm.files['firstPicture.jpg']);
        expect(fileRowClasses).toBe("scoped-file-icon file-in-transit move ")
    })
    test("Should have the correct classes for a file not in transit", () => {
        const fileRowClasses = tableRepMount.vm.fileClass(tableRepMount.vm.files['secondPicture.jpg']);
        expect(fileRowClasses).toBe("scoped-file-icon ")
    })
});

describe('Delete file success', () => {
    const fileToBeDeleted = MockState.fileList['firstPicture.jpg'];
    const state = _.merge({},MockState);
    const callDialog = jest.fn((txt) => Promise.resolve(txt));

    let actions;
    let tableRepMount;
    beforeAll(() => {

        actions = _.merge({},MockActions);
        actions.deleteFile = jest.fn();

        const store = new Vuex.Store({
            state,
            mutations: VueXMutations,
            actions,
            getters: VueXGetters
        });

        tableRepMount = shallowMount(TableRepComponent, {
            propsData: { 
                loading: false,
                currentPage: 0
            },
            mocks: {
                $dialog: {
                    confirm: callDialog
                },
                $log: {log: ()=>{}, error: ()=>{}}
            },
            store,
            localVue
        }); 
    }); 

    test("Should call a dialog on click on delete file", () => {
        const fileRowWrapper = tableRepMount.find("#file-row-" + fileToBeDeleted.hash);
        fileRowWrapper.find('.FileManager--file-action-delete').trigger('click');
        expect(callDialog).toHaveBeenCalled();
    });
    test("Should have called the delete action after clicking delete", () => {
        expect(actions.deleteFile).toHaveBeenCalled()
    }); 
});

describe('Delete file failure', () => {
    const fileToBeDeleted = MockState.fileList['firstPicture.jpg'];
    const state = _.merge({},MockState);
    const callDialog = jest.fn((txt) => Promise.reject());
    let actions;
    let tableRepMount;

    beforeEach(() => {
        actions = _.merge({},MockActions);
        actions.deleteFile = jest.fn(() => Promise.resolve());

        const store = new Vuex.Store({
            state,
            mutations: VueXMutations,
            actions,
            getters: VueXGetters
        });

        tableRepMount = shallowMount(TableRepComponent, {
            propsData: { 
                loading: false,
                currentPage: 0
            },
            mocks: {
                $dialog: {
                    confirm: callDialog
                },
                $log: {log: ()=>{}, error: ()=>{}}
            },
            store,
            localVue
        }); 
    }); 

    test("Should not call the delete action after clicking delete", () => {
        const fileRowWrapper = tableRepMount.find("#file-row-" + fileToBeDeleted.hash);
        fileRowWrapper.find('.FileManager--file-action-delete').trigger('click');
        expect(actions.deleteFile).not.toHaveBeenCalled()
    });
    
    describe('Pagination', () => {
        const state = _.merge({},MockState);
        const callDialog = jest.fn((txt) => Promise.reject());
        let actions;
        let tableRepMount;

        beforeEach(() => {
            actions = _.merge({},MockActions);
            actions.deleteFile = jest.fn(() => Promise.resolve());

            const store = new Vuex.Store({
                state,
                mutations: VueXMutations,
                actions,
                getters: VueXGetters
            });

            tableRepMount = shallowMount(TableRepComponent, {
                propsData: { 
                    loading: false,
                    currentPage: 0
                },
                mocks: {
                    $dialog: {
                        confirm: callDialog
                    },
                    $log: {log: ()=>{}, error: ()=>{}}
                },
                store,
                localVue
            });
        });

        // TODO: WIP: Mock file as new filelist.json
        // TODO: WIP: Test abstract representation.vue!
        it.skip('should contain pagination', () => {
            let files = [];
            for (let index = 0; index > 50; i++) {
                files.push('test_'+index+'.txt');
            }
            if (tableRepMount !== null) {
                tableRepMount.computed.files = files;
                expect(tableRepMount.computed.files.length).toBe(50);
                let pagination = tableRepMount.find('#ls-ba pager');
                expect(pagination).toBeDefined;
            } else {
                console.log('TableRepMount is null!');
            }
        });

        it.skip('should contains 2 pages', () => {
            // TODO: Set up dependencies for current test.
        });

        it.skip('should select page 2', () => {
            // TODO: Set up dependencies for current test.
        });
    });
})
