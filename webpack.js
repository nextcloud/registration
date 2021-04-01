const path = require('path')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {
	settings: path.join(__dirname, 'src', 'settings'),
}

module.exports = webpackConfig
