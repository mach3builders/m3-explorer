<template>
  <div id="app">
    <m3-explorer :settings="settings" />
  </div>
</template>

<script>
import M3Explorer from './components/M3Explorer.vue'

export default {
    name: 'app',
    components: {
        M3Explorer
    },
    data() {
        return {
            settings: {
                urls: {
                    loadData: 'src/data/load-data.json',
                    loadItemData: 'src/data/load-item-data.json',
                    addItem: 'src/data/add-item.json',
                    removeItem: 'src/data/remove-item.json',
                    renameItem: 'src/data/rename-item.json'
                }
            }
        }
    }
}
</script>

<style>
#app {
    align-items: center;
    display: flex;
    flex-direction: column;
    color: #333;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 14px;
    line-height: 2;
    margin-top: 40px;
    text-align: center;
}

h2 {
    font-size: 16px;
}

hr {
    border: 0;
    border-top: 1px solid #ddd;
}

.content {
    width: 500px;
}
</style>
