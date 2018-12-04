<template>
    <div class="m3-explorer-item-wrapper">
        <div class="m3-explorer-item">
            <i class="m3-explorer-item-expand far" :class="expandClasses" v-if="hasItems()" @click="toggleItems"></i>
            <div class="m3-explorer-item-name-wrapper" :class="itemClasses" @click="click">
                <i class="m3-explorer-item-icon far" :class="iconClasses"></i>
                <div class="m3-explorer-item-name" v-if="!renaming">{{ data.name }}</div>
                <div class="m3-explorer-item-input" v-if="renaming" @click.stop>
                    <input maxlength="50" v-model="data.name" @keyup.enter="renamed" ref="input" />
                </div>

                <m3-button type="transparent" icon="ellipsis-h" v-if="actionsAllowed" @click.native.stop="showDropdown" flat></m3-button>

                <m3-dropdown v-if="actionsAllowed" ref="dropdown">
                    <ul>
                        <li @click="rename"><div>Rename</div></li>
                        <li @click="removeConfirm">
                            <div>Delete</div>
                            <div class="m3-explorer-item-remove-confirm" :class="{ 'm3-show': removeConfirmClass }">
                                <m3-buttons>
                                    <m3-button color="danger" icon="times" @click.native.stop="removeCancelled"></m3-button>
                                    <m3-button color="success" icon="check" @click.native.stop="remove"></m3-button>
                                </m3-buttons>
                            </div>
                        </li>
                    </ul>
                </m3-dropdown>
            </div>
        </div>

        <m3-collapse :open="itemsOpen" v-if="hasItems()">
            <m3-explorer-item v-for="item in staticItems"
                :key="item.id"
                :collection="staticItems"
                :data="item"
                :groupId="groupId"
                :groupIsStatic="true"
                :urls="urls"
                :events="events" />

            <m3-explorer-item v-for="item in dynamicItems"
                :key="item.id"
                :collection="dynamicItems"
                :data="item"
                :groupId="groupId"
                :groupIsStatic="groupIsStatic"
                :urls="urls"
                :events="events" />
        </m3-collapse>
    </div>
</template>

<script>
import axios from 'axios'
import M3Button from '@mach3builders/m3-components/M3Button'
import M3Buttons from '@mach3builders/m3-components/M3Buttons'
import M3Collapse from '@mach3builders/m3-components/M3Collapse'
import M3Dropdown from '@mach3builders/m3-components/M3Dropdown'

export default {
    components: {
        M3Button,
        M3Buttons,
        M3Dropdown,
        M3Collapse,
    },

    props: {
        collection: Array,
        data: Object,
        events: Object,
        groupId: Number,
        groupIsStatic: Boolean,
        urls: Object,
    },

    data() {
        return {
            focus: false,
            itemsOpen: false,
            loading: false,
            renaming: false,
            removeConfirmClass: false,
        }
    },

    computed: {
        itemClasses() {
            let classes = this.loading ? ' m3-loading' : ''
            classes += this.focus ? ' m3-focus' : ''
            classes += this.data.active ? ' m3-active' : ''
            return classes.trim()
        },

        expandClasses() {
            if (this.hasItems()) {
                return this.itemsOpen ? 'fa-angle-down' : 'fa-angle-right'
            }
            return null
        },

        iconClasses() {
            return 'fa-'+(this.data.icon || 'folder')
        },

        actionsAllowed() {
            return (!this.groupIsStatic && !this.data.static && !this.renaming)
        },

        staticItems() {
            if (this.data.items) {
                return this.data.items.static || []
            }
            return []
        },

        dynamicItems() {
            if (this.data.items) {
                return this.data.items.length ? this.data.items : (this.data.items.dynamic || [])
            }
            return []
        }
    },

    beforeCreate () {
        this.$options.components.M3ExplorerItem = require('./M3ExplorerItem')
    },

    created() {
        this.$root.eventHub.$on('document:clicked', this.renamed)
    },

    beforeDestroy() {
        this.$root.eventHub.$off('document.clicked', this.renamed)
    },

    methods: {
        hasItems() {
            return this.staticItems.length || this.dynamicItems.length
        },

        toggleItems() {
            this.itemsOpen = !this.itemsOpen
        },

        click() {
            if (this.data.active) return

            if (this.urls.loadItemData) {
                this.loading = true

                axios.get(this.urls.loadItemData, {
                    params: {
                        group_id: this.groupId || 0,
                        id: this.data.id || 0,
                    }
                })
                .then((response) => {
                    this.loading = false
                    this.$set(this.data, 'active', true)
                    this.events.loadItemData(response.data)

                    this.$root.eventHub.$emit('set-active-item', this.data)
                })
                .catch((error) => {
                    console.error(error)
                })
            }
        },

        rename() {
            this.renaming = true
            this.$root.eventHub.$emit('set-renaming-item', this)

            // focus on the input field, but first wait till the dom is updated
            this.$nextTick(() => {
                const ref = this.$refs['input']

                if (ref) {
                    ref.focus()
                    ref.setSelectionRange(0, ref.value.length)
                }
            })

            this.hideDropdown()
        },

        renamed() {
            if (this.renaming && this.data.name.trim()) {
                if (this.urls.renameItem) {
                    this.loading = true

                    // create form data, so we can catch $_POST with PHP for instance...
                    const formData = new FormData()
                    formData.append('id', this.data.id || 0)
                    formData.append('group_id', this.groupId || 0)
                    formData.append('value', this.data.name.trim() || '')

                    axios.post(this.urls.renameItem, formData)
                    .then((response) => {
                        this.loading = false
                        this.renaming = false
                        this.$root.eventHub.$emit('sort-items', this.collection)
                    })
                    .catch((error) => {
                        console.error(error)
                    })
                } else {
                    this.renaming = false
                }
            }
        },

        removeConfirm() {
            this.removeConfirmClass = true
        },

        removeCancelled() {
            this.removeConfirmClass = false
        },

        remove() {
            if (this.urls.removeItem) {
                this.loading = true

                // create form data, so we can catch $_POST with PHP for instance...
                const formData = new FormData()
                formData.append('id', this.data.id || 0)
                formData.append('group_id', this.groupId || 0)

                axios.post(this.urls.removeItem, formData)
                .then((response) => {
                    this.collection.splice(this.collection.indexOf(this.data), 1)
                    this.loading = false
                })
                .catch((error) => {
                    console.error(error)
                })
            }

            this.hideDropdown()
        },

        showDropdown: function(event) {
            this.removeCancelled()
            this.focus = true

            const ref = this.$refs['dropdown']
            ref.setDispatcher(event.currentTarget)
            ref.setHideCallback(() => {
                this.focus = false
            })
            ref.show()

            this.$root.eventHub.$emit('hide-renaming-item-dropdown')
        },

        hideDropdown() {
            this.$refs['dropdown'].hide()
        }
    }
}
</script>
