<script>
import he from 'he';

export default {
    name: "TopBarLink",
    props: {
        item: {type: Object, required: true},
        active: {type: Boolean, default: true},
        preview_title: {type: String, default: ''}
    },
    data() {
        return {};
    },
    render(h) {
        return (
            <div class="topbarlink">
                <a
                    href={this.item.url}
                >
                    {this.item.icon != '' ? <i class={this.item.icon + ' icon'} /> : '' }
                    {he.decode('&nbsp;')}{ he.decode(this.item.name) }{he.decode('&nbsp;')}
                    { this.preview_title != '' ? <small><i>{ he.decode(this.preview_title)}</i></small> : ''}
                </a>
            </div>
        );
    }
};
</script>

<style lang="scss" scoped>
.topbarlink a {
    white-space: nowrap;
}

$black: #212529;
$white: #ffffff;

.icon {
    margin-right: 2px;
}

a {
    display: block;
    padding: 0.55em 1em;
    text-decoration: none;
    color: $black;

    &:hover {
        text-decoration: none;
        color: $white;
        background-color: var(--LS-admintheme-hovercolor);
    }

    &.active {
        background-color: var(--LS-admintheme-infocolor);
    }
}
</style>
