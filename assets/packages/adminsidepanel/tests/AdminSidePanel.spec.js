import  { shallowMount }  from '@vue/test-utils';
import {SideBar} from '../src/components/sidebar.vue';

describe('Admin Sidemenu Funtionalities', () => {

    beforeEach(() => {
        const BaseSideBar = {
            template: '',
            props: ['landOnTab', 'isSideMenuElementActive', 'activeSideMenuElement']
        };        
    });

    test('does it exists', () => {
        const wrapper = shallowMount(SideBar, {
            propsData: {
                landOnTab: 'Settings',
                isSideMenuElementActive: false,
                activeSideMenuElement: ''
            }
        });

        expect(wrapper.isVueInstance.toBeTruthy());
    });
});