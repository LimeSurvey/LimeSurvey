<?php

/**
 * Question OpenApi Schema
 *
 *  @OA\Schema(
 *      schema="question_list",
 *      type="array",
 *      @OA\Items(ref="#/components/schemas/question_list_item"),
 *      example={
 *          {
 *              "id": "1",
 *              "question": "Question 1",
 *              "help": "This is a question help text.",
 *              "language": "en",
 *              "qid": "1",
 *              "parent_qid": "0",
 *              "sid": "712896",
 *              "type": "T",
 *              "title": "G01Q01",
 *              "preg": "",
 *              "other": "N",
 *              "mandatory": "N",
 *              "encrypted": "N",
 *              "question_order": "1",
 *              "scale_id": "0",
 *              "same_default": "0",
 *              "question_theme_name": "longfreetext",
 *              "modulename": null,
 *              "gid": "1",
 *              "relevance": "1",
 *              "same_script": "0"
 *          }
 *      }
 * )
 *
 * @OA\Schema(
 *      schema="question_list_item",
 *      type="object",
 *      @OA\Property(
 *          property="id",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="question",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="help",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="language",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="qid",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="parent_qid",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="sid",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="type",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="title",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="preg",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="other",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="mandatory",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="encrypted",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="question_order",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="scale_id",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="same_default",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="question_theme_name",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="modulename",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="gid",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="relevance",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="same_script",
 *          type="string"
 *      ),
 *      example={
 *          "id": "1",
 *          "question": "Question 1",
 *          "help": "This is a question help text.",
 *          "language": "en",
 *          "qid": "1",
 *          "parent_qid": "0",
 *          "sid": "712896",
 *          "type": "T",
 *          "title": "G01Q01",
 *          "preg": "",
 *          "other": "N",
 *          "mandatory": "N",
 *          "encrypted": "N",
 *          "question_order": "1",
 *          "scale_id": "0",
 *          "same_default": "0",
 *          "question_theme_name": "longfreetext",
 *          "modulename": null,
 *          "gid": "1",
 *          "relevance": "1",
 *          "same_script": "0"
 *      }
 * )
 *
 * @OA\Schema(
 *      schema="question_detail",
 *      type="object",
 *      @OA\Property(
 *          property="id",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="parent_qid",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="sid",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="gid",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="type",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="title",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="preg",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="other",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="mandatory",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="encrypted",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="question_order",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="scale_id",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="same_default",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="relevance",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="question_theme_name",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="modulename",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="same_script",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="available_answers",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="subquestions",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="attributes",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="attributes_lang",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="answeroptions",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="defaultvalue",
 *          type="string"
 *      ),
 *      example={
 *          "qid": "1",
 *          "parent_qid": "0",
 *          "sid": "712896",
 *          "gid": "1",
 *          "type": "T",
 *          "title": "G01Q01",
 *          "preg": "",
 *          "other": "N",
 *          "mandatory": "N",
 *          "encrypted": "N",
 *          "question_order": "1",
 *          "scale_id": "0",
 *          "same_default": "0",
 *          "relevance": "1",
 *          "question_theme_name": "longfreetext",
 *          "modulename": null,
 *          "same_script": "0",
 *          "available_answers": "No available answers",
 *          "subquestions": "No available answers",
 *          "attributes": {
 *              "cssclass": "",
 *              "display_rows": "",
 *              "em_validation_q": "",
 *              "hidden": "0",
 *              "hide_tip": "0",
 *              "input_size": "",
 *              "maximum_chars": "",
 *              "page_break": "0",
 *              "random_group": "",
 *              "statistics_graphtype": "0",
 *              "statistics_showgraph": "1",
 *              "text_input_width": "",
 *              "time_limit": "",
 *              "time_limit_action": "1",
 *              "time_limit_disable_next": "0",
 *              "time_limit_disable_prev": "0",
 *              "time_limit_message_delay": "",
 *              "time_limit_message_style": "",
 *              "time_limit_timer_style": "",
 *              "time_limit_warning": "",
 *              "time_limit_warning_2": "",
 *              "time_limit_warning_2_display_time": "",
 *              "time_limit_warning_2_style": "",
 *              "time_limit_warning_display_time": "",
 *              "time_limit_warning_style": ""
 *          },
 *          "attributes_lang": {
 *              "em_validation_q_tip": "",
 *              "time_limit_countdown_message": "",
 *              "time_limit_message": "",
 *              "time_limit_warning_2_message": "",
 *              "time_limit_warning_message": ""
 *          },
 *          "answeroptions": "No available answer options",
 *          "defaultvalue": null
 *      }
 * )
 *
 * @OA\Schema(
 *      schema="question_status_error_invalid_question_id",
 *      type="object",
 *      @OA\Property(
 *          property="status",
 *          type="string"
 *      ),
 *      example={
 *          "status": "Error: Invalid questionid"
 *      }
 * )
 */
class QuestionSchema
{
}
