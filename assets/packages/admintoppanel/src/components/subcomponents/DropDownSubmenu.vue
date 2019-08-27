<script>
import map from 'lodash/map';
import Button from "./TopBarButton.vue";
import Link from "./TopBarLink.vue";
import Seperator from "./Seperator.vue";
import DropDownHeader from "./DropDownHeader.vue";

export default {
    name: "DropDownSubmenu",
    props: {
        item: {type: Object, required: true},
    },
    components: {
        "link-element": Link,
    },
    data() {
        return {
            isOpen: false,
            isActive: false
        };
    },
    computed: {
        mainButton() { return this.item.main_button },
        list() { return this.item.dropdown.items; },
        id() { return this.item.dropdown.id }
    },
    methods: {
        toggleOpen() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                console.log("Dropdown is open");
                $("body").on(
                    "click.topbardropdown" + this.uniquid,
                    ":not(.topbardropdown)",
                    () => {
                        this.isOpen = false;
                        $("body").off("click.topbardropdown" + this.uniquid);
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

        const listItems =  map(this.list, (item, key) => {
            if(item.class != undefined && item.class.includes('button-group')) {
                return <li key={key}><DropDownSubmenu parent-open={this.isOpen} item={item} /></li>;
            } else if (item.class != undefined && item.class.includes('divider')) {
                return <li key={key}><Seperator item={item} /></li>;
            } else if(item.class != undefined && item.class.includes('dropdown-header')) {
                return <li key={key}><DropDownHeader item={item} onClick={this.handleLinkClick} /></li>;
            } else {
                return <li key={key}><Link active={this.isActive} item={item} onClick={this.handleLinkClick} /></li>;
            }
        });
        return (
            <div class="topbardropdown-submenu">
                <Link item={this.mainButton} nativeOnClick={this.handleClick} has-dropdown={true}/>
                { this.isOpen 
                    ? <ul
                        id={this.id}
                        class={'dropdown-box ' + this.list.class}
                        aria-labelledby={this.list.arialabelledby}
                    >
                        {listItems}
                        
                    </ul>
                    : ''
                }   
            </div>
        );
    }
}
</script>

<style scoped lang="scss">

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
    top: 0;
    left: 100%;
    display: block;
    float: left;
    padding-left: 5px;
    border: 1px solid rgba(0, 0, 0, 0.15);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.175);
    background-color: #fff;
    width: auto;
    max-width: 80vw;    
    border-radius: 4px;
}
</style>
