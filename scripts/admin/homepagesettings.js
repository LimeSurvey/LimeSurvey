/**
 * JavaScript functions for HomePage Settings
 */
$(document).ready(function () {

    /**
     * Toggle show logo value
     */
    $('#show_logo').on('switchChange.bootstrapSwitch', function (event, state) {
        $url = $('#show_logo-url').attr('data-url');
        $.ajax({
            url: $url,
            type: 'GET',
            dataType: 'html',

            // html contains the buttons
            success: function (html, statut) {
            },
            error: function (html, statut) {
                alert('error');
            }
        });
    });

    /**
     * Toggle show last_survey_and_question value
     */
    $('#show_last_survey_and_question').on('switchChange.bootstrapSwitch', function (event, state) {
        $url = $('#show_last_survey_and_question-url').attr('data-url');
        $.ajax({
            url: $url,
            type: 'GET',
            dataType: 'html',

            // html contains the buttons
            success: function (html, statut) {
            },
            error: function (html, statut) {
                alert('error');
            }
        });
    });

    /**
     * Toggle show survey list value
     */
    $('#show_survey_list').on('switchChange.bootstrapSwitch', function (event, state) {
        $url = $('#show_survey_list-url').attr('data-url');
        console.log($url);
        $.ajax({
            url: $url,
            type: 'GET',
            dataType: 'html',

            // html contains the buttons
            success: function (html, statut) {
            },
            error: function (html, statut) {
                alert('error');
            }
        });
    });

    /**
     * Toggle show survey list search value
     */
    $('#show_survey_list_search').on('switchChange.bootstrapSwitch', function (event, state) {
        $url = $('#show_survey_list_search-url').attr('data-url');
        console.log($url);
        $.ajax({
            url: $url,
            type: 'GET',
            dataType: 'html',

            // html contains the buttons
            success: function (html, statut) {
            },
            error: function (html, statut) {
                alert('error');
            }
        });
    });

    /**
     * Save box settings
     */
    $('#save_boxes_setting').on('click', function () {
        $url = $(this).attr('data-url');
        $iBoxesByRow = $('#iBoxesByRow').val();
        $iBoxesOffset = $('#iBoxesOffset').val();
        $successMessage = $('#boxesupdatemessage').data('ajaxsuccessmessage');
        $.ajax({
            url: $url + '/boxesbyrow/' + $iBoxesByRow + '/boxesoffset/' + $iBoxesOffset,
            type: 'GET',
            dataType: 'html',
            // html contains the buttons
            success: function (html, statut) {
                $('#notif-container').append('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close limebutton" data-dismiss="alert" aria-label="Close"><span>×</span></button>' + $successMessage + '</div>');
            },
            error: function (html, statut) {
                alert('error');
            }
        });
    });

    /**
     * Confirmation modal
     */
    $('a[data-confirm]').click(function (ev) {
        var href = $(this).attr('href');
        if (!$('#dataConfirmModal').length) {
            $('body').append('<div  id="dataConfirmModal" class="modal  fade" role="dialog" aria-labelledby="dataConfirmLabel">  <div class="modal-dialog">    <div class="modal-content">      <div class="modal-header">        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>        <h4 class="modal-title">' + strConfirm + '</h4>      </div>      <div class="modal-body">      </div>      <div class="modal-footer"><a class="btn btn-primary" id="dataConfirmOK">' + strOK + '</a><button  type="button" class="btn btn-default" data-dismiss="modal" >' + strCancel + '</button>      </div>    </div><!-- /.modal-content -->  </div><!-- /.modal-dialog --></div><!-- /.modal -->');
        }
        $('#dataConfirmModal').find('.modal-body').text($(this).attr('data-confirm'));
        $('#dataConfirmOK').attr('href', href);
        $('#dataConfirmModal').modal({show: true});
        return false;
    });


    // Create Update : icons
    if ($('.option-icon').length > 1) {
        $('.option-icon').on('click', function (ev, that) {
            var fullIconName = $(ev.currentTarget).attr('data-icon');
            var iconName = fullIconName.substr(5)

            // Set icon preview and hidden input
            $('input[name="Boxes[ico]"]').val(iconName);
            $('#chosen-icon').attr('class', fullIconName + ' text-success');
        });

        // Show current icon
        var currentIcon = $('input[name="Boxes[ico]"]').val();
        if (currentIcon !== '') {
            var fullCurrentIconName = 'icon-' + currentIcon;
            $('#chosen-icon').attr('class', fullCurrentIconName + ' text-success');
        }
    }

    /**
     * This part show and hide form elem, depending on the selection of "#Boxes_custom_content"
     */

    var scenarioDefault = ['.o-homepage-box-form__icon-select', '.o-homepage-box-form__url'];
    var scenarioCustom = ['.o-homepage-box-form__class-name'];
    var customContentState = $("#Boxes_custom_content").val();

    /**
     * Set scenario
     */
    function setScenario() {
        var scenario = {};
        if (customContentState == 0) {
            scenario.showElems = scenarioDefault;
            scenario.hideElems = scenarioCustom;
        } else {
            scenario.showElems = scenarioCustom;
            scenario.hideElems = scenarioDefault;
        }
        return scenario;
    }

    /**
     * This function toggle form elems depending of the choosen scenario
     */
    function toggleFormElems() {
        $.each(setScenario(), function (scenarioKey, selectors) {
            if (scenarioKey === 'showElems') {
                $.each(selectors, function (key, value) {
                    $(value).show();
                });
            }
            if (scenarioKey === 'hideElems') {
                $.each(selectors, function (key, value) {
                    $(value).hide();
                });
            }
        });
    }

    /**
     * This function resets the field on reload
     */
    function clearFieldsOnReload() {
        if (customContentState == 1) {
            $('#Boxes_url').val(null);
            $('.o-homepage-box-form__icon-select').find('input.form-control').val(null);
            $('#chosen-icon').removeClass();
        }
        if (customContentState == 0) {
            $('#Boxes_custom_classname').val(null);
        }
    }

    /**
     * This function init the form onload
     */
    function initForm() {
        toggleFormElems();
        clearFieldsOnReload();
    }

    /**
     * This function toggles form elems onchange
     */
    $("#Boxes_custom_content").change(function () {
        customContentState = this.value;
        toggleFormElems();
        clearFieldsOnReload();
    });

    initForm();

});
