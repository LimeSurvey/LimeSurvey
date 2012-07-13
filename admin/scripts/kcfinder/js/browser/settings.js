<?php

/** This file is part of KCFinder project
  *
  *      @desc Settings panel functionality
  *   @package KCFinder
  *   @version 2.21
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010 KCFinder Project
  *   @license http://www.opensource.org/licenses/gpl-2.0.php GPLv2
  *   @license http://www.opensource.org/licenses/lgpl-2.1.php LGPLv2
  *      @link http://kcfinder.sunhater.com
  */?>

browser.initSettings = function() {

    if (!this.shows.length) {
        var showInputs = $('#show input[type="checkbox"]').toArray();
        $.each(showInputs, function (i, input) {
            browser.shows[i] = input.name;
        });
    }

    var shows = this.shows;

    if (!_.kuki.isSet('showname')) {
        _.kuki.set('showname', 'on');
        $.each(shows, function (i, val) {
            if (val != "name") _.kuki.set('show' + val, 'off');
        });
    }

    $('#show input[type="checkbox"]').click(function() {
        var kuki = $(this).get(0).checked ? 'on' : 'off';
        _.kuki.set('show' + $(this).get(0).name, kuki)
        if ($(this).get(0).checked)
            $('#files .file div.' + $(this).get(0).name).css('display', 'block');
        else
            $('#files .file div.' + $(this).get(0).name).css('display', 'none');
    });

    $.each(shows, function(i, val) {
        var checked = (_.kuki.get('show' + val) == 'on') ? 'checked' : '';
        $('#show input[name="' + val + '"]').attr('checked', checked);
    });

    if (!this.orders.length) {
        var orderInputs = $('#order input[type="radio"]').toArray();
        $.each(orderInputs, function (i, input) {
            browser.orders[i] = input.value;
        });
    }

    var orders = this.orders;

    if (!_.kuki.isSet('order'))
        _.kuki.set('order', 'name');

    if (!_.kuki.isSet('orderDesc'))
        _.kuki.set('orderDesc', 'off');

    $('#order input[value="' + _.kuki.get('order') + '"]').attr('checked', 'checked');
    $('#order input[name="desc"]').attr('checked',
        (_.kuki.get('orderDesc') == 'on') ? 'checked' : ''
    );

    $('#order input[type="radio"]').click(function() {
        _.kuki.set('order', $(this).get(0).value);
        browser.orderFiles();
    });

    $('#order input[name="desc"]').click(function() {
        _.kuki.set('orderDesc', $(this).get(0).checked ? "on" : "off");
        browser.orderFiles();
    });

    if (!_.kuki.isSet('view'))
        _.kuki.set('view', 'thumbs');

    if (_.kuki.get('view') == "list") {
        $('#show input').attr('checked', 'checked');
        $('#show input').attr('disabled', 'disabled');
    }

    $('#view input[value="' + _.kuki.get('view') + '"]').attr('checked', 'checked');

    $('#view input').click(function() {
        var view = $(this).attr('value');
        if (_.kuki.get('view') != view) {
            _.kuki.set('view', view);
            if (view == 'list') {
                $('#show input').attr('checked', 'checked');
                $('#show input').attr('disabled', 'disabled');
            } else {
                $.each(browser.shows, function(i, val) {
                    if (_.kuki.get('show' + val) != "on")
                        $('#show input[name="' + val + '"]').attr('checked', '');
                });
                $('#show input').attr('disabled', '');
            }
        }
        browser.refresh();
    });
};
