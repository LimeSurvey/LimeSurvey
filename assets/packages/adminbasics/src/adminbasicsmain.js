/*
 * JavaScript functions for GititSurvey administrator
 *
 * This file is part of LimeSurvey
 * Copyright (C) 2007-2013 The GititSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * GititSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */


//import the ES6 polyfills for IE11
import 'core-js';


//Define LS Namespace
window.LS = window.LS || {};

//import lodash
import _ from 'lodash';

//import jquery additions and prototypes
import './jqueryAdditions/center.js';
import './jqueryAdditions/isEmpty.js';
import './jqueryAdditions/bootstrapconfirm.js';
import './parts/prototypeDefinition';
import './components/bootstrap-remote-modals';


//import page wise functionality
import questionEdit from './pages/questionEditing';
//import * as quickAction from './pages/quickaction'; ->temporary deprecated
import {subquestionAndAnswersGlobalMethods} from './pages/subquestionandanswers';

//import parts for globalscope
import confirmationModal from './parts/confirmationModal';
import {globalStartUpMethods, globalWindowMethods} from './parts/globalMethods';
import autoCloseAlerts from './parts/autoCloseAlerts';
import ajaxAlerts, {ajaxAlertMethod} from './parts/ajaxAlerts';
import * as AjaxHelper from './parts/ajaxHelper';
import createUrl from './parts/createUrl';
import saveBindings from './parts/save';
import parameterGlobals from './parts/parameterGlobals';

// import components
import activateSubSubMenues from './components/bootstrap-sub-submenues';
import confirmDeletemodal from './components/confirmdeletemodal';
import fileManagerStyle from './components/file-manager';
import panelClickable from './components/panelclickable';
import panelsAnimation from './components/panelsanimation';
import notificationSystem from './components/notifications';
import gridAction from './components/gridAction';
import EventBus from './components/eventbus';
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

    const debug = () => {
        return {eventsBound, windowLS : window.LS }
    };

    const
        onLoadRegister = () => {
            globalStartUpMethods.bootstrapping();
            appendToLoad(function(){LOG.log('TRIGGERWARNING','Document ready triggered')}, 'ready');
            appendToLoad(function(){LOG.log('TRIGGERWARNING','Document scriptcomplete triggered')}, 'pjax:scriptcomplete');
            appendToLoad(saveBindings);
            appendToLoad(confirmationModal);
            appendToLoad(questionEdit);
            appendToLoad(confirmDeletemodal);
            appendToLoad(fileManagerStyle);
            appendToLoad(panelClickable);
            appendToLoad(window.LS.doToolTip);
            appendToLoad(panelsAnimation, null, null, 200);
            appendToLoad(notificationSystem.initNotification);
            appendToLoad(activateSubSubMenues);
            appendToLoad(globalWindowMethods.fixAccordionPosition);
            appendToLoad(globalWindowMethods.doSelect2);
        },
        appendToLoad = (fn, event, root, delay) => {
            event = event || 'pjax:scriptcomplete ready';
            root = root || 'document';
            delay = delay || 0;
            eventsBound[root] = eventsBound[root] || [];

            if(_.find(eventsBound[root], {fn, event, root, delay}) === undefined) {
                eventsBound[root].push({fn, event, root, delay});
                const events = _.map(event.split(' '), (event) => (event !== 'ready' ? event+'.admincore' : 'ready') );
                const call = delay > 0 ? () => { window.setTimeout(fn, delay); } : fn;
                if(root == 'document') {
                    $(document).on(events.join(' '), call);
                } else {
                    $(root).on(events.join(' '), call);
                }
            }
        },
        refreshAdminCore = () => {
            _.each(eventsBound, (eventMap, root) => {
                _.each(eventMap, (evItem) => {
                    const events = _.map(evItem.event.split(' '), (event) => (event !== 'ready' ? event+'.admincore' : ''));
                    const call = evItem.delay > 0 ? () => { window.setTimeout(evItem.fn, evItem.delay); } : evItem.fn;
                    if(evItem.root !== 'document') {
                        $(evItem.root).off(events.join(' '));
                        $(evItem.root).on(events.join(' '), call);
                    }
                });
            });
        },
        addToNamespace = (object, name="globalAddition") => {
            window.LS[name] = window.LS[name] || {};
            window.LS[name] = _.merge(window.LS[name], object);
        },
        setNameSpace = () => {
            const BaseNameSpace = {
                adminCore : {
                    refresh: refreshAdminCore,
                    onload: onLoadRegister,
                    appendToLoad: appendToLoad,
                    addToNamespace: addToNamespace,
                }
            };

            const pageLoadActions = {
                saveBindings,
                confirmationModal,
                questionEdit,
                confirmDeletemodal,
                fileManagerStyle,
                panelClickable,
                panelsAnimation,
                initNotification : notificationSystem.initNotification,
            }
            const LsNameSpace = _.merge(
                BaseNameSpace,
                globalWindowMethods,
                parameterGlobals,
                {AjaxHelper},
                {createUrl},
                autoCloseAlerts,
                ajaxAlertMethod,
                {ajaxAlerts},
                {EventBus},
                subquestionAndAnswersGlobalMethods,
                notificationSystem,
                gridAction
            );

            /*
            * Set the namespace to the global variable LS
            */
            window.LS = _.merge(window.LS, LsNameSpace, {pageLoadActions, ld: _, debug});

            /* Set a variable to test if browser have HTML5 form ability
            * Need to be replaced by some polyfills see #8009
            */
            window.hasFormValidation= typeof document.createElement( 'input' ).checkValidity == 'function';

        };


        setNameSpace();
        onLoadRegister();
}

AdminCore();