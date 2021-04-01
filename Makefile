# Makefile for building the project

app_name=registration

project_dir=$(CURDIR)/../$(app_name)
build_dir=$(CURDIR)/build/artifacts
appstore_dir=$(build_dir)/appstore
source_dir=$(build_dir)/source
sign_dir=$(build_dir)/sign
package_name=$(app_name)
cert_dir=$(HOME)/.nextcloud/certificates
version+=master

all: appstore

release: appstore create-tag

create-tag:
	git tag -a v$(version) -m "Tagging the $(version) release."
	git push origin v$(version)

appstore:
	rm -rf $(build_dir)
	mkdir -p $(sign_dir)
	rsync -a \
	--exclude=appinfo/screenshot.png \
	--exclude=AUTHORS.md \
	--exclude=babel.config.js \
	--exclude=/build \
	--exclude=composer.json \
	--exclude=composer.lock \
	--exclude=docs \
	--exclude=.drone.yml \
	--exclude=.eslintignore \
	--exclude=.eslintrc.js \
	--exclude=.git \
	--exclude=.gitattributes \
	--exclude=.github \
	--exclude=.gitignore \
	--exclude=issue_template.md \
	--exclude=jest.config.js \
	--exclude=.l10nignore \
	--exclude=mkdocs.yml \
	--exclude=Makefile \
	--exclude=node_modules \
	--exclude=package.json \
	--exclude=package-lock.json \
	--exclude=.php_cs.dist \
	--exclude=.php_cs.cache \
	--exclude=README.md \
	--exclude=src \
	--exclude=.stylelintignore \
	--exclude=stylelint.config.js \
	--exclude=.travis.yml \
	--exclude=.tx \
	--exclude=TESTING.md \
	--exclude=tests \
	--exclude=vendor \
	--exclude=webpack.*.js \
	--exclude=webpack.js \
	$(project_dir)/  $(sign_dir)/$(app_name)
	@if [ -f $(cert_dir)/$(app_name).key ]; then \
		echo "Signing app files…"; \
		php ../../occ integrity:sign-app \
			--privateKey=$(cert_dir)/$(app_name).key\
			--certificate=$(cert_dir)/$(app_name).crt\
			--path=$(sign_dir)/$(app_name); \
	fi
	tar -czf $(build_dir)/$(app_name)-$(version).tar.gz \
		-C $(sign_dir) $(app_name)
	@if [ -f $(cert_dir)/$(app_name).key ]; then \
		echo "Signing package…"; \
		openssl dgst -sha512 -sign $(cert_dir)/$(app_name).key $(build_dir)/$(app_name)-$(version).tar.gz | openssl base64; \
	fi
