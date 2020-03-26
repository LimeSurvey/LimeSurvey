<script>
import he from 'he';

export default {
    name: "TopBarButton",
    props: {
        button: {type: Object|Array, required: true},
        loading: {type: Boolean, default: false}
    },
    data() { return {} },
    computed: {
        isLoading: {
            get() { return this.loading },
            set(nV) { this.$emit('toggleLoading', nV); }
        },
        buttonIcon() {
            if(this.button.isSaveButton && this.isLoading === true) {
                return "fa fa-cog fa-spin";
            }
            return this.button.icon || "";
        },
        buttonUrl() {
            return this.button.isCloseButton ? this.$store.state.closeButtonUrl : this.button.url;
        },
        dataAttributes() {
            const dataAttributes = {}; 
            LS.ld.forEach(this.button.data,  (attributeValue, attributeKey) => {
                dataAttributes[`data-${attributeKey}`] = attributeValue
            });
            return dataAttributes;
        }
    },
    methods: {
        runConfirmPost() {
            let postdata = {};
            try {
                postdata = JSON.parse(this.button.postdata);
            } catch(e){
                this.$log.error('ERROR: Postdata no valid json, exiting');
            }
            postdata.ajax = 1;

            this.__runAjax(
                this.button.dataurl,
                postdata,
                'POST',
                ''
            ).then(
                (result) => {
                    this.$log.log(result);
                    window.LS.notifyFader(result.data.message, 'well-lg text-center ' + (result.data.success ? 'bg-primary' : 'bg-danger'));
                    setTimeout(() => {window.location.href = result.data.redirect}, 1500);
                }, 
                (reject) => {
                    this.$log.error(reject);
                }
            )
        },
        clicked(event) {
            if(this.button.type == 'confirm') {
                $.bsconfirm(
                    this.button.message,
                    LS.lang.confirm, 
                    ()=>{
                        this.runConfirmPost();
                        $('#identity__bsconfirmModal').modal('hide');
                    },
                    () => {
                         $('#identity__bsconfirmModal').modal('hide');
                    }
                );
                return false;
            }

            if(this.button.isSaveButton && this.isLoading === true) {
                return false;
            }
            

            if(this.button.triggerEvent) {
                LS.EventBus.$emit(this.button.triggerEvent, this.button);
                return false;
            }

            this.$log.log('Button clicked -> ', this.button);
            if (this.button.isSaveButton) {
                event.preventDefault();
                this.isLoading = true;
                LS.EventBus.$emit("saveButtonCalled", this.button);
                return false;
            }

            if (this.button.isCloseButton) {
                event.preventDefault();
                this.isLoading = true;
                window.location.href = this.$store.state.closeButtonUrl;
                return false;
            }
            return true;
        }
    },
    mounted() {
        LS.EventBus.$on('loadingFinished', () => {
            if(this.isLoading) {
                this.isLoading=false;
            }
        });
    },
    render(h) {
        const button = this.button;

        if(button.class === 'dropdown-toggle') {
            return (
            <div class="topbarbutton">
                <button
                    type="button"
                    class={'btn btn-default navbar-btn button ' + button.class}
                    id={button.id}
                    data-toggle={button.datatoggle}
                    aria-haspopup={button.ariahaspopup}
                    aria-expanded={button.ariaexpanded}
                    onClick={this.clicked}
                    data-btntype="2"
                >
                    <i class={this.buttonIcon + ' icon'} />
                    {he.decode("&nbsp;")}{he.decode(button.name)}{he.decode("&nbsp;")}
                    <i class={button.iconclass + ' icon'} />
                </button>
            </div>
            )
        } 

        let classAddition;
        if (this.buttonIcon.includes('clock-o')) {
            classAddition = ' btn-default ';
        } else if (this.buttonIcon.includes('-o') || button.class.includes('btn-danger') || button.class.includes('btn-success')) {
            classAddition = ' white ';
        } else {
            classAddition = ' btn-default ';
        }

        return  (
            <div class="topbarbutton">
                <a
                    type="button"
                    class= {
                        'btn navbar-btn button '
                        + classAddition
                        + button.class
                    }
                    href={this.buttonUrl}
                    id={button.id}
                    data-placement={button.dataplacement || null }
                    data-toggle={button.datatoggle || null }
                    {...{attrs: this.dataAttributes}}
                    title={button.title || null }
                    target={button.target || null }
                    access-key={button.accesskey || null }
                    data-btntype="1"
                    disabled={button.isSaveButton && this.isLoading ? true : false}
                    onClick={this.clicked}
                >
                    { this.buttonIcon != '' 
                        ? <i class={this.buttonIcon + ' icon'} />
                        : ''
                    }
                    {he.decode("&nbsp;")}{he.decode(button.name)}{he.decode("&nbsp;")}
                    { button.iconclass !== undefined 
                        ? <i class={'icon ' + button.iconclass} />
                        : ''
                    }
                </a>
            </div>
        )
    }
};
</script>

<style scoped lang="scss">

.topbarbutton {
    position: relative;
    display: inline-block;
    vertical-align: middle;
    &>a {
        margin:1px;
    }
    .navbar-btn {
        margin-top: 0;
    }
}
.icon {
    margin-right: 2px;
}

.white {
    color: white;
}

.btn-danger {
    color: white;
}
</style>
