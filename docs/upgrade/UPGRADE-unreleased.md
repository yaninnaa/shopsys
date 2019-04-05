# [Upgrade from v7.1.0 to Unreleased]

This guide contains instructions to upgrade from version v7.1.0 to Unreleased.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/project-base]
### Configuration
- translations are now extracted also from overwritten templates in `app/Resources` ([#931](https://github.com/shopsys/shopsys/pull/931))
    - update your `build-dev.xml` file accordingly:
        ```diff
            <target name="dump-translations-project-base" description="Extracts translatable messages from all source files in project base.">
                <exec executable="${path.php.executable}" passthru="true" checkreturn="true">
                    <arg value="${path.bin-console}" />
                    <arg value="translation:extract" />
                    <arg value="--bundle=ShopsysShopBundle" />
                    <arg value="--dir=${path.src}/Shopsys/ShopBundle" />
        +           <arg value="--dir=${path.app}/Resources" />
                    <arg value="--exclude-dir=frontend/plugins" />
                    <arg value="--output-format=po" />
                    <arg value="--output-dir=${path.src}/Shopsys/ShopBundle/Resources/translations" />
                    <arg value="--keep" />
                    <arg value="cs" />
                    <arg value="en" />
                </exec>
            </target>
        ```

[Upgrade from v7.1.0 to Unreleased]: https://github.com/shopsys/shopsys/compare/v7.1.0...HEAD
