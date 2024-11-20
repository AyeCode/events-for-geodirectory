(function ($, Widget) {
    'use strict';

    function geodir_event_widget_post_type_changed(el) {
        var $el, $block, $category, post_type;
        
        $el	= $(el);
        $block = $el.closest('.sd-shortcode-settings').length ? $el.closest('.sd-shortcode-settings') : $el.closest('.widget-inside');
        if (! $('form#gd_listings', $block).length && $('[name="id_base"]', $block).val() != 'gd_listings' && ! $('form#gd_linked_posts', $block).length && $('[name="id_base"]', $block).val() != 'gd_linked_posts') {
            return;
        }
        $category = $('[data-argument="category"]', $block).find('select');
        $sort_by = $('[data-argument="sort_by"]', $block).find('select');
        post_type = $el.val();
    
        if (!post_type) {
            return;
        }
    
        var data = {
            action: 'geodir_widget_post_type_field_options',
            geodir_nonce: Widget.nonce,
            post_type: post_type,
        };
    
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: data,
            beforeSend: function() {
                $category.css({
                    opacity: 0.5
                });
                $sort_by.css({
                    opacity: 0.5
                });
            },
            success: function(res, textStatus, xhr) {
                data = res && typeof res == 'object' && typeof res.data != 'undefined' ? res.data : '';
    
                if (data && typeof data == 'object') {
                    if (typeof data.category != 'undefined' && typeof data.category.options != 'undefined') {
                        $category.html(data.category.options).trigger('change');
                    }
                    if (typeof data.sort_by != 'undefined' && typeof data.sort_by.options != 'undefined') {
                        $sort_by.html(data.sort_by.options).trigger('change');
                    }
                }
    
                $category.css({
                    opacity: 1
                });
                $sort_by.css({
                    opacity: 1
                });
    
                $('body').trigger('geodir_widget_post_type_field_options', data);
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log(errorThrown);
                $category.html('').trigger('change');
                $category.css({
                    opacity: 1
                });
                $sort_by.css({
                    opacity: 1
                });
            }
        });	
    }

    $(document).on('change', '[data-argument="post_type"] select', function(e) {
		geodir_event_widget_post_type_changed(this);
	});

})(jQuery, Geodir_Events_Widget);

