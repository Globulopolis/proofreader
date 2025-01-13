/**
 * Proofreader
 *
 * @package     Proofreader
 * @author      Sergey M. Litvinov (smart@joomlatune.com), Olga Novikova (helga@joomlatune.com)
 * @copyright   Copyright (C) 2013-2015 by Sergey M. Litvinov, Olga Novikova. All rights reserved.
 * @copyright   Copyright (C) 2005-2007 by Alexandr Balashov. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(function ($) {
	$.fn.proofreader = function (options) {
		let $container = this,
			that = {
				initialized    : false,
				selectionObject: {},
				scripts        : []
			},
			pluginSettings = $.extend({
				'loadFormUrl'              : null,
				'messagesContainerSelector': '#proofreader_messages_container',
				'typoContainerSelector'    : '#proofreader_typo_container',
				'typoTextElementSelector'  : '#proofreader_typo_text',
				'typoPrefixElementSelector': '#proofreader_typo_prefix',
				'typoSuffixElementSelector': '#proofreader_typo_suffix',
				'highlightClass'           : 'proofreader_highlight',
				'messageErrorClass'        : 'proofreader_message_error',
				'floatingButtonClass'      : 'proofreader_mouse',
				'floatingButtonOffset'     : 15,
				'floatingButtonDelay'      : 2000,
				'highlightTypos'           : false,
				'selectionMaxLength'       : 100,
				'showWordsBefore'          : 10,
				'showWordsAfter'           : 10,
				'handlerType'              : 'keyboard'
			}, options);

		that.init = function () {
			that.clearSelectionObject();

			if ($container.find('form').length) {
				that.initForm();
			}

			if (pluginSettings.handlerType === 'keyboard' || pluginSettings.handlerType === 'both') {
				that.addKeyboardEvents();
			}

			if (pluginSettings.handlerType === 'mouse' || pluginSettings.handlerType === 'both') {
				that.addSelectionEvents();
			}
		};

		that.initForm = function () {
			that.$form = $container.find('form').first();

			if (that.$form.length) {
				that.$messagesContainer = $(pluginSettings.messagesContainerSelector);
				that.$typoContainer = $(pluginSettings.typoContainerSelector);
				that.$typoTextElement = $(pluginSettings.typoTextElementSelector);
				that.$typoPrefixElement = $(pluginSettings.typoPrefixElementSelector);
				that.$typoSuffixElement = $(pluginSettings.typoSuffixElementSelector);

				const $modal = $('#proofreaderModal .modal-footer');
				that.$closeButton = $modal.find('button[data-bs-dismiss]');
				that.$submitButton = $modal.find('button[type="submit"]');

				that.$submitButton.on('click', function (e) {
					e.preventDefault();
					that.submitForm();
				});
				that.$form.on('submit', function (e) {
					e.preventDefault();
					that.submitForm();
				});

				document.getElementById('proofreaderModal').addEventListener('hidden.bs.modal', function() {
					that.hideProofreader();
				});

				that.initialized = true;
			}
		};

		that.loadForm = function (callback) {
			Joomla.request({
				url: pluginSettings.loadFormUrl,
				data: {
					'page_url'  : encodeURI(window.location.href),
					'page_title': $(document).find('title').text()
				},
				onSuccess: (response) => {
					try {
						const _response = JSON.parse(response);

						if (!_response.success) {
							that.showMessage(_response.message, 'danger', true);
						} else {
							if (that.isValidFormResponse(_response.data.form)) {
								that.replaceForm(_response.data.form);
								that.injectScripts(_response.data.scripts, _response.data.script).done(function () {
									if (callback !== undefined) {
										callback();
									}
								});
							} else {
								that.hideProofreader();
							}
						}
					} catch (e) {
						that.showMessage(e, 'danger', true);

						return false;
					}
				},
				onError: (xhr) => {
					try {
						const response = JSON.parse(xhr.response);

						that.showMessage(response.message, 'danger', true);
					} catch (e) {
						that.showMessage(xhr.statusText, 'danger', true);
					}
				},
				onComplete: () => {
					that.$submitButton.removeAttr('disabled');
				}
			});
		};

		that.addKeyboardEvents = function () {
			let isCtrl = false;

			$(document)
				.keyup(function (e) {
					if (e.which === 17) {
						isCtrl = false;
					}
				})
				.keydown(function (e) {
					if (e.which === 27) {
						const pToastEl = document.getElementById('proofreaderToast');
						const pToast = bootstrap.Toast.getInstance(pToastEl);

						if ($container.is(':visible')) {
							that.hideProofreader();
						} else if (pToast.isShown()) {
							that.resetMessagePopup();
						}

						return false;
					}

					if (e.which === 17) {
						isCtrl = true;
					}

					if (isCtrl === true && e.which === 13 && !$container.is(':visible')) {
						that.removeFloatingButton();
						that.refreshSelectionObject();
						that.showProofreader();

						return false;
					}
				});
		};

		that.addSelectionEvents = function () {
			$('body')
				.on('mouseup', function (e) {
					if (!that.isSubmitButtonClick(e)) {
						that.removeFloatingButton();
						that.refreshSelectionObject();

						if (that.canShowProofreader()) {
							that.createFloatingButton(e);
						}
					}
				});
		};

		that.injectScript = function (script) {
			$('head')
				.append($('<script>', {
					'type': 'text/javascript',
					'text': script
				}));
		};

		that.injectScripts = function (scripts, script) {
			let requests = [],
				d = $.Deferred();

			if (scripts) {
				$.each(scripts, function (index, src) {
					if ($.inArray(src, that.scripts) === -1 && !$('script[src="' + src + '"]').length) {
						requests.push($.ajax({
							'url'     : src,
							'dataType': 'script',
							'success' : function () {
								that.scripts.push(src);
							}
						}));
					}
				});

				if (script && script !== '') {
					if (requests.length) {
						$.when.apply($, requests).done(function () {
							that.injectScript(script);
							d.resolve();
						});
					} else {
						that.injectScript(script);
						d.resolve();
					}
				} else {
					d.resolve();
				}
			} else {
				d.resolve();
			}

			return d.promise();
		};

		that.canShowProofreader = function () {
			const pToastEl = document.getElementById('proofreaderToast');
			const pToast = bootstrap.Toast.getInstance(pToastEl);

			return !(that.selectionObject.text === '' || $container.is(':visible') || pToast.isShown());
		};

		that.showProofreader = function () {
			if (that.canShowProofreader()) {
				if (that.selectionObject.text.length > pluginSettings.selectionMaxLength) {
					const text = Joomla.Text._('COM_PROOFREADER_ERROR_TOO_LARGE_TEXT_BLOCK', 'You have selected too large text block!');

					that.showMessage(text, 'danger', (!$container.is(':visible')));

					return;
				}

				if (that.initialized) {
					that.showForm();
				} else if (pluginSettings.loadFormUrl) {
					that.loadForm(that.showForm);
				} else {
					return;
				}

				$container.show();
			}
		};

		that.hideProofreader = function () {
			that.clearSelectionObject();
			that.updateFormHiddenFields();

			$container.hide();
		};

		that.showForm = function () {
			if (that.initialized) {
				that.$form.trigger('reset');
				that.removeFormMessages();
				that.renderFormTypoContainer();
				that.updateFormHiddenFields();

				if (that.$submitButton.length) {
					that.$submitButton.removeAttr('disabled');
				}
			}

			const modal = new bootstrap.Modal('#proofreaderModal');
			const modalToggle = document.getElementById('proofreaderModal');
			modal.show(modalToggle);
		};

		that.submitForm = function () {
			if (!document.formvalidator.isValid(document.getElementById('proofreaderForm'))) {
				that.showMessage(Joomla.Text._('JLIB_FORM_CONTAINS_INVALID_FIELDS'), 'danger');

				return false;
			}

			Joomla.request({
				url: that.$form.attr('action'),
				method: 'POST',
				data: that.$form.serialize(),
				onBefore: () => {
					that.removeMessage();
					that.$submitButton.attr('disabled', 'disabled');
				},
				onSuccess: (response) => {
					const _response = JSON.parse(response);

					if (!_response.success) {
						that.showMessage(_response.message, 'danger');
					} else {
						that.$closeButton.trigger('click');

						if (that.isValidFormResponse(_response.data.form)) {
							that.replaceForm(_response.data.form);
							that.injectScripts(_response.data.scripts, _response.data.script);
						}

						if (pluginSettings.highlightTypos) {
							that.highlightTypo($('.' + pluginSettings.highlightClass), that.$form.find('#proofreader_typo_text').val());
						}

						that.showMessage(Joomla.Text._('COM_PROOFREADER_MESSAGE_THANK_YOU', 'Thank you for reporting the typo!'), 'success', true);
						that.clearSelectionObject();
					}
				},
				onError: (xhr) => {
					that.$closeButton.trigger('click');

					try {
						const response = JSON.parse(xhr.response);

						that.showMessage(response.message, 'danger', true);
					} catch (e) {
						that.showMessage(xhr.statusText, 'danger', true);
					}
				},
				onComplete: () => {
					that.$submitButton.removeAttr('disabled');
				}
			});
		};

		that.replaceForm = function (form) {
			$container
				.find('form')
				.remove()
				.end()
				.show()
				.append(form);

			that.initForm();

			$.each(that.$form.find('label'), function(index, element){
				$(element).removeAttr('title');
			});

			that.$form
				.find('button')
				.focus();
		};

		that.isSubmitButtonClick = function (e) {
			return $(e.target).attr('type') === 'submit';
		};

		that.resetMessagePopup = function () {
			const pToastEl = document.getElementById('proofreaderToast');
			const pToast = bootstrap.Toast.getInstance(pToastEl);
			pToast.hide();
		};

		that.createFloatingButton = function (e) {
			const mousePos = that.getMousePosition(e);

			that.$floatingButton = $('<div>', {
					'text' : Joomla.Text._('COM_PROOFREADER_BUTTON_REPORT_TYPO', 'Report a typo'),
					'class': pluginSettings.floatingButtonClass
				})
				.css({
					'position': 'absolute',
					'top'     : mousePos.y + pluginSettings.floatingButtonOffset + 'px',
					'left'    : mousePos.x + pluginSettings.floatingButtonOffset + 'px'
				})
				.on('mouseup', function () {
					that.showProofreader();
					that.renderFormTypoContainer();
					$(this).remove();

					return false;
				})
				.appendTo($('body'));

			that.floatingButtonTimer = setTimeout(function () {
				if (that.$floatingButton !== undefined) {
					that.$floatingButton.remove();
				}
			}, pluginSettings.floatingButtonDelay);
		};

		that.removeFloatingButton = function () {
			if (that.floatingButtonTimer !== undefined) {
				clearInterval(that.floatingButtonTimer);
			}
			if (that.$floatingButton !== undefined) {
				that.$floatingButton.remove();
			}
		};

		that.updateFormHiddenFields = function () {
			if (that.$typoTextElement.length) {
				that.$typoTextElement.val(that.selectionObject.text);
			}

			if (that.$typoPrefixElement.length) {
				that.$typoPrefixElement.val(that.selectionObject.prefix);
			}

			if (that.$typoSuffixElement.length) {
				that.$typoSuffixElement.val(that.selectionObject.suffix);
			}
		};

		that.renderFormTypoContainer = function () {
			if (that.initialized && that.$typoContainer.length) {
				that.$typoContainer.html('');

				if (that.selectionObject.prefix !== '') {
					that.$typoContainer
						.append($('<span>', {
							'text': that.selectionObject.prefix
						}));
				}

				that.$typoContainer
					.append($('<mark>', {
						'text': that.selectionObject.text,
						'data-markjs': true
					}));

				if (that.selectionObject.suffix !== '') {
					that.$typoContainer
						.append($('<span>', {
							'text': that.selectionObject.suffix
						}));
				}
			}
		};

		that.removeFormMessages = function () {
			const alertDiv = that.$messagesContainer.find('div.alert');

			if (that.initialized && alertDiv.length) {
				const alert = bootstrap.Alert.getOrCreateInstance(alertDiv);
				alert.close();
			}
		};

		/**
		 * Highlight a typo.
		 *
		 * @param   {object}  $node     jQuery node object.
		 * @param   {string}  typoText  Text to highlight.
		 *
		 * @return  void
		 *
		 * @since   2.0
		 */
		that.highlightTypo = function ($node, typoText) {
			let parts,
				text,
				pattern = '',
				replacement = '';

			if (typoText !== ''
				&& $node.length
				&& $node.prop('tagName').toLowerCase() !== 'body'
			) {
				parts = typoText.split(' ');
				$.each(parts, function (index, text) {
					pattern = pattern
						+ '(<[^>]+>)?(\\s)?('
						+ text.replace(/([-.*+?^${}()|[\]\/\\])/g, '\\$1')
						+ ')(\\s)?';
					replacement = replacement
						+ '$' + (index * 4 + 1)
						+ '$' + (index * 4 + 2)
						+ '<mark data-markjs="true">'
						+ '$' + (index * 4 + 3)
						+ '</mark>'
						+ '$' + (index * 4 + 4);
				});

				text = $node
					.html()
					.replace(new RegExp(pattern, 'g'), replacement);

				if (text !== '') {
					$node.html(text);
				}
			}
		};

		that.showMessage = function (text, type, toast) {
			that.resetMessagePopup();
			that.removeMessage();
			that.removeFloatingButton();

			if (toast) {
				const pToastEl = document.getElementById('proofreaderToast');

				if (type === 'success') {
					pToastEl.classList.remove('text-bg-danger');
					pToastEl.classList.add('text-bg-success');
				} else if (type === 'danger') {
					pToastEl.classList.remove('text-bg-success');
					pToastEl.classList.add('text-bg-danger');
				}

				const pToast = bootstrap.Toast.getInstance(pToastEl);
				$(pToastEl).find('.toast-body').text(text);
				pToast.show();
			} else {
				const alertPlaceholder = document.getElementById('proofreader_messages_container');
				let icon = '', textMargin = 'ms-3';

				if (type === 'danger') {
					icon = '<span class="icon icon-cancel-circle"></span>';
				} else if (type === 'warning') {
					icon = '<span class="icon exclamation-triangle"></span>';
				} else {
					textMargin = '';
				}

				if (!$('#proofreader_messages_container .alert').is(':visible')) {
					const wrapper = document.createElement('div');
					wrapper.innerHTML = [
						'<div class="alert alert-' + type + ' alert-dismissible d-flex align-items-center" role="alert">',
						icon + '   <div class="' + textMargin + '">' + text + '</div>',
						'   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
						'</div>'
					].join('');

					// Something went wrong while request ajax-form.
					if (alertPlaceholder) {
						alertPlaceholder.append(wrapper);
					} else {
						that.showMessage(text, type, true);
					}
				}
			}
		};

		that.removeMessage = function (container) {
			let messageContainer;

			if (container instanceof HTMLElement) {
				messageContainer = container;
			} else {
				if (typeof container === 'undefined' || container && container === '#proofreader_messages_container') {
					messageContainer = document.getElementById('proofreader_messages_container');
				} else {
					messageContainer = document.querySelector(container);
				}
			}

			if (messageContainer && messageContainer.querySelectorAll(':scope div').length) {
				that.removeFormMessages();
			}
		}

		that.createSelectionObject = function (text, prefix, suffix, node) {
			return {
				'text'  : text,
				'prefix': prefix,
				'suffix': suffix,
				'node'  : node
			};
		};

		that.clearSelectionObject = function () {
			that.selectionObject = that.createSelectionObject('', '', '');
		};

		that.refreshSelectionObject = function () {
			if (window.getSelection) {
				that.selectionObject = that.getWebKitSelection();
			} else if (document.getSelection) {
				that.selectionObject = that.getGeckoSelection();
			} else if (document.selection) {
				that.selectionObject = that.getTridentSelection();
			} else {
				that.clearSelectionObject();
				that.showMessage(Joomla.Text._('COM_PROOFREADER_ERROR_BROWSER_IS_NOT_SUPPORTED', 'Your browser does not support selection handling.'), 'danger', true);
			}

			if (that.selectionObject.text === '') {
				that.clearSelectionObject();
			}
		};

		that.getRangeText = function (range) {
			let fragment = range.cloneContents(),
				div = document.createElement('div'),
				$skipElements,
				length;

			while (fragment.firstChild) {
				div.appendChild(fragment.firstChild);
			}

			$skipElements = $(div).find('script,style,form');
			length = $skipElements.length;
			while (length--) {
				$skipElements[length].parentNode.removeChild($skipElements[length]);
			}

			while (div.firstChild) {
				fragment.appendChild(div.firstChild);
			}

			return fragment.textContent;
		};

		that.getSelectionContainer = function (node) {
			while (node) {
				if (node.nodeType === 1) {
					return node;
				}
				node = node.parentNode;
			}
		};

		that.getWebKitSelection = function () {
			let selection = window.getSelection(),
				text = '',
				prefix = '',
				suffix = '',
				node,
				range,
				prefixRange,
				suffixRange;

			if (selection && selection.rangeCount > 0) {
				text = selection.toString();
				range = selection.getRangeAt(0);
				node = that.getSelectionContainer(range.commonAncestorContainer);

				prefixRange = range.cloneRange();
				prefixRange.setStartBefore(range.startContainer.ownerDocument.body);
				prefixRange.setEnd(range.startContainer, range.startOffset);
				prefix = that.truncateText(that.getRangeText(prefixRange), -pluginSettings.showWordsBefore);

				suffixRange = range.cloneRange();
				suffixRange.setStart(range.endContainer, range.endOffset);
				suffixRange.setEndAfter(range.endContainer.ownerDocument.body);
				suffix = that.truncateText(that.getRangeText(suffixRange), pluginSettings.showWordsAfter);
			}

			return that.createSelectionObject(text, prefix, suffix, node);

		};

		that.getGeckoSelection = function () {
			const text = document.getSelection().toString();

			// TODO try to get prefix and suffix
			return that.createSelectionObject(text, '', '');
		};

		that.getTridentSelection = function () {
			const selection = document.selection,
				range = selection.createRange,
				text = range.text,
				node = that.getSelectionContainer(range.parentElement()),
				prefixRange = selection.createRange(),
				suffixRange = selection.createRange();

			prefixRange.moveStart('word', -pluginSettings.showWordsBefore);
			prefixRange.moveEnd('character', -text.length);

			suffixRange.moveStart('character', text.length);
			suffixRange.moveEnd('word', pluginSettings.showWordsAfter);

			return that.createSelectionObject(text, prefixRange.text, suffixRange.text, node);
		};

		that.getMousePosition = function (e) {
			let pos = {'x': 0, 'y': 0};

			if (e) {
				if (e.pageX || e.pageY) {
					pos.x = e.pageX;
					pos.y = e.pageY;
				} else if (e.clientX || e.clientY) {
					pos.x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
					pos.y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
				}
			}
			return pos;
		};

		that.truncateText = function (text, length) {
			let words = text.replace(/(\r|\n|\t)+/g, ' ').replace(/(\s)+/g, ' ').split(' ').filter(Boolean),
				start = Math.min(words.length, Math.abs(length)),
				result = '';

			if (length > 0) {
				result = (text.match(/^\s/g) ? ' ' : '') + words.slice(0, start).join(' ');
			} else {
				result = words.slice(words.length - start).join(' ') + (text.match(/\s$/g) ? ' ' : '');
			}

			return result;
		};

		that.isValidFormResponse = function(form) {
			return form && $('<div>' + form + '</div>').find('form').length > 0;
		};

		that.init();

		return $container;
	};
})(jQuery);
