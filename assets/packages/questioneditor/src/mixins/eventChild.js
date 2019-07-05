import isArray from 'lodash/isArray';
import indexOf from 'lodash/indexOf';

export default {
    props: {
        event : {type: Object, default: null}
    },
    watch: {
        event(newEvent, oldEvent) {
            if(newEvent !== null) {
                const affected = isArray(newEvent.target) 
                    ? (indexOf(newEvent.target ,this.$options.name)!=-1 )
                    : (newEvent.target == this.$options.name);
                if( affected && (typeof this[newEvent.method] == 'function' )) {
                    try{
                        this[newEvent.method](newEvent.content);
                    } catch (e) {
                        this.$log.error('Event handling errored', e);
                    }
                    if(newEvent.chain) {
                        newEvent.target = newEvent.chain;
                        newEvent.chain == null;
                        this.$emit('triggerEvent', newEvent);
                    } else {
                        this.$emit('eventSet');
                    }
                    return;
                }
                this.$log.log('Event skipped to next child', newEvent, this.name);
            }
        }
    },
    methods: {
        eventSet() {
            this.$emit('eventSet', this.$options.name);
        },
        triggerEvent(event) {
            this.$emit('triggerEvent', event);
        }
    }
}