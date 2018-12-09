<template>
    <div class="m3-explorer">
        <div class="m3-explorer-header"></div>
        <div class="m3-explorer-body">
            <m3-explorer-items-group v-for="group in data" :key="group.id" :data="group" :settings="groupSettings" />
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
        return {
            activeItem: null,
            renamingItem: null,
            data: [],
            groupSettings: this.mergeSettings(),
        }
    },

    created() {
        // listen to events
        this.$root.eventHub.$on('explorer-items-group:sort-items', this.sortItems)
        this.$root.eventHub.$on('explorer-item:activate', this.setActiveItem)
        this.$root.eventHub.$on('explorer-item:rename', this.setRenamingItem)
        this.$root.eventHub.$on('explorer-item:sort', this.sortItems)
        this.$root.eventHub.$on('explorer-item:hide-renaming-popper', this.hideRenamingItemPopper)

        // load data
        if (this.groupSettings.urls.loadData) {
            axios.get(this.groupSettings.urls.loadData)
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
        this.$root.eventHub.$off('explorer-items-group:sort-items', this.sortItems)
        this.$root.eventHub.$off('explorer-item:activate', this.setActiveItem)
        this.$root.eventHub.$off('explorer-item:rename', this.setRenamingItem)
        this.$root.eventHub.$off('explorer-item:sort', this.sortItems)
        this.$root.eventHub.$off('explorer-item:hide-renaming-popper', this.hideRenamingItemPopper)
    },

    methods: {
        mergeSettings() {
            const events = {}
            const text = {}
            const urls = {}

            Object.assign(events, {
                getItem: (data) => {
                    console.warn('You can define an event when an item is fetched.')
                }
            }, this.settings.events)

            Object.assign(text, {
                addItemAction: 'Add',
                renameItemAction: 'Rename',
                moveItemAction: 'Move',
                moveItemSelectOption: 'Select an item',
                removeItemAction: 'Remove',
            }, this.settings.text)

            Object.assign(urls, {
                loadData: ''
            }, this.settings.urls)

            return {
                events: events,
                text: text,
                urls: urls,
            }
        },

        sortItems(data) {
            if (data.length) {
                data.sort(function(a, b) {
                    const nameA = a.name.toUpperCase()
                    const nameB = b.name.toUpperCase()

                    if (nameA < nameB) {
                        return -1
                    }
                    if (nameA > nameB) {
                        return 1
                    }

                    return 0
                });
            }

            return data
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

        setActiveItem(item) {
            if (this.activeItem) {
                this.$set(this.activeItem, 'active', false)
            }
            this.activeItem = item
        },

        setRenamingItem(item) {
            this.renamingItem = item
        },

        hideRenamingItemPopper() {
            if (this.renamingItem) {
                this.renamingItem.renaming = false
            }
        }
    }
}
</script>
