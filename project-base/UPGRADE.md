# Upgrading

## From 2.0.0-beta.16.0 to Unreleased
- coding standards for JS files were added, make sure `phing eslint-check` passes
(you can run `phing eslint-fix` to fix some violations automatically)
- HTML structure of forms in administration was simplified and does not use `<div>` tags with `form-line__label`, `form-line__item` CSS classes anymore.
    Use `<dl>`, `<dt>` and `<dd>` tags instead.

    You should change all your admin form HTML from structure like this:
    ```twig
    <div class="form-line">
        <div class="form-line__label">
            {{ 'Registration date'|trans }}:
        </div>
        <div class="form-line__side">
            <div class="form-line__item form-line__item--text">
                {{ user.createdAt|formatDateTime }}
            </div>
        </div>
    </div>
    ```
    into this simplified structure:
    ```twig
    <dl class="form-line">
        <dt>
            {{ 'Registration date'|trans }}:
        </dt>
        <dd>
            {{ user.createdAt|formatDateTime }}
        </dd>
    </dl>
    ```

## From 2.0.0-beta.15.0 to 2.0.0-beta.16.0
- all implementations of `Shopsys\ProductFeed\FeedItemRepositoryInterface` must implement interface `Shopsys\ShopBundle\Model\Feed\FeedItemRepositoryInterface` instead
    - the interface was moved from [shopsys/product-feed-interface](https://github.com/shopsys/product-feed-interface/) to core
- parameter `email_for_error_reporting` was renamed to `error_reporting_email_to` in `app/config/parameter.yml.dist`,
you will be prompted to fill it out again during `composer install`
- all implementations of `StandardFeedItemInterface` must implement methods `isSellingDenied()` and `getCurrencyCode()`, see [product-feed-interface](https://github.com/shopsys/product-feed-interface/blob/master/UPGRADE.md#from-030-to-040)