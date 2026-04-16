<?php

namespace LimeSurvey\Helpers\Update;

use Exception;

class Update_133 extends DatabaseUpdateBase
{
    public function up()
    {
        addColumn('{{users}}', 'one_time_pw', 'binary');
        // Add new assessment setting
        addColumn('{{surveys}}', 'assessments', "string(1) NOT NULL DEFAULT 'N'");
        // add new assessment value fields to answers & labels
        addColumn('{{answers}}', 'assessment_value', "integer NOT NULL DEFAULT '0'");
        addColumn('{{labels}}', 'assessment_value', "integer NOT NULL DEFAULT '0'");

        $this->applyUpdates();

        // activate assessment where assessment rules exist
        $this->db->createCommand(
            "UPDATE {{surveys}} SET assessments='Y' where sid in (SELECT sid FROM {{assessments}} group by sid)"
        )->execute();
        // add language field to assessment table
        addColumn('{{assessments}}', 'language', "string(20) NOT NULL DEFAULT 'en'");
        // update language field with default language of that particular survey
        $this->db->createCommand(
            "UPDATE {{assessments}} SET language=(select language from {{surveys}} where sid={{assessments}}.sid)"
        )->execute();
        // drop the old link field
        dropColumn('{{assessments}}', 'link');

        // Add new fields to survey language settings
        addColumn('{{surveys_languagesettings}}', 'surveyls_url', "string");
        addColumn('{{surveys_languagesettings}}', 'surveyls_endtext', 'text');
        // copy old URL fields ot language specific entries
        $this->db->createCommand(
            "UPDATE {{surveys_languagesettings}} set surveyls_url=(select url from {{surveys}} where sid={{surveys_languagesettings}}.surveyls_survey_id)"
        )->execute();
        // drop old URL field
        dropColumn('{{surveys}}', 'url');
    }

    public function applyUpdates()
    {
        // copy any valid codes from code field to assessment field
        switch ($this->db->driverName) {
            case 'mysql':
                $this->db->createCommand(
                    "UPDATE {{answers}} SET assessment_value=CAST(`code` as SIGNED) where `code` REGEXP '^-?[0-9]+$'"
                )->execute();
                $this->db->createCommand(
                    "UPDATE {{labels}} SET assessment_value=CAST(`code` as SIGNED) where `code` REGEXP '^-?[0-9]+$'"
                )->execute();
                // copy assessment link to message since from now on we will have HTML assignment messages
                $this->db->createCommand(
                    "UPDATE {{assessments}} set message=concat(replace(message,'/''',''''),'<br /><a href=\"',link,'\">',link,'</a>')"
                )->execute();
                break;
            case 'sqlsrv':
            case 'dblib':
            case 'mssql':
                try {
                    $this->db->createCommand(
                        "UPDATE {{answers}} SET assessment_value=CAST([code] as int) WHERE ISNUMERIC([code])=1"
                    )->execute();
                    $this->db->createCommand(
                        "UPDATE {{labels}} SET assessment_value=CAST([code] as int) WHERE ISNUMERIC([code])=1"
                    )->execute();
                } catch (\Exception $e) {
                };
                // copy assessment link to message since from now on we will have HTML assignment messages
                \alterColumn('{{assessments}}', 'link', "text", false);
                \alterColumn('{{assessments}}', 'message', "text", false);
                $this->db->createCommand(
                    "UPDATE {{assessments}} set message=replace(message,'/''','''')+'<br /><a href=\"'+link+'\">'+link+'</a>'"
                )->execute();
                break;
            case 'pgsql':
                $this->db->createCommand(
                    "UPDATE {{answers}} SET assessment_value=CAST(code as integer) where code ~ '^[0-9]+'"
                )->execute();
                $this->db->createCommand(
                    "UPDATE {{labels}} SET assessment_value=CAST(code as integer) where code ~ '^[0-9]+'"
                )->execute();
                // copy assessment link to message since from now on we will have HTML assignment messages
                $this->db->createCommand(
                    "UPDATE {{assessments}} set message=replace(message,'/''','''')||'<br /><a href=\"'||link||'\">'||link||'</a>'"
                )->execute();
                break;
        }
    }
}
