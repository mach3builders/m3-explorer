let mix = require('laravel-mix')
mix
.js('demo/demo.js', 'demo/app.js')
.sass('demo/demo.scss', 'demo/app.css')
