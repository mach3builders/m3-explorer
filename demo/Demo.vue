<template>
    <div id="app">
        <m3-explorer :settings="settings" />
    </div>
</template>

<script>
import M3Explorer from '../M3Explorer'

export default {
    components: {
        M3Explorer,
    },

    data() {
        return {
            settings: {
                urls: {
                    loadData: 'src/data/explorer-load-data.json',
                    loadItemData: 'src/data/media-load-data.php',
                    addItem: 'src/data/explorer-add-item.php',
                    removeItem: 'src/data/explorer-remove-item.php',
                    renameItem: 'src/data/explorer-rename-item.php'
                }
            }
        }
    }
}
</script>
