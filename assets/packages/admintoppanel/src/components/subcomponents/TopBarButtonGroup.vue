<script>
import DropDown from "./TopBarDropDown.vue";
import Button from "./TopBarButton.vue";

export default {
    name: "TopBarButtonGroup",
    props: {
        list: {type: Object, default() { return null }},
        mainButton: {type: Object, required: true },
        dropdownOpen: {type: Boolean|Number, required: true },
    },
    data: () => {
        return {};
    },
    methods: {
        dropdowntrigger(state) {
            this.$emit('dropdowntrigger', state);
        } 
    },
    render(h) {
        return (
            <div class="topbarbuttongroup">
                { this.list != null
                    ? <DropDown 
                        list={this.list} 
                        mainButton={this.mainButton}
                        onDropdowntrigger = {this.dropdowntrigger} 
                        dropdownOpen = {this.dropdownOpen}
                    />
                    : <Button button={this.mainButton} />
                }
            </div>
        );                        
    }
};
</script>

<scoped style>

</style>
