import  { shallowMount, createLocalVue }  from '@vue/test-utils';
import Vuex from 'vuex';
import SideBar from '../src/components/sidebar.vue';
import QuestionExplorer from '../src/components/subcomponents/_questionsgroups.vue';

const localVue = createLocalVue();
localVue.use(Vuex);

const actions = {
    updatePjaxLinks: jest.fn(),
};
const store = new Vuex.Store({
    state: {
        'lastQuestionGroupOpen': 1,
        'questiongroups': [
            'group_order',
        ],
    },
    actions,
});

localVue.use(store);

describe('Admin Sidemenu Funtionalities', () => {

    test('does it exists', () => {
        const wrapper = shallowMount(SideBar, {
            localVue,
            store,
            propsData: {
                landOnTab: 'Settings',
                isSideMenuElementActive: false,
                activeSideMenuElement: ''
            },
            data() {
                return {
                    activeMenuElement: 0,
                    openSubpanelId: 0,
                    menues: [],
                    collapsed: false,
                    sideBarWidth: "315",
                    sideBarHeight: "400px",
                    initialPos: {
                        x: 0,
                        y: 0
                    },
                    isMouseDown: false,
                    isMouseDownTimeOut: null,
                    showLoader: false,
                    loading: true,
                    hiddenStateToggleDisplay: 'flex',
                    smallScreenHidden: false,
                }
            }
        });      
        expect(wrapper.vm._isVue).toBe(true);
    });

    test('owns Questions', () => {

        const questionexplorer = shallowMount(QuestionExplorer, {
            localVue,
            store,
            data() {
                return {
                    active: [],
                    questiongroupDragging: false,
                    draggedQuestionGroup: null,
                    questionDragging: false,
                    draggedQuestions: null,
                    draggedQuestionsGroup: null,
                }
            },
        });
        const wrapper = shallowMount(SideBar, {
            localVue,
            store,
            propsData: {
                landOnTab: 'Settings',
                isSideMenuElementActive: false,
                activeSideMenuElement: ''
            },
            data() {
                return {
                    activeMenuElement: 0,
                    openSubpanelId: 0,
                    menues: [],
                    collapsed: false,
                    sideBarWidth: "315",
                    sideBarHeight: "400px",
                    initialPos: {
                        x: 0,
                        y: 0
                    },
                    isMouseDown: false,
                    isMouseDownTimeOut: null,
                    showLoader: false,
                    loading: true,
                    hiddenStateToggleDisplay: 'flex',
                    smallScreenHidden: false,
                }
            },
            stubs: {
                'registered-component': questionexplorer,
                'another-component': true,
            }
        });
        expect(wrapper.vm.$children).toBeDefined();
        expect(wrapper.vm.$children[0].$options._base.component.name).toBe('QuestionExplorer');
    });
});