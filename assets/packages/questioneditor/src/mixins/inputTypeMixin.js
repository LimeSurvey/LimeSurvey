export default {
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
}