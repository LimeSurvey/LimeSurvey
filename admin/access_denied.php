<?php
/*
 * LimeSurvey
 * Copyright (C) 2007 The LimeSurvey Project Team / Carsten Schmitz
 * All rights reserved.
 * License: GNU/GPL License v2 or later, see LICENSE.php
 * LimeSurvey is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * $Id$
 */


if (!isset($dbprefix) || isset($_REQUEST['dbprefix'])) {die("Cannot run this script directly (access_denied)");}

if (isset($_SESSION['loginID']))
{

    $accesssummary = "<p><strong>".$clang->gT("Access denied!")."</strong><br />\n";

    $action=returnglobal('action');
    if  (  $action == "dumpdb"  )
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed dump the database!")."<br />";
        $accesssummary .= "<a href='$scriptname'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "dumplabel")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed export a label set!")."<br />";
        $accesssummary .= "<a href='$scriptname'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "edituser")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to change user data!");
        $accesssummary .= "<br /><br /><a href='$scriptname?action=editusers'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "newsurvey")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to create new surveys!")."<br />";
        $accesssummary .= "<a href='$scriptname'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "deletesurvey")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to delete this survey!")."<br />";
        $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "addquestion")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to add new questions for this survey!")."<br />";
        $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "activate")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to activate this survey!")."<br />";
        $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "deactivate")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to deactivate this survey!")."<br />";
        $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "addgroup")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to add a group to this survey!")."<br />";
        $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "ordergroups")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to order groups in this survey!")."<br />";
        $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "editsurvey")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to edit this survey!")."</p>";
        $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "editgroup")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to edit groups in this survey!")."</p>";
        $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "browse_response" || $action == "listcolumn" || $action == "vvexport" || $action == "vvimport")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to browse responses!")."</p>";
        $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "assessment")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to set assessment rules!")."</p>";
        $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "delusergroup")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to delete this group!")."</p>";
        $accesssummary .= "<a href='$scriptname?action=editusergroups'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "importsurvey")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to import a survey!")."</p>";
        $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }

    elseif($action == "importgroup")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to import a group!")."</p>";
        $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "importquestion")
    {
        $accesssummary .= "<p>".$clang->gT("You are not allowed to to import a question!")."</p>";
        $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "CSRFwarn")
    {
        $sURLID='';
        if (isset($sid)) {
            $sURLID="?sid={$sid}";
        }
        $accesssummary .= "<p><span color='errortitle'>".$clang->gT("Security alert")."</span>: ".$clang->gT("Someone may be trying to use your LimeSurvey session (CSRF attack suspected). If you just clicked on a malicious link, please report this to your system administrator.").'<br>'.$clang->gT('Also this problem can occur when you are working/editing in LimeSurvey in several browser windows/tabs at the same time.')."</p>";
        $accesssummary .= "<a href='{$scriptname}{$sURLID}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    elseif($action == "FakeGET")
    {
        $accesssummary .= "<p><span class='errortitle'>".$clang->gT("Security alert")."</span>: ".$clang->gT("Someone may be trying to use your LimeSurvey session by using dangerous GET requests (CSRF attack suspected). If you just clicked on a malicious link, please report this to your system administrator.").'<br>'.$clang->gT('Also this problem can occur when you are working/editing in LimeSurvey in several browser windows/tabs at the same time.')."</p>";
        $accesssummary .= "<a href='$scriptname?sid={$sid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
    }
    else
    {
        $accesssummary .= "<br />".$clang->gT("You are not allowed to perform this operation!")."<br />\n";
        if(!empty($sid))
        {
            $accesssummary .= "<br /><br /><a href='$scriptname?sid=$sid>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        elseif(!empty($ugid))
        {
            $accesssummary .= "<br /><br /><a href='$scriptname?action=editusergroups&ugid={$ugid}'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
        else
        {
            $accesssummary .= "<br /><br /><a href='$scriptname'>".$clang->gT("Continue")."</a><br />&nbsp;\n";
        }
    }

}
?>
