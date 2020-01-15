<script>

import map from "lodash/map";
import Divider from "./TopBarDivider.vue";
import Button from "./TopBarButton.vue";
import ButtonGroup from "./TopBarButtonGroup.vue";

export default {
    name: "TopBarContent",
    data() {
        return {
            dropdownOpen: false,
            itemId: 'itm-'+Math.floor(1+Math.random()*10000000),
            loading: false
        }
    },
    props: {
        leftButtons: {type: Array|Object, required: true},
        rightButtons: {type: Array|Object, required: true},
        slotbutton: {type: String, default: ''}
    },
    methods: {
        dropdowntrigger(state) {
            this.dropdownOpen = state;
        }
    },
    render(h) {
        const applyButtons = (button) => {
            if( button.dropdown !== undefined && button.class.includes('btn-group') ) {
                return (<li key = {button.id}>
                    <ButtonGroup
                        dropdownOpen = {this.dropdownOpen}
                        class = {button.class}
                        list = {button.dropdown}
                        mainButton = {button.main_button}
                        onDropdowntrigger = {this.dropdowntrigger}
                    />
                </li>);
            } else if(button.class.includes('divider')) {
                return (<li key = {button.id}> <Divider button = {button} /></li>);
            } else {
                const toggleLoading = (ev) => {this.loading = ev};
                return (
                    <li key = {button.id}> 
                        <Button 
                            button={button} 
                            loading={this.loading} 
                            onToggleLoading={toggleLoading} 
                        />
                    </li>);
            }
        };

        const leftButtonsHTML = map(this.leftButtons, applyButtons);
        const rightButtonsHTML = map(this.rightButtons, applyButtons);

        return (
            <div class = "ls-flex ls-flex-row ls-space padding top-5" id = {this.itemId}>
                <ul class = "nav navbar-nav scoped-topbar-nav ls-flex-item ls-flex-row grow-2 text-left">
                    {leftButtonsHTML}
                    {this.slotbutton != '' ? <li key = "slotbutton-content" class = "slotbutton-content" domProps = {{ innerHTML: this.slotbutton}} /> : '' }
                </ul>
                {this.rightButtons.length>0
                    ? <ul
                        class = "nav navbar-nav scoped-topbar-nav ls-flex-item ls-flex-row align-content-flex-end text-right padding-left scoped-switch-floats"
                    > {rightButtonsHTML}  </ul>
                    : ''
                }
            </div>
        )
    }
}
</script>

<style lang="scss" scoped>
    .scoped-topbar-nav {
        float: none;
        width: initial;
        max-width: 100%;
        min-width: 30%;
        padding-top:4px;
        flex-wrap: wrap;
    }
</style>
