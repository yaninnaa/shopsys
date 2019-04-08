# [Upgrade from v7.1.0 to Unreleased]

This guide contains instructions to upgrade from version v7.1.0 to Unreleased.

**Before you start, don't forget to take a look at [general instructions](/UPGRADE.md) about upgrading.**
There you can find links to upgrade notes for other versions too.

## [shopsys/framework]
### Application
- *(low priority)* reconfigure fm_elfinder to use main_filesystem ([#932](https://github.com/shopsys/shopsys/pull/932))
    - upgrade version of `helios-ag/fm-elfinder-bundle` to `^9.2` in `composer.json`
    - update `fm_elfinder.yml` config
    ```diff
        driver: Flysystem
    -   path: '%shopsys.filemanager_upload_web_dir%'
    +   path: 'web/%shopsys.filemanager_upload_web_dir%'
        flysystem:
    -       type: local
    -       options:
    -           local:
    -               path: '%shopsys.web_dir%'
    +       enabled: true
    +       filesystem: 'main_filesystem'
        upload_allow: ['image/png', 'image/jpg', 'image/jpeg']
    -   tmb_path: '%shopsys.filemanager_upload_web_dir%/_thumbnails'
    +   tmb_path: 'web/%shopsys.filemanager_upload_web_dir%/_thumbnails'
        url: '%shopsys.filemanager_upload_web_dir%'
        tmb_url: '%shopsys.filemanager_upload_web_dir%/_thumbnails'
        attributes:
            thumbnails:
    -           pattern: '/^\/content\/wysiwyg\/_thumbnails$/'
    +           pattern: '/^\/web\/content\/wysiwyg\/_thumbnails$/'
                hidden: true
    ```
    - download [`app/elFinderVolumeFlysystem.php`](https://github.com/shopsys/project-base/blob/master/app/elFinderVolumeFlysystem.php)
    - download the whole folder [`src/Shopsys/ShopBundle/Component/Flysystem`](https://github.com/shopsys/project-base/tree/master/src/Shopsys/ShopBundle/Component/Flysystem)
    - add `app/elFinderVolumeFlysystem.php` into classmap of `composer.json`
    - read the section about proxying the URL content subpats via webserver domain [`docs/introduction/abstract-filesystem.md`](https://github.com/shopsys/shopsys/blob/master/docs/introduction/abstract-filesystem.md)

[Upgrade from v7.1.0 to Unreleased]: https://github.com/shopsys/shopsys/compare/v7.1.0...HEAD
[shopsys/framework]: https://github.com/shopsys/framework
