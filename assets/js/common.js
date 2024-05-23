jQuery(function($) {
    if ($('[name="event_dates[start_date]"]').length) {
        GeoDir_Event_Add.init($('[name="event_dates[start_date]"]').closest('form'));
    }
});
var GeoDir_Event_Add = {
    init: function($form) {
        var $self = this;
        this.$form = $form;

        jQuery('[name="event_dates[start_date]"]', $form).datepicker({
            changeMonth: true,
            changeYear: true,
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
            changeMonth: true,
            changeYear: true,
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
                changeMonth: true,
                changeYear: true,
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
            $self.hideFields(['start_date', 'end_date', 'repeat_days', 'repeat_weeks', 'repeat_x', 'repeat_end_type']);
            $self.showFields(['duration_x', 'recurring_dates', 'different_times', 'all_day']);
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
				var sdate = spdates[i];
                var $el = jQuery('.show_different_times_div .event-multiple-times[data-date="' + sdate + '"]', $self.form);
				if ($el.length) {
					$el.find('.geodir-select').removeClass('enhanced').removeClass('select2-hidden-accessible');
					$el.find('.select2').remove();
					date_selected = $el.html();
				} else {
					var adate = sdate.split("-");
					if (adate.length == 3) {
						var odate = new Date(parseInt(adate[0]), parseInt(adate[1]) - 1, parseInt(adate[2]));
					} else {
						var odate = new Date(sdate);
					}
					date_selected = '<label class="event-multiple-dateto">' + $self.dateFormat( odate ) + '</label><div class="event-multiple-dateto-inner"><select id="event_start_times" name="event_dates[start_times][]" class="geodir_textfield geodir-select geodir-w110">' + start_time_options + '</select></div><label class="event-multiple-end"> ' + geodir_event_params.text_to + ' </label><div class="event-multiple-dateto-inner"><select id="event_end_times" name="event_dates[end_times][]" class="geodir_textfield geodir-select geodir-w110">' + end_time_options + '</select></div>';
				}
				dates_selected += '<div data-date="' + sdate + '" class="event-multiple-times clearfix">' + date_selected + '</div>';
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
					start_weekday: geodir_event_params.week_start_day,
					MONTHS_LONG: [cal_trans.month_long_1, cal_trans.month_long_2, cal_trans.month_long_3, cal_trans.month_long_4, cal_trans.month_long_5, cal_trans.month_long_6, cal_trans.month_long_7, cal_trans.month_long_8, cal_trans.month_long_9, cal_trans.month_long_10, cal_trans.month_long_11, cal_trans.month_long_12],
					MONTHS_SHORT: [cal_trans.month_s_1, cal_trans.month_s_2, cal_trans.month_s_3, cal_trans.month_s_4, cal_trans.month_s_5, cal_trans.month_s_6, cal_trans.month_s_7, cal_trans.month_s_8, cal_trans.month_s_9, cal_trans.month_s_10, cal_trans.month_s_11, cal_trans.month_s_12],
					WEEKDAYS_1CHAR: [cal_trans.day_s1_1, cal_trans.day_s1_2, cal_trans.day_s1_3, cal_trans.day_s1_4, cal_trans.day_s1_5, cal_trans.day_s1_6, cal_trans.day_s1_7],
					WEEKDAYS_LONG: [cal_trans.day_s5_1, cal_trans.day_s5_2, cal_trans.day_s5_3, cal_trans.day_s5_4, cal_trans.day_s5_5, cal_trans.day_s5_6],
					WEEKDAYS_MEDIUM: [cal_trans.day_s3_1, cal_trans.day_s3_2, cal_trans.day_s3_3, cal_trans.day_s3_4, cal_trans.day_s3_5, cal_trans.day_s3_6, cal_trans.day_s3_7],
					WEEKDAYS_SHORT: [cal_trans.day_s2_1, cal_trans.day_s2_2, cal_trans.day_s2_3, cal_trans.day_s2_4, cal_trans.day_s2_5, cal_trans.day_s2_6, cal_trans.day_s2_7],
					STRINGS: {
						previousMonth: cal_trans.s_previousMonth,
						nextMonth: cal_trans.s_nextMonth,
						close: cal_trans.s_close
					},
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
                        dateLabels += '<span data-date="' + dateValue + '">' + dateLabel + '</span>';
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
    dateFormat: function(date) {
        var date_format = geodir_event_params.input_date_format, month = date.getMonth(), day = date.getDate();
        monthsArray = eval(geodir_event_params.monthsArray);
        date_format = date_format.replace("Y", date.getFullYear());
        date_format = date_format.replace("m", ("0" + (month + 1)).slice(-2));
        date_format = date_format.replace("d", ("0" + day).slice(-2));
        date_format = date_format.replace("j", day);
        date_format = date_format.replace("F", monthsArray[month]);
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
    }
}