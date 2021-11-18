import  {mount}  from '@vue/test-utils';

describe('Admin Sidemenu Funtionalities', () => {

    beforeEach(() => {
        const AdminSidePanelComponent = {
            template: '',
            props: ['landOnTab', 'isSideMenuElementActive', 'activeSideMenuElement']
        };        
    });

    test('does it exists', () => {
        const wrapper = mount(AdminSidePanelComponent, {
            propsData: {
                landOnTab: 'Settings',
                isSideMenuElementActive: false,
                activeSideMenuElement: ''
            }
        });

        expect(wrapper.isVueInstance.toBeTruthy());
    });
});