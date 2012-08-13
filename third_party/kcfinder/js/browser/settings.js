<?php

/** This file is part of KCFinder project
  *
  *      @desc Settings panel functionality
  *   @package KCFinder
  *   @version 2.51
  *    @author Pavel Tzonkov <pavelc@users.sourceforge.net>
  * @copyright 2010, 2011 KCFinder Project
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
        $('#show input[name="' + val + '"]').get(0).checked = checked;
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

    $('#order input[value="' + _.kuki.get('order') + '"]').get(0).checked = true;
    $('#order input[name="desc"]').get(0).checked = (_.kuki.get('orderDesc') == 'on');

    $('#order input[type="radio"]').click(function() {
        _.kuki.set('order', $(this).get(0).value);
        browser.orderFiles();
    });

    $('#order input[name="desc"]').click(function() {
        _.kuki.set('orderDesc', $(this).get(0).checked ? 'on' : 'off');
        browser.orderFiles();
    });

    if (!_.kuki.isSet('view'))
        _.kuki.set('view', 'thumbs');

    if (_.kuki.get('view') == 'list') {
        $('#show input').each(function() { this.checked = true; });
        $('#show input').each(function() { this.disabled = true; });
    }

    $('#view input[value="' + _.kuki.get('view') + '"]').get(0).checked = true;

    $('#view input').click(function() {
        var view = $(this).attr('value');
        if (_.kuki.get('view') != view) {
            _.kuki.set('view', view);
            if (view == 'list') {
                $('#show input').each(function() { this.checked = true; });
                $('#show input').each(function() { this.disabled = true; });
            } else {
                $.each(browser.shows, function(i, val) {
                    $('#show input[name="' + val + '"]').get(0).checked =
                        (_.kuki.get('show' + val) == "on");
                });
                $('#show input').each(function() { this.disabled = false; });
            }
        }
        browser.refresh();
    });
};
