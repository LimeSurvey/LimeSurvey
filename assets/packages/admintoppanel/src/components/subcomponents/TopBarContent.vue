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
                return (<li key = {button.id}> <Button button = {button} /></li>);
            }
        };

        const leftButtonsHTML = map(this.leftButtons, applyButtons);
        const rightButtonsHTML = map(this.rightButtons, applyButtons);

        return (
            <div class = "ls-flex ls-flex-row" id = {this.itemId}>
                <ul class = "nav navbar-nav ls-flex-item ls-flex-row text-left grow-3">
                    {leftButtonsHTML}
                    {this.slotbutton != '' ? <li key = "slotbutton-content" domProps = {{ innerHTML: this.slotbutton}} /> : '' }
                </ul>
                {this.rightButtons.length>0
                    ? <ul
                        class = "nav navbar-nav ls-flex-item  ls-flex-row align-content-flex-end text-right padding-left scoped-switch-floats"
                    > {rightButtonsHTML}  </ul>
                    : ''
                }
            </div>
        )
    }
}
</script>
