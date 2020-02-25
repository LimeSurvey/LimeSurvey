<script>
import empty from 'lodash/isEmpty';

export default {
    name: 'AbstractInputType',
    props: {
        elId: {type: String, required: true},
        elName: {type: [String, Boolean], default: ''},
        elLabel: {type: String, default: ''},
        elHelp: {type: String, default: ''},
        currentValue: {default: ''},
        elOptions: {type: Object, default: {}},
        readonly: {type: Boolean, default: false },
        debug: {type: [Object, Boolean]},
    },
    data(){
        return {
            triggerShowHelp: false
        };
    },
    computed: {
        curValue: {
            get() {  return this.currentValue || this.elOptions.default },
            set(newValue) { 
                this.$emit('change', newValue);
            },
        },
        getClasses() {
            if(!empty(this.elOptions.classes)) {
                return this.elOptions.classes.join(' ')
            }
            return '';
        },
        showHelp(){
            return this.triggerShowHelp && (this.elHelp.length>0);
        },
        hasPrefix(){
            if(!empty(this.elOptions.inputGroup)){
                return !empty(this.elOptions.inputGroup.prefix);
            }
            return false;
        },
        hasSuffix(){
            if(!empty(this.elOptions.inputGroup)){
                return !empty(this.elOptions.inputGroup.suffix);
            }
            return false;
        },
    },
}
</script>
