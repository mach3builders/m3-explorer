import Demo from './Demo.vue'

document.addEventListener('DOMContentLoaded', () => {
    new Vue({
    render: h => h(Demo),
        data() {
            return {
                eventHub: new Vue()
            }
        },
        mounted() {
            document.addEventListener('click', () => {
                this.eventHub.$emit('document:clicked')
            })
        }
    }).$mount('#app')
})
