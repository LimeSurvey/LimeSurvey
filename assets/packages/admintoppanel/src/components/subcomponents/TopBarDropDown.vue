<script>
import map from 'lodash/map';
import Button from "./TopBarButton.vue";
import Link from "./TopBarLink.vue";
import Seperator from "./Seperator.vue";
import DropDownHeader from "./DropDownHeader.vue";
import DropDownSubmenu from "./DropDownSubmenu.vue";

// TODO: Es wird auch bei einem Seperator oder DropDownHeader Element ein Link-Element mitgerendert.
// TODO: Wie kann man das unterdrücken?

export default {
    name: "TopBarDropDown",
    components: {
        "button-element": Button,
        "link-element": Link,
        "seperator-element": Seperator,
        "dropdown-header-element": DropDownHeader,
        "dropdown-submenu": DropDownSubmenu
    },
    props: {
        list : {type: Object, required: true}, 
        mainButton : {type: Object, required: true}, 
        dropdownOpen : {type: Boolean|Number, default: false}
    },
    computed: {
        globalDropdown : {
            get() {return this.dropdownOpen;},
            set(newState) {this.$emit('dropdowntrigger', newState);}
        }
    },
    data: () => {
        return {
            uniquid: Math.floor((1 + Math.random()) * 1000000),
            isOpen: false,
            isActive: false,
            selectedItem: 0
        };
    },
    watch: {
        dropdownOpen(newState, oldState) {
            if(this.uniquid != newState) {
                this.isOpen = false;
            }
        }
    },
    methods: {
        emitDropdownTrigger(open) {
            this.$emit('dropdowntrigger', this.isOpen);
        },
        toggleOpen() {
            if(this.globalDropdown) {
                this.globalDropdown = false;
                console.log("Another dropdown is open, closing it");
            }
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                console.log("Dropdown is open");
                this.globalDropdown = this.uniquid;
                $("body").on(
                    "click.topbardropdown" + this.uniquid,
                    ":not(.topbardropdown)",
                    () => {
                        this.isOpen = false;
                        $("body").off("click.topbardropdown" + this.uniquid);
                        this.globalDropdown = false;
                    }
                );
            } else {
                console.log("Dropdown is closed");
                $("body").off("click.topbardropdown" + this.uniquid);
            }
        },
        handleClick(e) {
            e.stopPropagation();
            this.toggleOpen();
        },
        handleLinkClick() {
            this.isActive = true;
        }
    },
    render(h) {

        const listItems =  map(this.list.items, (item, key) => {
            if(item.class != undefined && item.class.includes('btn-group')) {
                return <li key={key}><DropDownSubmenu onDropdowntrigger={this.emitDropdownTrigger} item={item} /></li>;
            } else if (item.class != undefined && item.class.includes('divider')) {
                return <li key={key}><Seperator item={item} /></li>;
            } else if(item.class != undefined && item.class.includes('dropdown-header')) {
                return <li key={key}><DropDownHeader item={item} onClick={this.handleLinkClick} /></li>;
            } else {
                return <li key={key}><Link active={this.isActive} item={item} onClick={this.handleLinkClick} /></li>;
            }
        });

        return ( 
            <div class="topbardropdown">
                <Button button={this.mainButton} nativeOnClick={this.handleClick} />
                { this.isOpen && this.globalDropdown == this.uniquid
                    ? <ul
                        class={'dropdown-box ' + this.list.class}
                        aria-labelledby={this.list.arialabelledby}
                    >
                        {listItems}
                        
                    </ul>
                    : ''
                }   
            </div>);
    }
};
</script>

<style scoped lang="scss">
$black: #212529;
$white: #ffffff;
$green: #00b248;
$green-active: #66ffa6;

ul {
    list-style-type: none;
    li {
        position: relative;
        margin: 0.2em;
        text-align: left;
    }
}

* {
    box-sizing: border-box;
}

.dropdown-box {
    position: absolute;
    top: 100%;
    left: 0;
    display: block;
    float: left;
    border: 1px solid rgba(0, 0, 0, 0.15);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.175);
    background-color: $white;
    width: auto;
    max-width: 80vw;
}
</style>
