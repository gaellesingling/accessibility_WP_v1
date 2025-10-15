(function ($) {
    'use strict';

    function getLayoutInput($container) {
        var layoutSelector = $container.data('layoutInput');

        if (layoutSelector) {
            var $target = $(layoutSelector);

            if ($target.length) {
                return $target;
            }
        }

        return $container.children('.a11y-widget-admin-layout');
    }

    function updateSection($container) {
        var slugs = [];

        $container.children('.a11y-widget-admin-feature').each(function () {
            var slug = $(this).data('featureSlug');

            if (slug) {
                slugs.push(slug);
            }
        });

        var $input = getLayoutInput($container);
        if ($input.length) {
            $input.val(slugs.join(','));
        }

        var $empty = $container.children('.a11y-widget-admin-section__empty-message');
        if ($empty.length) {
            if (slugs.length) {
                $empty.attr('hidden', 'hidden');
            } else {
                $empty.removeAttr('hidden');
            }
        }
    }

    function refreshAll($containers) {
        $containers.each(function () {
            updateSection($(this));
        });
    }

    $(function () {
        var $containers = $('.a11y-widget-admin-section__content');

        if (!$containers.length || !$.fn.sortable) {
            return;
        }

        $containers.sortable({
            items: '.a11y-widget-admin-feature',
            connectWith: '.a11y-widget-admin-section__content',
            handle: '.a11y-widget-admin-feature__handle',
            placeholder: 'a11y-widget-admin-feature a11y-widget-admin-feature--placeholder',
            tolerance: 'pointer',
            forcePlaceholderSize: true,
            cancel: '.a11y-widget-admin-toggle, .a11y-widget-switch, .a11y-widget-switch *',
            start: function (event, ui) {
                ui.item.addClass('a11y-widget-admin-feature--dragging');
            },
            stop: function (event, ui) {
                ui.item.removeClass('a11y-widget-admin-feature--dragging');
                refreshAll($containers);
            },
            update: function () {
                refreshAll($containers);
            },
            receive: function () {
                refreshAll($containers);
            }
        });

        refreshAll($containers);

        $containers.closest('form').on('submit', function () {
            refreshAll($containers);
        });
    });
})(jQuery);
