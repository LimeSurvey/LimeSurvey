<?php

/**
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
 *          type="object",
 *          @OA\Property(
 *              property="cssclass",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="display_rows",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="em_validation_q",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="hidden",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="hide_tip",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="input_size",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="maximum_chars",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="page_break",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="random_group",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="statistics_graphtype",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="statistics_showgraph",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="text_input_width",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_action",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_disable_next",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_disable_prev",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_message_delay",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_message_style",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_timer_style",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_warning",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_warning_2",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_warning_2_display_time",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_warning_2_style",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_warning_display_time",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_warning_style",
 *              type="string"
 *          )
 *      ),
 *      @OA\Property(
 *          property="attributes_lang",
 *          type="object",
 *          @OA\Property(
 *              property="em_validation_q_tip",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_countdown_message",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_message",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_warning_2_message",
 *              type="string"
 *          ),
 *          @OA\Property(
 *              property="time_limit_warning_message",
 *              type="string"
 *          )
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
 */
