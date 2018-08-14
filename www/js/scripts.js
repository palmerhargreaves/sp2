$(document).ready(function () {
    var opened;

    //External link modal dialog
    if (RegExp('link', 'gi').test(window.location) && RegExp('hash', 'gi').test(window.location)) {
        setTimeout(function() {
            $('#add-model-categories-button').trigger('click');
        }, 1500);
    }

    $('#user-menu').click(function () {
        //$(this).toggleClass('open');
        $(this).find(".items").slideToggle();
    });

    $('#user-menu li').click(function () {
        $(this).find('a').each(function () {
            location.href = this.href;
        });
    });

    $('#user-messages').click(function (e) {
        if ($('.items', e.currentTarget).length > 0)
            $(this).toggleClass('open');
        else
            location.href = $('.messages-view-all', e.currentTarget).data('url');
    });

    $('.tabs').each(function () {
        var width = 0;
        $(this).children('.tab').each(function () {
            width += $(this).outerWidth();
        });
        $(this).siblings('.pane-shadow').width(width + 6);
    });

//	$('.tabs li').click(function(){
//		$('.tabs li').removeClass('active');
//		$(this).addClass('active');
//		$('.pane>div').removeClass('active');
//		$('.pane>div').eq($(this).index()).addClass('active');
//	});

    $('.tabs li').click(function (e) {
        if ($(e.target).closest('a').length > 0)
            return;

        var $a = $(this).closest('li').find('a');
        if ($a.length > 0)
            $a.clickAnchor();
    });

    $('.group.open .group-content').show();

    $('.group-header').click(function () {
        $(this).parents('.group').toggleClass('open');
        $(this).parents('.group').find('.group-content').slideToggle();

        if ($(this).parents('.group').hasClass('open'))
            $('html,body').animate({scrollTop: $(this).offset().top}, 500);
    });

    var matches = location.hash.match(/#material\/([0-9]+)(\/([0-9]+))?/);
    if (matches) {
        $('#materials .group').removeClass('open');
        $('#material-group-' + matches[1]).addClass('open');
    }

    $('#pass-change-link').click(function () {
        $('#pass-change').krikmodal('show');
    });

    $('#switch-to-dealer-link').click(function () {
        $('#switch-to-dealer').krikmodal('show');
    });

//	$('.modal-close').click(function(){
//		$(this).parents('.modal').hide();
//	});

//	$('#change-button').click(function(){
//		$('#pass-change').hide();
//		$('#pass-changed').show();
//	});

//	$('.banner').click(function(){
//		$('#zoom').show();
//		$('#zoom2').show();
//		opened = $(this);
//	});
//	
//	$('#zoom .modal-close').click(function(){
//		opened.addClass("closed");
//	});

    $('#chat-button').click(function () {
        $('#chat-modal').krikmodal('show');
        if ($('#chat-modal').data('manager-discussion') != 'yes')
            window.common_discussion.startDiscussion($('#chat-modal').data('dealer-discussion'));
    });

    $('.unblock-model').click(function (e) {
        e.stopPropagation();

        var bt = $(this);
        $.post(bt.closest('div[id=agreement-models]').data('url'),
            {
                modelId: bt.data('model-id')
            },
            function () {
                bt.fadeOut();
            });
    });

    $('#chat-modal select[name=dealer]').change(function () {
        var value = $(this).val();
        if (!value)
            window.common_discussion.stopDiscussion();
        else
            window.common_discussion.startDiscussionWithDealer(value);
    });

    var matches = location.hash.match(/#ask\/([0-9]+)\/([0-9]+)/);
    if (matches) {
        setTimeout(function () {
            $('#chat-modal').krikmodal('show');
            if ($('#chat-modal').data('manager-discussion') != 'yes') {
                window.common_discussion.startDiscussion($('#chat-modal').data('dealer-discussion'), matches[2]);
            } else {
                $('#chat-modal select[name=dealer]').val(matches[1]);
                window.common_discussion.startDiscussionWithDealer(matches[1], matches[2]);
            }
        }, 500);
    }

    $('#chat-modal').on('close-modal', function () {
        window.common_discussion.stopDiscussion();
    })

    $('.scroller').tinyscrollbar({size: 336, sizethumb: 41});

    $('#chat-modal').hide();

    $('.modal-file-wrapper .js-dealer-statistics-upload-file, .modal-file-wrapper .js-dealer-extended-statistics-upload-file').live('change', function () {
        var titles = [], files = this.files, total_files = 0, max_upload_files = $(this).data('max-upload-files'), max_files_cls = '';

        $.each(files, function (i, file) {
            var item_to_add = $('<div/>');

            item_to_add.append('<span class="model-add-file-name ' + max_files_cls + '">' + getUploadedFileTitle(file.name) + '</span>');
            item_to_add.append('( <span class="model-add-file-size ' + max_files_cls + '">' + humanFileSize(file.size) + '</span>)');

            titles.push('<div class="model-add-file-container">' + item_to_add.html() + '</div>');

            total_files++;
        });

        $(this).parents('.file').find('.file-name').html(titles.join('<br/>'));

    }).live('reset', function () {
        $(this).parents('.file').find('.file-name').html('');

        if ($(this).data('ext-model-file') == 1)
            $(this).closest('tr').remove();
    });

    $('.modal-file-model-report input').live('change', function () {
        var idx = $(this).data('idx');

        if ($(this).prop('data-is-loaded') == undefined && idx != undefined) {
            $(this).prop('data-is-loaded', true);

            idx++;
            $.post($(this).data('file-container-url'),
                {
                    fileIdx: idx
                },
                function (result) {
                    $('.panel-decline-files-container').append(result);
                }
            )
        }
    });

    //Проверка даты в календаре
    window.dates_in_calendar = [];
    $.post('/agreement/model/check/date/in/calendar', function(result) {
        window.dates_in_calendar = result.dates;
    });

    $('input.date').datepicker({
        dateFormat: "dd.mm.y",
        beforeShowDay: function (date) {
            var modelCategory = $('input[name="model_type_id"]'),
                change_period_button = $("div.change-period-model-type-" + modelCategory.val());

            //Make compatibility with old models
            if (change_period_button.length == 0) {
                change_period_button = $(".js-change-model-period");
            }

            if (modelCategory.length != 0) {
                if (modelCategory.data('is-sys-admin') && change_period_button.length > 0 && change_period_button.data('action') == 'apply') {
                    return [true];
                }

                var allow_date = true, check_date = '';

                check_date = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();
                window.dates_in_calendar.forEach(function(el) {
                    if (el == check_date) {
                        allow_date = false;
                    }
                });

                if (!allow_date) {
                    return [false];
                }

                var today = new Date().getTime() + (2 * 86400000),
                    tmp = new Date(today);

                //Убираем нерабочие дни
                /*if (date.getDay() == 0 || date.getDay() == 6) {
                    return [false];
                }*/

                if (date.getTime() > tmp.getTime())
                    return [true];

                if (parseInt(date.getMonth()) == parseInt(tmp.getMonth())) {
                    if (parseInt(date.getDate()) > parseInt(tmp.getDate()))
                        return [true];
                    else
                        return [false];
                }
            }

            return [false];
        }
    });

    $(':input[placeholder]').defaultValue();
    $(':input.date.empty').removeClass('date');

    var rainbow = new Rainbow();
    rainbow.setSpectrum('00e900', 'ffcc00', 'f91800');

    $('.quarter-pane .timeline-wrapper .line .caret').each(function () {
        var percent = $(this).data("percent");
        var color = rainbow.colourAt(percent);
        $(this).css("background-color", "#" + color);
    });

    var blue = $(".budget .progressbar .blue");
    blue.each(function () {
        var blueElement = $(this);
        if (blueElement.data("percent") > 0)
            setTimeout(function () {
                blueElement.show().animate({width: blueElement.data("percent") + "%"}, 1000);
            }, 700)
    });

    var companys_percent = $('.activity-main-page .activity-main-page-sums .js-progressbar');
    companys_percent.each(function () {
        var percent_item = $(this).find('i');
        if (percent_item.data("percent") > 0)
            setTimeout(function () {
                percent_item.show().animate({width: percent_item.data("percent") + "%"}, 1000);
            }, 700)
    });

    showInfoMsg('what-info', 'В случае, если данный макет был ранее утвержден, укажите в данном поле номер заявки, в которой был согласован макет.');
    showInfoMsg('what-info-conception', 'Здесь вам необходимо добавить конечную дату действия сертификата на выгодное обслуживание, который вы будете выдавать участникам мероприятия после проверки.');

    $('div.add-child-field').live('click', function () {
        var isHide = false;

        $.each($("tr.type-fields-" + $(this).data('model-id')), function (ind, el) {
            if (!$(el).is(':visible') && !isHide) {
                $(el).fadeIn();

                isHide = true;
            }

        });
    });

    $('.remove-report-ext-file, .remove-concept-ext-file').live('click', function () {
        var $el = $(this);

        if (confirm('Удалить файл ?')) {
            var modelId = $el.data('model-id'),
                fileId = $el.data('file-id'),
                fileInd = $el.data('file-ind'),
                fileType = $el.data('el'),
                isModel = $el.data('is-model'),
                isConcept = $el.data('is-concept');

            $.post('/activity/module/agreement/delete/model/file',
                {
                    modelId: modelId,
                    fileId: fileInd,
                    fileType: fileType,
                    isModel: isModel
                },
                function (result) {
                    !isConcept ? $('tr.' + fileType + fileInd).remove() : $('.' + fileType + fileInd).remove();
                });
        }
    });

    if (RegExp('hash', 'gi').test(window.location.href)) {
        setTimeout(function () {
            $('#add-model-button').trigger('click');
        }, 2000);
    }

    $(document).on('mouseover', '.info-download-file-size', function () {

        $(this).popmessage('show', 'info', 'Размер загружаемого файла не должен превышать 5 Мб.');

        setTimeout(function () {
            $(this).popmessage('hide');
        }, 5000);
    });

    $(document).on('mouseout', '.info-download-file-size', function () {
        $(this).popmessage('hide');
    });

    $(document).on('click', '.js-activity-status-by-user', function(event) {
        $.post($(event.target).data('url'), {
            quarter: $(event.target).data('quarter')
        }, function(result) {

        });
    });

});

function getUploadedFileTitle(file) {
    var name = file;
    var win_pattern = /.*\\(.*)/;
    var file_title = name.replace(win_pattern, "$1");
    var unix_pattern = /.*\/(.*)/;

    return file_title.replace(unix_pattern, "$1");
}

function startDiscussionWithDealer(id) {
    $('#chat-modal').krikmodal('show');
    $('#chat-modal select[name=dealer]').val(id);
    window.common_discussion.startDiscussionWithDealer(id);
}

function showInfoMsg(from, msg) {
    $('.' + from).live('click', function () {
        $(this).popmessage('show', 'info', msg);

        setTimeout(function () {
            $('.' + from).popmessage('hide');
        }, 5000);
    });
}

function showAlertPopup(title, msg) {
    var $errorPopup = $('#j-alert-global');

    $errorPopup.find('.j-title').html(title);
    $errorPopup.find('.j-message').html(msg);
    $errorPopup.fadeIn();

    scrollTop('#j-alert-global');
    setTimeout(function() {
        $('#j-alert-global').fadeOut();
    }, 5000);
}

function scrollTop (ancor, parent){
    var offset = 0;

    if ($(ancor).length > 0) {
        offset = $(ancor).offset().top - 10;

        if (parent != undefined) {
            var scroll_position = $('#' + parent).scrollTop();

            offset = $(ancor).offset().top;
            if (offset < 0) {
                offset = scroll_position - Math.abs(offset);
            } else {
                offset = scroll_position + offset;
            }
            offset -= $('#' + parent).offset().top;
        }

        $(parent != undefined ? ('#' + parent) : "body, html").animate({
                scrollTop: offset + "px"
            },
            {duration: 500});
    }
}

function humanFileSize(bytes, si) {
    var thresh = si ? 1000 : 1024;

    if (Math.abs(bytes) < thresh) {
        return bytes + ' B';
    }
    var units = si
        ? ['kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB']
        : ['KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
    var u = -1;
    do {
        bytes /= thresh;
        ++u;
    } while (Math.abs(bytes) >= thresh && u < units.length - 1);

    return bytes.toFixed(1) + ' ' + units[u];
}

function TranslitSymbols(text){
    var space = '-';

    var transl = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'e', 'ж': 'zh',
        'з': 'z', 'и': 'i', 'й': 'j', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
        'о': 'o', 'п': 'p', 'р': 'r','с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h',
        'ц': 'c', 'ч': 'ch', 'ш': 'sh', 'щ': 'sh','ъ': space, 'ы': 'y', 'ь': space, 'э': 'e', 'ю': 'yu', 'я': 'ya',
        ' ': space, '_': space, '`': space, '~': space, '!': space, '@': space,
        '#': space, '$': space, '%': space, '^': space, '&': space, '*': space,
        '(': space, ')': space,'-': space, '\=': space, '+': space, '[': space,
        ']': space, '\\': space, '|': space, '/': space,'.': space, ',': space,
        '{': space, '}': space, '\'': space, '"': space, ';': space, ':': space,
        '?': space, '<': space, '>': space, '№':space
    }

    var result = '';
    var curent_sim = '';

    for(i=0; i < text.length; i++) {
        // Если символ найден в массиве то меняем его
        if(transl[text[i]] != undefined) {
            if(curent_sim != transl[text[i]] || curent_sim != space){
                result += transl[text[i]];
                curent_sim = transl[text[i]];
            }
        }
        // Если нет, то оставляем так как есть
        else {
            result += text[i];
            curent_sim = text[i];
        }
    }

    return TrimStr(result);
}

function TrimStr(s) {
    s = s.replace(/^-/, '');
    return s.replace(/-$/, '');
}

function addShakeAnim (cls, form) {
    $(cls, form).addClass('shake-container');
    setTimeout(function() {
        $(cls, form).removeClass('shake-container');
    }, 500);
}
