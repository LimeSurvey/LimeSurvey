/*
 * JavaScript functions for LimeSurvey administrator
 *
 * This file is part of LimeSurvey
 * Copyright (C) 2007-2013 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */



//Define LS Namespace
window.LS = window.LS || {};

//import lodash
import _ from 'lodash';

//import jquery additions and prototypes
import './jqueryAdditions/center.js';
import './jqueryAdditions/isEmpty.js';
import './parts/prototypeDefinition';
import './components/bootstrap-remote-modals';


//import page wise functionality
import questionEdit from './pages/questionEditing';
import * as quickAction from './pages/quickaction';
import {subquestionAndAnswersGlobalMethods} from './pages/subquestionandanswers';
import {onExistBinding as surveyGrid} from './pages/surveyGrid';

//import parts for globalscope
import confirmationModal from './parts/confirmationModal'; 
import {globalStartUpMethods, globalWindowMethods, globalOnloadMethods} from './parts/globalMethods';
import * as notifyFader from './parts/notifyFader';
import * as AjaxHelper from './parts/ajaxHelper';
import saveBindings from './parts/save';

// import components
import confirmDeletemodal from './components/confirmdeletemodal';
import panelClickable from './components/panelclickable';
import panelsAnimation from './components/panelsanimation';
import notificationSystem from './components/notifications';
import LOG from './components/lslog';

const AdminCore = function(){
    //Singelton Pattern -> the AdminCore functions can only be nound once.
    if(typeof window.LS.adminCore === 'object') {
        window.LS.adminCore.refresh();
        return;
    }

    const eventsBound = {
        document: []
    };

    const 
        onLoadRegister = () => {
            globalStartUpMethods.bootstrapping();
            surveyGrid();
            appendToLoad(saveBindings);
            appendToLoad(confirmationModal);
            appendToLoad(questionEdit);
            appendToLoad(confirmDeletemodal);
            appendToLoad(panelClickable);
            appendToLoad(panelsAnimation);
            appendToLoad(notificationSystem.initNotification);
        },
        appendToLoad = (fn, event, root) => {
            event = event || 'ready pjax:scriptcomplete';
            root = root || 'document';
            LOG.log('appendToLoad', {
                'type' : typeof fn,
                'fn' : fn
            })
            eventsBound[root] = eventsBound[root] || [];
            
            if(_.find(eventsBound[root], {fn, event, root}) === undefined) {
                eventsBound[root].push({fn, event, root});
                const events = _.map(event.split(' '), (event) => event+'.admincore');
                if(root == 'document') {
                    $(document).on(events.join(' '), fn);
                } else {
                    $(root).on(events.join(' '), fn);
                }
            }
        },
        refreshAdminCore = () => {
            _.each(eventsBound, (eventMap, root) => {
                _.each(eventMap, (evItem) => {
                    const events = _.map(evItem.event.split(' '), (event) => event+'.admincore');
                    $(evItem.root).off(events.join(' '));
                    $(evItem.root).on(events.join(' '), evItem.fn);
                });
            });
            surveyGrid();
            LOG.log("Refreshed Admin core methods");
        },
        setNameSpace = () => {
            const BaseNameSpace = {
                adminCore : {
                    refresh: refreshAdminCore,
                    onload: onLoadRegister,
                    appendToLoad: appendToLoad
                }
            };
            const LsNameSpace = _.merge(BaseNameSpace, globalWindowMethods, AjaxHelper, notifyFader, subquestionAndAnswersGlobalMethods, notificationSystem);
            
            /*
            * Set the namespace to the global variable LS
            */
            window.LS = _.merge(window.LS, LsNameSpace, {ld: _});
            
            /* Set a variable to test if browser have HTML5 form ability
            * Need to be replaced by some polyfills see #8009
            */
            window.hasFormValidation= typeof document.createElement( 'input' ).checkValidity == 'function';

        };
        setNameSpace();
        onLoadRegister();
        LOG.log("AdminCore", eventsBound);
}

AdminCore();