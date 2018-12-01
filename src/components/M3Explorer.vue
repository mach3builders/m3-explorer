<template>
    <div class="m3-explorer">
        <div class="m3-explorer-header"></div>
        <div class="m3-explorer-body">
            <m3-explorer-items-group v-for="group in data" :key="group.id" :data="group" :urls="urls" :events="events" />
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import M3ExplorerItemsGroup from './M3ExplorerItemsGroup';

export default {
    components: {
        M3ExplorerItemsGroup,
    },

    props: {
        settings: Object,
    },

    data() {
        const settings = this.mergeSettings();

        return {
            activeItem: null,
            renamingItem: null,
            data: [],
            events: settings.events,
            urls: settings.urls,
        }
    },

    created() {
        // listen to events
        this.$root.eventHub.$on('set-active-item', this.setActiveItem);
        this.$root.eventHub.$on('set-renaming-item', this.setRenamingItem);
        this.$root.eventHub.$on('hide-renaming-item-dropdown', this.hideRenamingItemDropdown);

        // load data
        if (this.urls.loadData) {
            axios.get(this.urls.loadData)
            .then((response) => {
                this.data = response.data;

                this.initActiveItem();
            })
            .catch((error) => {
                console.error(error);
            });
        } else {
            console.warn('You did not pass a data url to the Explorer.');
        }
    },

    beforeDestroy() {
        this.$root.eventHub.$off('set-active-item', this.setActiveItem);
        this.$root.eventHub.$off('set-renaming-item', this.setRenamingItem);
        this.$root.eventHub.$off('hide-renaming-item-dropdown', this.hideRenamingItemDropdown);
    },

    methods: {
        mergeSettings() {
            const events = {};
            const urls = {};

            Object.assign(events, {
                loadItemData: (data) => {
                    console.warn('You have to define an event when an item is loaded.');
                }
            }, this.settings.events);

            Object.assign(urls, {
                loadData: ''
            }, this.settings.urls);

            return {
                events: events,
                urls: urls,
            }
        },

        initActiveItem() {
            for (let a=0; a<this.data.length; a++) {
                const group = this.data[a];
                var items;

                if (group.items.length) {
                    items = group.items;
                } else {
                    const staticItems = group.items.static || [];
                    const dynamicItems = group.items.dynamic || [];
                    items = staticItems.concat(dynamicItems);
                }

                if (items.length) {
                    const activeItem = items.find(item => item.active);
                    if (activeItem) {
                        this.activeItem = activeItem;
                        break;
                    }
                }
            }
        },

        setActiveItem: function(item) {
            if (this.activeItem) {
                this.$set(this.activeItem, 'active', false);
            }
            this.activeItem = item;
        },

        setRenamingItem: function(item) {
            this.renamingItem = item;
        },

        hideRenamingItemDropdown() {
            if (this.renamingItem) {
                this.renamingItem.renaming = false;
            }
        }
    }
}
</script>

<style lang="scss">
    .m3-explorer {
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        user-select: none;
        width: 15rem;
    }

    .m3-explorer-header {
        background-color: #fff;
        border-bottom: 1px solid #e8e8e8;
        flex-shrink: 0;
        height: 3rem;
    }

    .m3-explorer-body {
        flex-grow: 1;
        overflow: auto;
        padding: 1.5rem 0.75rem;
        position: relative; /* do not remove, it's needed for dropdown positioning */
    }

    .m3-explorer-items-group {
        &:not(:last-child) {
            margin-bottom: 2rem;
        }
    }

    .m3-explorer-items-group-name-wrapper {
        align-items: center;
        display: flex;
        margin-bottom: 0.5rem;

        .m3-dropdown {
            padding: 0.5rem;

            .m3-form-field {
                margin-right: 0.5rem;
            }
        }
    }

    .m3-explorer-items-group-name {
        flex-grow: 1;
        font-family: $font-family-bold;
        font-size: 0.625rem;
        color: #747474;
        height: 1rem;
        letter-spacing: 0.5px;
        line-height: 1.6;
        overflow: hidden;
        margin-right: 0.5rem;
        padding-left: 0.5rem;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .m3-explorer-items {
        &:not(:last-child) {
            border-bottom: 1px solid #e8e8e8;
            margin-bottom: 0.25rem;
            padding-bottom: 0.25rem;
        }
    }

    .m3-explorer-item-wrapper {
        > .m3-collapse {
            margin: 0.25rem 0 0 0.5rem;
        }

        &:not(:last-child) {
            margin-bottom: 0.25rem;
        }
    }

    .m3-explorer-item {
        display: flex;
    }

    .m3-explorer-item-expand {
        border-radius: 3px;
        color: #747474;
        flex-shrink: 0;
        height: 1.25rem;
        margin: 0.625rem 0 0 0.25rem;
        transition: background-color 200ms, color 200ms;
        width: 1.25rem;

        &:hover {
            background-color: #e8e8e8;
            color: #020202;
        }
    }

    .m3-explorer-item-name-wrapper {
        align-items: flex-start;
        border-radius: 3px;
        color: #434343;
        display: flex;
        flex-grow: 1;
        transition: background-color 200ms, color 200ms;

        &:hover,
        &.m3-loading,
        &.m3-focus,
        &.m3-active {
            background-color: #e8e8e8;
            color: #020202;
        }

        .m3-button {
            align-self: center;
            flex-shrink: 0;
            opacity: 0;
            transition: opacity 200ms;
        }

        &.m3-loading {
            .m3-explorer-item-icon {
                @include loading;
            }
        }

        &:hover,
        &.m3-focus {
            .m3-button {
                opacity: 1;
            }
        }

        &:hover,
        &.m3-active {
            .m3-explorer-item-icon {
                color: #434343;
            }

            .m3-explorer-item-input {
                > input {
                    box-shadow: inset 0 -1px 0 #b6b6b6;
                }
            }
        }
    }

    .m3-explorer-item-icon {
        box-sizing: border-box;
        color: #747474;
        flex-shrink: 0;
        height: 2.5rem;
        transition: color 200ms;
        width: 2.125rem;
    }

    .m3-explorer-item-name,
    .m3-explorer-item-input {
        flex-grow: 1;
        overflow: hidden;
        padding: 0.5rem;
        padding-left: 0;
        white-space: nowrap;
    }

    .m3-explorer-item-input {
        > input {
            background-color: transparent;
            border: 0;
            box-shadow: inset 0 -1px 0 #d7d7d7;
            box-sizing: border-box;
            font-family: $font-family-regular;
            font-size: 0.75rem;
            height: 1.5rem;
            padding: 0;
            transition: box-shadow 200ms;
            width: 100%;

            &:focus {
                outline: 0;
            }
        }
    }

    .m3-explorer-item-remove-confirm {
        background-color: #f9f9f9;
        border-top: 1px solid #e8e8e8;
        padding: 0.5rem 0.75rem;
        transition: margin-top 300ms;
        z-index: 1;

        &.m3-show {
            margin-top: -3rem;
            top: -1px;
        }
    }
</style>
