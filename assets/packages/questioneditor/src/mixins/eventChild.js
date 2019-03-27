export default {
    props: {
        event : {type: Object, default: null}
    },
    watch: {
        event(newEvent, oldEvent) {
            if(newEvent !== null) {
                if(this.$options.name == newEvent.target 
                    && (typeof this[newEvent.method] == 'function' )) {
                    try{
                        this[newEvent.method](newEvent.content);
                        this.$emit('eventSet');
                    } catch (e) {
                        this.$log.error('Event handling errored', e);
                    }
                    return;
                }
                this.$log.log('Event skipped to next child', newEvent, this.name);
            }
        }
    },
    methods: {
        eventSet() {
            this.$emit('eventSet');
        },
    }
}