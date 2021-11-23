import  { shallowMount, createLocalVue }  from '@vue/test-utils';
import Vuex from 'vuex';
import SideBar from '../src/components/sidebar.vue';
import QuestionExplorer from '../src/components/subcomponents/_questionsgroups.vue';

// Mixins
import pjaxMixins from '../src/mixins/pjaxMixins.js';
import translateMixins from '../src/mixins/translateMixins.js';

const localVue = createLocalVue();
localVue.use(Vuex);

describe('Admin Sidemenu Funtionalities', () => {

    test('does it exists', () => {
        const store = new Vuex.Store({
            state: {
            },
        });
        localVue.use(store);
        localVue.use(translateMixins);

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
        wrapper.destroy();
    });

    test('owns Questions', () => {
        const store = new Vuex.Store({
            state: {
                'lastQuestionGroupOpen': 1,
                'questiongroups': [
                    {gid: 1}
                ],
                'questionGroupOpenArray': [],
                'lastQuestionGroupOpen': false,
                'SideMenuData': {
                    'isActive': false,
                    'createQuestionGroupLink': 'createQuestionGroupLinkMock',
                    'createQuestionLink': 'createQuestionLinkMock',
                }
            },
            mutations: {
                newToggleKey (state) {
                    state.toggleKey = Math.floor(Math.random()*10000)+'--key';
                },
            }
        });
        localVue.use(store);
        localVue.use(pjaxMixins);
        localVue.use(translateMixins);

        const questionexplorer = shallowMount(QuestionExplorer, {
            localVue,
            store,
            data() {
                return {
                    openQuestionGroups: [],
                    currentlyDraggingQuestionGroups: false,
                    draggedQuestionGroup: null,
                    questionDragging: false,
                    draggedQuestions: null,
                    draggedQuestionsGroup: null,
                    questionGroups: [],
                    lastQuestionGroupOpened: false,
                    isSurveyActive: false,
                    createQuestionGroupLinkString: '',
                    sideMenuData: [],
                }
            },
            mixins: pjaxMixins,
        });
      /*  const wrapper = shallowMount(SideBar, {
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
        });*/

     //   expect(wrapper.vm.$children).toBeDefined();
     //   expect(wrapper.vm.$children[0].$options._base.component.name).toBe('QuestionExplorer');

        questionexplorer.destroy();
        wrapper.destroy();
    });
});