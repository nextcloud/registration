const path = require('path')
const { merge } = require('webpack-merge')
const webpackConfig = require('@nextcloud/webpack-vue-config')

webpackConfig.entry = {}
const config = {
	entry: {
		settings: path.join(__dirname, 'src', 'settings'),
	},
}

module.exports = merge(config, webpackConfig)
