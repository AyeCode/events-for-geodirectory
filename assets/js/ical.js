(function ($, iCal) {
    'use strict';

    iCal.Modal = {};
    iCal.Clipboard = {};

    const CONFIG = {
        modalId: 'geodir-events-modal',
        defaultSize: 'medium',
        clipboard: {
            selector: '.clipboard-init',
            activeClass: 'active',
            successDuration: 2000
        }
    };

    iCal.Modal.init = function () {
        if (!$(`#${CONFIG.modalId}`).length) {
            const modalHTML = `
                <div id="${CONFIG.modalId}" class="modal fade" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHTML);
        }

        this.$modal = $(`#${CONFIG.modalId}`);
        this.$modalContent = this.$modal.find('.modal-content');

        this.bindEvents();
    };

    iCal.Modal.bindEvents = function () {
        $(document).on('click', '[data-bs-toggle="geodir-modal"]', this.handleTriggerClick.bind(this));
        this.$modal.on('show.bs.modal', () => $(document).trigger('geodirModalBeforeShow'));
        this.$modal.on('shown.bs.modal', () => $(document).trigger('geodirModalAfterShow'));
        this.$modal.on('hide.bs.modal', () => $(document).trigger('geodirModalBeforeHide'));
        this.$modal.on('hidden.bs.modal', () => $(document).trigger('geodirModalAfterHide'));
    };

    // Handle trigger click
    iCal.Modal.handleTriggerClick = function (e) {
        e.preventDefault();
        const $trigger = $(e.currentTarget);

        if ($trigger.hasClass('disabled')) return;

        const config = {
            action: $trigger.data('action'),
            size: $trigger.data('size') || CONFIG.defaultSize,
            static: $trigger.data('static') || false
        };

        this.show(config);
    };

    // Show modal
    iCal.Modal.show = function (config) {
        $(document).trigger('geodirModalBeforeLoad', [config]);

        if (config.action.charAt(0) === '#') {
            this.showTemplateModal(config);
        }

        $(document).trigger('geodirModalAfterLoad', [config]);
    };

    // Show template modal
    iCal.Modal.showTemplateModal = function (config) {
        const $template = $(config.action);
        if (!$template.length) return;

        this.setSize(config.size);
        this.setOptions(config);

        this.$modalContent.html($template.html());
        this.$modal.modal('show');

        this.initializeModalContent();
    };

    // Set modal size
    iCal.Modal.setSize = function (size) {
        const $dialog = this.$modal.find('.modal-dialog');
        const sizes = {
            'small': 'modal-sm',
            'medium': 'modal-md',
            'large': 'modal-lg',
            'extra-large': 'modal-xl'
        };

        $dialog.removeClass('modal-sm modal-md modal-lg modal-xl').addClass(sizes[size] || '');
    };

    // Set modal options
    iCal.Modal.setOptions = function (config) {
        if (config.static) {
            this.$modal.data('bs.modal')._config.backdrop = 'static';
            this.$modal.data('bs.modal')._config.keyboard = false;
        }
    };

    // Initialize modal content
    iCal.Modal.initializeModalContent = function () {
        $(document).trigger('geodirModalContentInitialized', [this.$modalContent]);
        iCal.Clipboard.init();
    };

    iCal.Clipboard = {
        init: function () {
            $(document).on('click', CONFIG.clipboard.selector, this.handleClick.bind(this));
        },

        handleClick: function (e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const target = $btn.data('clipboard-target');
            const successText = $btn.data('clip-success');
            const defaultText = $btn.data('clip-text');
            const $clipboardText = $btn.find('.clipboard-text');

            // Create a range and select the target text
            const t = document.createRange();
            t.selectNode(document.querySelector(target));
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(t);

            // Copy the selected text
            try {
                document.execCommand('copy');

                // Update the button state
                $btn.addClass(CONFIG.clipboard.activeClass);
                $clipboardText.text(successText);

                // Reset button state after the success duration
                setTimeout(() => {
                    $btn.removeClass(CONFIG.clipboard.activeClass);
                    $clipboardText.text(defaultText);
                }, CONFIG.clipboard.successDuration);
            } catch (err) {
                console.error('Unable to copy text:', err);
            }

            // Deselect the text after copying
            window.getSelection().removeAllRanges();
        }
    };


    iCal.ImportStats = $.extend({}, {
        totalEl: null,
        succeedEl: null,
        skippedEl: null,
        failedEl: null,
        init: function (el, args) {
            this.element = el;
            this.totalEl = this.element.find('.geodir-events-total');
            this.succeedEl = this.element.find('.geodir-events-succeed');
            this.skippedEl = this.element.find('.geodir-events-skipped');
            this.failedEl = this.element.find('.geodir-events-failed');
            return this;

        },
        updateStats: function (data) {
            // Update process info
            this.totalEl.text(data.total);
            this.succeedEl.text(data.succeed);
            this.skippedEl.text(data.skipped);
            this.failedEl.text(data.failed);
        }
    });

    iCal.LogsHandler = $.extend({}, {
        shown: 0,

        init: function (el, args) {
            this.element = el;
            return this;
        },

        insertLogs: function (logs) {
            this.element.append(logs);
        },
        /**
         *
         * @param {int} count
         * @returns {undefined}
         */
        setShown: function (count) {
            this.shown = count;
        }
    });

    iCal.ProgressBar = $.extend({}, {
        barEl: null,
        textEl: null,
        init: function (el, args) {
            this.element = el;
            this.barEl = this.element.find('.geodir-events-progress__bar');
            this.textEl = this.element.find('.geodir-events-progress__text');
            return this;
        },
        updateProgress: function (newProgress) {
            this.barEl.css('width', newProgress + '%');
            this.textEl.text(newProgress + '%');
        }
    });

    iCal.Importer = {
        tickInterval: 2000,
        shortTickInterval: 500,
        retriesCount: 1,
        retriesLeft: 0,
        inProgress: false,
        updateTimeout: null,
        preventUpdates: false,
        init: function (el, args) {
            this.element = el;
            this.inProgress = args.inProgress;
            this.resetRetries();
            if (this.inProgress) {
                this.start();
            }

            return this;
        },
        start: function () {
            this.preventUpdates = false;
            this.updateTimeout = setTimeout(this.tick.bind(this), this.shortTickInterval);
        },
        requestUpdate: function () {
            this.preventUpdates = false;
            this.updateTimeout = setTimeout(this.tick.bind(this), this.tickInterval);
        },
        stop: function () {
            clearTimeout(this.updateTimeout);
            this.preventUpdates = true;
        },
        resetRetries: function () {
            this.retriesLeft = this.retriesCount;
        },
        markInProgress: function () {
            this.inProgress = true;
        },
        markStopped: function () {
            this.inProgress = false;
        },
        trigger: function (event) {
            if (this.hasOwnProperty(event)) {
                this[event]();
            }
            this.element.trigger(event);
        }
    };

    iCal.UploadImporter = $.extend({}, iCal.Importer, {
        ALLOWED_TYPES: ['.ics', '.ical', '.icalendar'],
        MAX_FILE_SIZE: null,
        queue_id: null,
        importer: null,
        uploadArea: null,
        uploadAreaImport: null,
        uploadAreaUrl: null,
        uploadProcess: null,
        fileContent: null,
        filePreview: null,
        fileInput: null,
        browseButton: null,
        submitButton: null,
        success: null,
        error: null,
        progressBar: null,
        logsHandler: null,
        importStats: null,

        /**
         * Initialize the module
         */
        init: function (el, args) {
            this._super = iCal.Importer.init;
            this._super(el, args);

            this.importer = this;

            this.MAX_FILE_SIZE = iCal.maxFileSize;
            this.progressBar = iCal.ProgressBar.init(this.element.find('.geodir-events-progress'));
            this.logsHandler = iCal.LogsHandler.init(this.element.find('.geodir-events-logs'));
            this.importStats = iCal.ImportStats.init(this.element.find('.geodir-events-import-stats'));
            this.uploadArea = this.element.find('.gdevents-upload-area');
            this.uploadAreaImport = this.element.find('.upload-area__import');
            this.uploadAreaUrl = this.element.find('.upload-area__url');
            this.uploadProcess = this.element.find('.upload-area__process');
            this.fileContent = this.element.find('.upload-area__content');
            this.filePreview = this.element.find('.upload-area__file');
            this.fileInput = this.element.find('.js_import-file');
            this.urlInput = this.element.find('[name="ical_url"]');
            this.browseButton = this.element.find('.js_browse-btn');
            this.submitButton = this.element.find('button[type="submit"]');
            this.success = this.element.find('.alert.alert-success');
            this.error = this.element.find('.alert.alert-danger');

            this.bindEvents();
        },

        /**
         * Performs AJAX request.
         * @param {string} action - Action to perform.
         * @param {function} callback - Callback function.
         * @param {Object} data - Data to send (optional).
         * @param {Object} atts - Additional parameters for $.ajax (optional).
         * @returns {jqXHR} - jQuery XMLHttpRequest object.
         */
        ajax: function (action, callback, data, atts) {
            atts = (typeof atts !== 'undefined') ? atts : {};
            data = (typeof data !== 'undefined') ? data : {};

            const nonce = iCal.nonces.hasOwnProperty(action) ? iCal.nonces[action] : '';
            if (data instanceof FormData) {
                data.append('action', action);
                data.append('geodir_nonce', nonce);

                atts.processData = false;
                atts.contentType = false;
            } else {
                data['action'] = action;
                data['geodir_nonce'] = nonce;
            }

            atts = $.extend(atts, {
                url: iCal.ajaxUrl,
                dataType: 'json',
                data: data,
                success: function (response, textStatus, jqXHR) {
                    var success = true === response.success;
                    var responseData = response.data || {};

                    callback(success, responseData);
                },
            });

            return $.ajax(atts);
        },

        tick: function () {
            var self = this;
            this.ajax(iCal.actions.ical.progress, function (success, data) {

                // Request failed?
                if (!success) {
                    if (self.retriesLeft > 0) {
                        self.retriesLeft--;
                        self.requestUpdate();
                    } else {
                        self.buttonStatus(self.submitButton, 'reset');
                    }
                    return;
                } else {
                    self.resetRetries();
                }

                data.isFinished ? self.markStopped() : self.markInProgress();

                self.importStats.updateStats(data);
                self.progressBar.updateProgress(data.progress);

                self.logsHandler.setShown(data.logsShown);
                self.logsHandler.insertLogs(data.logs);

                // Insert notice when finished
                if (data.notice) {
                    self.showSuccess(data.notice);
                }

                if (self.inProgress) {
                    self.requestUpdate();
                } else {
                    self.logsHandler.setShown(0);
                    self.buttonStatus(self.submitButton, 'reset');
                }

            }, { "logsShown": self.logsHandler.shown, "queue_id": self.queue_id });
        },

        /**
         * Bind event listeners
         */
        bindEvents: function () {
            const self = this;
            // Form submission
            this.element.on('submit', (e) => this.handleSubmit(e));

            // Upload area click
            this.uploadArea.on('click', () => this.handleAreaClick());

            // File input change
            this.fileInput.on('change', (e) => this.handleFileSelect(e));

            // Browse button click
            this.browseButton.on('click', (e) => {
                e.stopPropagation();
                self.fileInput.click();
            });

            // Remove file
            this.element.find('.remove-file').on('click', (e) => self.removeFile(e));

            // Drag and drop handlers
            this.uploadArea
                .on('dragover dragenter', (e) => self.handleDragOver(e))
                .on('dragleave dragend drop', (e) => self.handleDragLeave(e))
                .on('drop', (e) => self.handleDrop(e));
        },

        /**
         * Handle click on upload area
         */
        handleAreaClick: function () {
            if (!this.filePreview.hasClass('d-none')) {
                return;
            }
            this.fileInput.click();
        },

        /**
         * Handle dragover event
         * @param {Event} e 
         */
        handleDragOver: function (e) {
            e.preventDefault();
            e.stopPropagation();
            this.uploadArea.addClass('dragover');
        },

        /**
         * Handle dragleave event
         * @param {Event} e 
         */
        handleDragLeave: function (e) {
            e.preventDefault();
            e.stopPropagation();
            this.uploadArea.removeClass('dragover');
        },

        /**
         * Handle file drop
         * @param {Event} e 
         */
        handleDrop: function (e) {
            e.preventDefault();
            e.stopPropagation();
            this.uploadArea.removeClass('dragover');

            const files = e.originalEvent.dataTransfer.files;
            if (files.length) {
                this.handleFiles(files[0]);
            }
        },

        /**
         * Handle file selection
         * @param {Event} e 
         */
        handleFileSelect: function (e) {
            const file = e.target.files[0];
            if (file) {
                this.handleFiles(file);
            }
        },

        /**
         * Process the selected file
         * @param {File} file 
         */
        handleFiles: function (file) {
            // Validate file type
            const fileExt = '.' + file.name.split('.').pop().toLowerCase();
            if (!this.ALLOWED_TYPES.includes(fileExt)) {
                this.showError('Please upload an iCal file (.ics, .ical, .icalendar)');
                return;
            }

            // Validate file size
            if (file.size > this.MAX_FILE_SIZE) {
                this.showError(`File size exceeds the limit of ${this.formatBytes(this.MAX_FILE_SIZE)}`);
                return;
            }

            // Update UI
            this.fileContent.addClass('d-none');
            this.filePreview.removeClass('d-none');
            this.filePreview.find('.file-name').text(file.name);
            this.submitButton.prop('disabled', false);
            this.urlInput.prop('disabled', true);
            this.hideAlerts();
        },

        /**
         * Remove selected file
         * @param {Event} e 
         */
        removeFile: function (e) {
            e.stopPropagation();
            this.fileInput.val('');
            this.fileContent.removeClass('d-none');
            this.filePreview.addClass('d-none');
            this.urlInput.prop('disabled', false);
        },

        /**
         * Handle form submission
         * @param {Event} e 
         */
        handleSubmit: function (e) {
            e.preventDefault();
            this.hideAlerts();

            const { __ } = wp.i18n;
            const self = this;
            const file = this.fileInput[0].files[0];
            const icalUrl = this.urlInput.val();

            if (!file && !icalUrl) {
                this.showError(__('Please upload a file or provide an iCal URL', 'uwpm'));
                return;
            }

            // Validate iCal URL if provided
            if (icalUrl && !this.isValidUrl(icalUrl)) {
                this.showError(__('Please enter a valid iCal URL', 'uwpm'));
                return;
            }

            var formData = new FormData();

            if (file) {
                formData.append('import', file);
            } else {
                formData.append('url', icalUrl);
            }

            this.ajax(iCal.actions.ical.import, function (success, response) {
                self.uploadAreaImport.slideUp();
                self.uploadAreaUrl.slideUp();

                self.queue_id = response.queue_id;
                self.importer.start();
            }, formData, {
                method: 'POST',
                beforeSend: function () {
                    self.buttonStatus(self.submitButton, 'importing');
                    self.uploadProcess.removeClass('d-none').slideDown();
                },
                error: function (xhr, textStatus, errorThrown) {
                    self.buttonStatus(self.submitButton, 'reset');
                    self.showError(__('There is something that went wrong!', 'uwpm'));
                }
            });
        },

        /**
         * Show error message
         * @param {string} message 
         */
        showError: function (message) {
            if (this.success.is(':visible')) {
                this.success.addClass('d-none');
            }

            this.error.removeClass('d-none').html(message).slideDown();
        },

        /**
         * Show success message
         * @param {string} message 
         */
        showSuccess: function (message) {
            if (this.error.is(':visible')) {
                this.error.addClass('d-none');
            }

            this.success.removeClass('d-none').html(message).slideDown();
        },

        /**
         * Hide all alert messages
         */
        hideAlerts: function () {
            this.success.addClass('d-none');
            this.error.addClass('d-none');
        },

        /**
         * Format bytes to human readable size
         * @param {number} bytes 
         * @param {number} decimals 
         * @returns {string}
         */
        formatBytes: function (bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        },

        isValidUrl: function (url) {
            try {
                new URL(url);
                return true;
            } catch (e) {
                return false;
            }
        },

        /**
        * Toggle button loading state
        * 
        * @param {jQuery} element Button element
        * @param {string} handle State to set ('loading', 'importing, or 'reset')
        */
        buttonStatus: function (element, handle) {
            if (handle === "loading") {
                element.data('text', element.html());
                element.prop('disabled', true);
                element.html('<i class="fas fa-circle-notch fa-spin ml-2"></i> <span>Loading...</span>');
            } else if (handle === "importing") {
                element.data('text', element.html());
                element.prop('disabled', true);
                element.html('<i class="fas fa-circle-notch fa-spin ml-2"></i> <span>Importing...</span>');
            } else {
                element.prop('disabled', false);
                element.html(element.data('text'));
            }
        },
    });

    $(document).on('geodirModalAfterLoad', function (event, config) {
        if (config.action === '#geodir-event-import-calendar') {
            const uploadImportWrapper = $('.js_event-import');
            if (uploadImportWrapper.length) {
                iCal.UploadImporter.init(uploadImportWrapper, {
                    inProgress: uploadImportWrapper.inProgress
                });
            }
        }
    });

    $(document).ready(function () {
        iCal.Modal.init();
    });
})(jQuery, Geodir_Events_iCal);