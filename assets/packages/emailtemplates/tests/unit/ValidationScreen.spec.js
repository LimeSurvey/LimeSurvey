import {
    shallowMount,
    createLocalVue
} from '@vue/test-utils'
import _ from 'lodash';
import Vuex from 'vuex';

import Loader from '_@/helperComponents/loader.vue';
import ValidationScreen from '_@/components/ValidationScreen.vue';
import mockState from '../mocks/mockState.js';
import validationHtml from '../mocks/validationHtml.html';

global.LS = {
    ld: _,
    data: {
        csrfTokenName : 'xxx',
        csrfToken : 'xxx',
    }
};
const translate =  {
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
};

const localVue = createLocalVue();
const mockAjax = jest.fn(
    (settings) => {
        settings.success(validationHtml, 400, []);
    }
);

localVue.use(Vuex);
localVue.component('loader-widget', Loader);
localVue.mixin(translate);

global.EmailTemplateData = {
    validatorUrl: "getValidationHtml",
    surveyid: 0
};
global.$ = {
    ajax: mockAjax
};

describe("ValidationScreen correctly rendering", () => {
    let store;
    let wrapper;

    beforeEach(() => {
        store = new Vuex.Store({
                    state: mockState,
                    mutations: {},
                    actions: {}
                });
        
    
        wrapper = shallowMount(ValidationScreen, {
            mocks: {
                $log: {
                    log: ()=>{}
                }
            },
            store,
            localVue
        });
    });

    test('calls html on created', () => {
        expect(mockAjax).toHaveBeenCalled();
    });

    test('header correctly set (no header)', () => {
        expect(wrapper.find('#emailtemplates--validation-header').exists()).toBe(false);
    });

    test('header correctly set (header)', () => {
        wrapper.setProps(
            {header: 'Header set'}
        )
        expect(wrapper.find('#emailtemplates--validation-header').text()).toBe('Header set') ;
    });

    test('stripHtml strips html', () => {
        const test = "<script>console.log('This should not be executed');</script>"
        const stripped = wrapper.vm.stripScripts(test)
        expect(stripped).toMatch(`<pre>[script]
console.log('This should not be executed');
[/script]</pre>`);
    });

    test('shows the correct html', () => {
        expect(wrapper.find('#test_headline').text()).toMatch('This is a test')
        && expect(wrapper.find('#test_prepart').length).toBe(1);
    })

});
