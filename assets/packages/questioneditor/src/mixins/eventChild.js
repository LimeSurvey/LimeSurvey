export default {
    props: {
        event : {type: Object, default: null}
    },
    watch: {
        event(newEvent, oldEvent) {
            if(newEvent !== null) {
                if(this.$options.name == newEvent.target) {
                    try{
                        this[newEvent.method](newEvent.content);
                        this.$emit('eventSet');
                    } catch (e) {
                        this.$log.error('EVENT HANDLING ERRORED', e);
                    }
                    return;
                }
                this.$log.log('EVENT SKIPPED', newEvent, this.name);
            }
        }
    }
}