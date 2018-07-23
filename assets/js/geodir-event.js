jQuery(function($) {
    if ($('[name="event_dates[start_date]"]').length) {
        GeoDir_Event_Add.init($('[name="event_dates[start_date]"]').closest('form'));
    }
	if ($('select[name="link_business"]').length) {
        GeoDir_Event_Add.initLinkBusiness($('select[name="link_business"]').closest('form'));
    }
});
var GeoDir_Event_Add = {
    init: function($form) {
        var $self = this;
        this.$form = $form;

        jQuery('[name="event_dates[start_date]"]', $form).datepicker({
            dateFormat: geodir_event_params.jqueryui_date_format,
            minDate: 0,
            onClose: function(selectedDate) {
                if (jQuery('[name="event_dates[repeat_end]"]', $form).length) {
                    jQuery('[name="event_dates[repeat_end]"]', $form).datepicker("option", "minDate", selectedDate);
                }
                jQuery('[name="event_dates[end_date]"]', $form).datepicker("option", "minDate", selectedDate);
            }
        });
        jQuery('[name="event_dates[end_date]"]', $form).datepicker({
            dateFormat: geodir_event_params.jqueryui_date_format,
            minDate: 0,
            onClose: function(selectedDate) {
                var esDate = jQuery('[name="event_dates[start_date]"]', $form).val();
                if (esDate) {
                    if (jQuery('[name="event_dates[repeat_end]"]', $form).length) {
                        jQuery('[name="event_dates[repeat_end]"]', $form).datepicker("option", "minDate", esDate);
                    }
                    jQuery('[name="event_dates[end_date]"]', $form).datepicker("option", "minDate", esDate);
                }
            }
        });
        if (jQuery('[name="event_dates[repeat_end]"]', $form).length) {
            jQuery('[name="event_dates[repeat_end]"]', $form).datepicker({
                dateFormat: geodir_event_params.jqueryui_date_format,
                minDate: 0,
                onClose: function(selectedDate) {
                    if (jQuery('[name="event_dates[start_date]"]', $form).val()) {
                        jQuery('[name="event_dates[repeat_end]"]', $form).datepicker("option", "minDate", jQuery('[name="event_dates[start_date]"]', $form).val());
                    }
                }
            });
        }

        jQuery('[name="recurring"]', $form).on('click', function(e) {
            $self.onChangeRecurring(jQuery(this));
        });
        jQuery('[name="recurring"]:checked', $form).trigger('click');
        jQuery('[name="event_dates[repeat_type]"]', $form).on('change', function(e) {
            $self.onChangeRepeatType(jQuery(this));
        });
        jQuery('#event_all_day_chk', $form).on('change', function(e) {
            $self.onChangeAllDay(jQuery('[name="event_dates[all_day]"]', $form));
        });
        jQuery('#event_different_times_chk', $form).on('change', function(e) {
            $self.onChangeDifferentTimes(jQuery('[name="event_dates[different_times]"]', $form));
        });
        if (jQuery('[name="event_dates[recurring_dates]"]', $form).length) {
            $self.loadYUICalendar();
        }
    },
    onChangeRecurring: function($el) {
        var $self = this;

        if (parseInt($el.val()) === 1) {
            $self.showFields(['duration_x', 'repeat_type', 'repeat_x', 'repeat_end_type']);
            $self.hideFields(['end_date']);
            $self.onChangeRepeatType(jQuery('[name="event_dates[repeat_type]"]', $self.form));
        } else {
            $self.hideFields(['duration_x', 'repeat_type', 'repeat_x', 'repeat_end_type', 'repeat_days', 'repeat_weeks', 'recurring_dates']);
            jQuery('#geodir_event_times_row', $self.form).addClass('geodir-none');
            $self.showFields(['start_date', 'end_date', 'all_day']);
            if (parseInt(jQuery('[name="event_dates[all_day]"]', $self.form).val()) === 1) {
                $self.hideFields(['start_time']);
            } else {
                $self.showFields(['start_time']);
            }
        }
    },
    onChangeRepeatType: function($el) {
        var $self = this;
        var $times = jQuery('#geodir_event_times_row', $self.form);
        var $all_day = jQuery('[name="event_dates[all_day]"]', $self.form);
        var repeat_type = $el.val();

        if (repeat_type == 'custom') {
            $self.hideFields(['start_date', 'end_date', 'duration_x', 'repeat_days', 'repeat_weeks', 'repeat_x', 'repeat_end_type']);
            $self.showFields(['recurring_dates', 'different_times', 'all_day']);
            if (parseInt($all_day.val()) === 1) {
                $self.hideFields(['start_time']);
            } else {
                $self.showFields(['start_time']);
            }
            $times.removeClass('geodir-none');
        } else if (repeat_type == 'year' || repeat_type == 'month' || repeat_type == 'week' || repeat_type == 'day') {
            $self.showFields(['duration_x', 'repeat_x', 'repeat_end_type', 'start_date', 'all_day']);

            if (parseInt($all_day.val()) === 1) {
                $self.hideFields(['start_time']);
            } else {
                $self.showFields(['start_time']);
            }

            if (repeat_type == 'month' || repeat_type == 'week') {
                $self.showFields(['repeat_days', 'repeat_weeks']);
                if (repeat_type == 'week') {
                    $self.hideFields(['repeat_weeks']);
                }
            } else {
                $self.hideFields(['repeat_days', 'repeat_weeks']);
            }

            $self.hideFields(['recurring_dates', 'different_times']);
            $times.addClass('geodir-none');
        } else {
            $self.hideFields(['repeat_x', 'repeat_end_type', 'recurring_dates', 'all_day', 'start_time', 'different_times']);
            $times.addClass('geodir-none');
        }
        if (repeat_type != '') {
            $self.onChangeAllDay($all_day);
        }
    },
    onChangeAllDay: function($el) {
        var $self = this;
        var $times = jQuery('#geodir_event_times_row', $self.form);

        if (jQuery('[name="event_dates[repeat_type]"]', $self.form).val() == 'custom') {
            $self.showFields(['different_times']);
            if (parseInt(jQuery('[name="event_dates[different_times]"]', $self.form).val()) === 1) {
				$times.removeClass('geodir-none');
			} else {
				$times.addClass('geodir-none');
			}
        } else {
            $self.hideFields(['different_times']);
            $times.addClass('geodir-none');
        }
        if (parseInt($el.val()) === 1) {
            $self.hideFields(['start_time', 'different_times']);
            $times.addClass('geodir-none');
        } else {
            $self.showFields(['start_time']);
        }
    },
    onChangeDifferentTimes: function($el) {
        var $self = this;
        var $times = jQuery('#geodir_event_times_row', $self.form);

        if (parseInt($el.val()) === 1) {
            $times.removeClass('geodir-none');
        } else {
            $times.addClass('geodir-none');
        }
        var event_dates = jQuery('[name="event_dates[recurring_dates]"]', $self.form).val();
        if (event_dates != '') {
            var spdates = event_dates.split(",");
            var total_dates = spdates.length;
            var dates_selected = '';
            var start_time_options = jQuery('#geodir_event_start_time_options', $form).html();
            var end_time_options = jQuery('#geodir_event_end_time_options', $form).html();
            for (i = 0; i < total_dates; i++) {
                dates_selected += '<div class="event-multiple-times clearfix"><label class="event-multiple-dateto">' + spdates[i] + '</label><div class="event-multiple-dateto-inner"><select id="event_start_times" name="event_dates[start_times][]" class="geodir_textfield geodir-select geodir-w110">' + start_time_options + '</select></div><label class="event-multiple-end"> ' + geodir_event_params.text_to + ' </label><div class="event-multiple-dateto-inner"><select id="event_end_times" name="event_dates[end_times][]" class="geodir_textfield geodir-select geodir-w110">' + end_time_options + '</select></div></div>';
            }
            jQuery('.show_different_times_div', $self.form).html(dates_selected);
            jQuery('.show_different_times_div', $self.form).find('.geodir-select').each(function() {
                jQuery(this).trigger('geodir-select-init');
            });
        } else {
            jQuery('.show_different_times_div').html('');
        }
    },
    loadYUICalendar: function() {
        $self = this;
        $form = $self.form;
        $dates = jQuery('[name="event_dates[recurring_dates]"]', $form);
        $err = $dates.closest('.required_field').find('.geodir_message_error');

        try {
            gdEventMultiCal = function() {
                geodirCalParams = {
                    pages: 2,
                    MULTI_SELECT: true,
                    start_weekday: geodir_event_params.week_start_day
                };
                geodirSetDates = jQuery('#geodir_event_default_dates', $form).text();
                if (geodirSetDates != '') {
                    geodirCalParams.selected = geodirSetDates;
                }
                geodirCalGroup = new YAHOO.widget.CalendarGroup("multiCal1", "geodir_event_multi_dates_cal", geodirCalParams);
                geodirCalGroup.render();
                var geodirCalUpdated = function() {
                    var date, m, d, dateValue, dateLabel;
                    var selectedDates = geodirCalGroup.getSelectedDates();
                    var dateLabels = "";
                    var dateValues = "";
                    for (var ii = 0; ii < selectedDates.length; ii++) {
                        date = selectedDates[ii];
                        y = date.getFullYear();
                        m = ("0" + (date.getMonth() + 1)).slice(-2);
                        d = ("0" + date.getDate()).slice(-2);
                        dateValue = y + "-" + m + "-" + d;
                        dateLabel = $self.dateFormat(date);
                        if (ii > 0) {
                            dateValues += ",";
                        }
                        dateValues += dateValue;
                        dateLabels += "<span>" + dateLabel + "</span>";
                    }
                    if (dateLabels == '') {
                        jQuery('#geodir_event_selected_dates_row', $form).hide();
                        if ($err.length) {
                            $err.removeClass('geodir-none');
                            $err.html(error_message);
                        }
                    } else {
                        jQuery('#geodir_event_selected_dates_row', $form).show();
                        $err.addClass('geodir-none');
                    }
                    jQuery('#geodir_event_selected_dates', $form).html(dateLabels);
                    $dates.val(dateValues);
                    jQuery('#event_different_times_chk', $form).trigger('change');
                    calChanged(geodirCalGroup);
                }
                geodirCalGroup.selectEvent.subscribe(geodirCalUpdated, geodirCalGroup, true);
                geodirCalGroup.deselectEvent.subscribe(geodirCalUpdated, geodirCalGroup, true);
            }
            YAHOO.util.Event.onDOMReady(gdEventMultiCal);
        } catch (e) {
            console.log(e);
        }
    },
    dateFormat(date) {
        var date_format = geodir_event_params.input_date_format;
        monthsArray = eval(geodir_event_params.monthsArray);
        date_format = date_format.replace("Y", date.getFullYear());
        date_format = date_format.replace("m", (date.getMonth() + 1));
        date_format = date_format.replace("d", date.getDate());
        date_format = date_format.replace("j", date.getDate());
        date_format = date_format.replace("F", monthsArray[date.getMonth()]);
        return date_format;
    },
    showFields: function(fields) {
        var $self = this;
        jQuery.each(fields, function(i, field) {
            name = 'event_dates[' + field + ']';
            if (field == 'repeat_days' || field == 'repeat_weeks') {
                name += '[]';
            }
            jQuery('[name="' + name + '"]', $self.form).closest('.geodir_form_row').removeClass('geodir-none');
        });
    },
    hideFields: function(fields) {
        var $self = this;
        jQuery.each(fields, function(i, field) {
            name = 'event_dates[' + field + ']';
            if (field == 'repeat_days' || field == 'repeat_weeks') {
                name += '[]';
            }
            jQuery('[name="' + name + '"]', $self.form).closest('.geodir_form_row').addClass('geodir-none');
        });
    },
	initLinkBusiness: function($form) {
        var $self = this;
        this.$form = $form;

		jQuery('.geodir-fill-business', $form).on('click', function(e) {
            $wrap = jQuery(this).closest('.geodir_form_row');
			id = parseInt(jQuery('[name="link_business"]', $wrap).val());
			if (id > 0) {
				$self.onFillBusiness(id, jQuery(this));
			} else {
				jQuery('[name="link_business"]', $wrap).focus();
			}
        });
    },
	onFillBusiness: function(id, $el) {
	    if ( !id ) {
			return false;
		}
		$wrap = $el.closest('#geodir_wrapper');
		$form = $el.closest('form');

	    var data = {
	        action: 'geodir_event_fill_business',
	        id: id,
	        security: $el.data('nonce')
	    }
	    jQuery.ajax({
	        url: geodir_params.ajax_url,
	        type: 'POST',
	        dataType: 'json',
	        data: data,
	        beforeSend: function() {
	            $el.prop('disabled', true);
				$wrap.css({
	                opacity: 0.6
	            });
	        },
	        success: function(res, textStatus, xhr) {
	            console.log(res);
				if (res.data && res.data.message) {
	                alert(res.data.message);
	            }
	            if (res.success && res.data && res.data.fields) {
					console.log(res.data.fields);
					var address = false;
					fields = res.data.fields;
					jQuery.each(fields, function(i, item) {console.log(i);
						if (item.key == 'text') {
							if (item.value == false) item.value = '';
							jQuery('input[name="' + i + '"]', $form).val(item.value);
						}
						if (i == 'post_title') {
							jQuery('#title', $form).focus();
						}
						if (item.key == 'textarea') {
							if (item.value == false) item.value = '';
							jQuery('[name="' + i + '"]').val(item.value);
							if (i == 'post_content') {
								jQuery('[name="content"]').val(item.value);
							}
							if (typeof tinymce != 'undefined') {
								if (tinyMCE.get('content') && i == 'post_content') {
									i = 'content';
								}
								if (tinymce.editors.length > 0 && tinyMCE.get(i)) tinyMCE.get(i).setContent(item.value);
							}
						}
						if (i == 'street') address = true;
						if (i == 'city' || i == 'region' || i == 'country') {
							if (jQuery('[name="' + i + '"] option:contains("' + item.value + '")').length == 0) {
								jQuery('[name="' + i + '"]').append('<option value="' + item.value + '">' + item.value + '</option>');
							}
							jQuery('[name="' + i + '"]').val(item.value).trigger("change.select2");
						}
						if (item.key == 'checkbox') {
							var value = parseInt(item.value) > 0 ? 1 : 0;
							jQuery('input[name="' + i + '"]').val(value);
							var Ele = jQuery('input[name="' + i + '"][value="' + value + '"]');
							if (jQuery(Ele).prop('type') != 'checkbox') {
								jQuery(Ele).closest('.geodir_form_row').find('input[type="checkbox"]').prop('checked', value);
							}
						}
						if (item.key == 'radio') {
							var value = item.value == false ? '' : item.value;
							jQuery('input[name="' + i + '"][value="' + value + '"]').prop('checked', true);
						}
						if (item.key == 'select') {
							var value = item.value == false ? '' : item.value;
							jQuery('select[name="' + i + '"]').val(value).trigger("change.select2");
						}
						if (item.key == 'multiselect') {
							var value = item.value == false ? '' : item.value;
							var field_type = jQuery('[name="' + i + '[]"]').prop('type');
							switch (field_type) {
								case 'checkbox':
								case 'radio':
									jQuery('input[name="' + i + '[]"]').val(value);
									value = typeof value == 'object' && value ? value[0] : '';
									if (field_type == 'radio' && value != '') {
										jQuery('input[name="' + i + '[]"][value="' + value + '"]').prop('checked', true);
									}
									break;
								default:
									jQuery('select[name="' + i + '[]"]').val(value).trigger("change.select2");
									break;
							}
						}
						if (item.key == 'datepicker' && item.value && item.value != '') {
							jQuery('input[name="' + i + '"]').datepicker('setDate', item.value);
						}
						if (item.key == 'time' && item.value && item.value != '') {
							jQuery('input[name="' + i + '"]').timepicker('setTime', new Date("January 1, 2015 " + item.value));
						}
						if (item.key == 'tags' && item.value && item.value != '') {
							jQuery('input[name="post_tags"]').val(item.value);
							jQuery('input[name="newtag[gd_event_tags]"]').val(item.value);
						}
					});
					if ((window.gdMaps == 'google' || window.gdMaps == 'osm') && typeof baseMarker != 'undefined' && (latitude = jQuery('[name="latitude"]').val()) && (longitude = jQuery('[name="longitude"]').val())) {
						if (window.gdMaps == 'google') {
							baseMarker.setPosition(new google.maps.LatLng(latitude, longitude));
						} else if (window.gdMaps == 'osm') {
							baseMarker.setLatLng(new L.latLng(latitude, longitude));
						}
						centerMap();
					}
	            }
				$el.prop('disabled', false);
				$wrap.css({
	                opacity: 1
	            });
	        },
	        error: function(xhr, textStatus, errorThrown) {
	            console.log(errorThrown);
	            $el.prop('disabled', false);
				$wrap.css({
	                opacity: 1
	            });
	        }
	    });
	}
}