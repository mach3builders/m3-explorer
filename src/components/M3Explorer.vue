<template>
    <div class="m3-explorer">
        <div class="m3-explorer-header"></div>
        <div class="m3-explorer-body">
            <m3-explorer-items-group v-for="group in data" :key="group.id" :data="group" :urls="urls" :events="events" />
        </div>
    </div>
</template>

<script>
import axios from 'axios'
import M3ExplorerItemsGroup from './M3ExplorerItemsGroup'

export default {
    components: {
        M3ExplorerItemsGroup,
    },

    props: {
        settings: Object,
    },

    data() {
        const settings = this.mergeSettings()

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
        this.$root.eventHub.$on('set-active-item', this.setActiveItem)
        this.$root.eventHub.$on('set-renaming-item', this.setRenamingItem)
        this.$root.eventHub.$on('hide-renaming-item-dropdown', this.hideRenamingItemDropdown)

        // load data
        if (this.urls.loadData) {
            axios.get(this.urls.loadData)
            .then((response) => {
                this.data = response.data
                this.initActiveItem()
            })
            .catch((error) => {
                console.error(error)
            })
        } else {
            console.warn('You did not pass a data url to the Explorer.')
        }
    },

    beforeDestroy() {
        this.$root.eventHub.$off('set-active-item', this.setActiveItem)
        this.$root.eventHub.$off('set-renaming-item', this.setRenamingItem)
        this.$root.eventHub.$off('hide-renaming-item-dropdown', this.hideRenamingItemDropdown)
    },

    methods: {
        mergeSettings() {
            const events = {}
            const urls = {}

            Object.assign(events, {
                loadItemData: (data) => {
                    console.warn('You have to define an event when an item is loaded.')
                }
            }, this.settings.events)

            Object.assign(urls, {
                loadData: ''
            }, this.settings.urls)

            return {
                events: events,
                urls: urls,
            }
        },

        initActiveItem() {
            for (let a=0; a<this.data.length; a++) {
                const group = this.data[a]
                var items

                if (group.items.length) {
                    items = group.items
                } else {
                    const staticItems = group.items.static || []
                    const dynamicItems = group.items.dynamic || []
                    items = staticItems.concat(dynamicItems)
                }

                if (items.length) {
                    const activeItem = items.find(item => item.active)
                    if (activeItem) {
                        this.activeItem = activeItem
                        break
                    }
                }
            }
        },

        setActiveItem: function(item) {
            if (this.activeItem) {
                this.$set(this.activeItem, 'active', false)
            }
            this.activeItem = item
        },

        setRenamingItem: function(item) {
            this.renamingItem = item
        },

        hideRenamingItemDropdown() {
            if (this.renamingItem) {
                this.renamingItem.renaming = false
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
</style>
