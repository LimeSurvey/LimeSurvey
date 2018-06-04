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

//import page wise functionality
import * as questionEdit from './pages/questionEditing';
import * as quickAction from './pages/quickaction';
import {subquestionAndAnswersGlobalMethods} from './pages/subquestionandanswers';
import {onExistBinding as surveyGrid} from './pages/surveyGrid';

//import parts for globalscope
import * as confirmationModal from './parts/confirmationModal'; 
import {globalStartUpMethods, globalWindowMethods} from './parts/globalMethods';
import * as notifyFader from './parts/notifyFader';
import * as AjaxHelper from './parts/ajaxHelper';

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
            appendToLoad(confirmationModal);
            appendToLoad(questionEdit);
        },
        appendToLoad = (fn, event, root) => {
            event = event || 'ready pjax:scriptcomplete';
            root = root || 'document';
            
            eventsBound[root] = eventsBound[root] || [];
            
            if(_.find(eventsBound[root], {fn, event, root}) === undefined) {
                eventsBound[root].push({fn, event, root});
                $(root).on(event+'.admincore', fn);
            }

        },
        refreshAdminCore = () => {
            _.each(eventsBound, (eventMap, root) => {
                _.each(eventMap, (evItem) => {
                    $(evItem.root).off(evItem.event+'.admincore');
                    $(evItem.root).on(evItem.event+'.admincore', evItem.fn);
                });
            });
            surveyGrid();
            console.ls.log("Refreshed Admin core methods");
        },
        setNameSpace = () => {
            const BaseNameSpace = {
                adminCore : {
                    refresh: refreshAdminCore,
                    onload: onLoadRegister,
                    appendToLoad: appendToLoad
                }
            };
            const LsNameSpace = _.merge(BaseNameSpace, globalWindowMethods, AjaxHelper, notifyFader, subquestionAndAnswersGlobalMethods);
            
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
}

AdminCore();