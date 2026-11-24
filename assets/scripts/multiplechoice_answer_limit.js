/**
 * 多选题「最多可选 N 项」的勾选拦截 —— 纯逻辑部分（无 DOM 依赖）。
 *
 * 拆成纯函数是为了能在 Node 里直接单测：判定规则一旦写错，
 * 用户要么被无故阻止，要么真的超选了却没人管。
 *
 * 全局对象名 LSMultiAnswerLimit，对应 assets/scripts/multiplechoice_answer_limit.js
 */
(function (root, factory) {
    if (typeof module === 'object' && module.exports) {
        module.exports = factory();
    } else {
        root.LSMultiAnswerLimit = factory();
    }
}(typeof globalThis !== 'undefined' ? globalThis
    : (typeof window !== 'undefined' ? window
    : (typeof self !== 'undefined' ? self : this)), function () {
    'use strict';

    /**
     * 把属性值解析成可用上限。
     *
     * max_answers 也允许写成表达式（如 {Q1.NAOK}），那种情况下浏览器端
     * 无法预先知道数值，必须视为"不限"，否则会误阻止用户。
     *
     * @param {*} raw
     * @returns {number|null} 正整数上限；不限时返回 null
     */
    function parseLimit(raw) {
        if (raw === null || raw === undefined) {
            return null;
        }
        var text = String(raw).trim();
        if (!/^\d+$/.test(text)) {
            return null;
        }
        var value = parseInt(text, 10);
        return value > 0 ? value : null;
    }

    /**
     * 判断是否允许勾选下一个选项。
     *
     * @param {number} selectedCount 当前已选个数
     * @param {number|null} maxAnswers 上限；null 表示不限
     * @returns {boolean}
     */
    function canSelect(selectedCount, maxAnswers) {
        var limit = parseLimit(maxAnswers);
        if (limit === null) {
            return true;
        }
        return selectedCount < limit;
    }

    /**
     * 生成给用户看的提示文案。
     *
     * 注意：真正的用户可见文案由 PHP 侧用 gT() 翻译后传入，
     * 这里只负责插值，避免在 JS 里写死可翻译文本。
     *
     * 模板里的 {limit} 会被替换成实际上限；同时兼容 %s 写法。
     *
     * @param {string} template 形如 "You can select at most {limit} answer(s)."
     * @param {number|null} maxAnswers
     * @returns {string}
     */
    function formatLimitMessage(template, maxAnswers) {
        var limit = parseLimit(maxAnswers);
        if (limit === null || typeof template !== 'string' || template === '') {
            return '';
        }
        return template.replace(/\{limit\}/g, String(limit)).replace('%s', String(limit));
    }

    /**
     * 拦截超限勾选，并在超限时给出可见提示。
     *
     * 依赖 jQuery（LimeSurvey 前端到处都在用），只在浏览器里调用；
     * 纯函数部分保持无依赖，方便 Node 单测。
     *
     * @param {number} qid      题目 ID
     * @param {number} max      最多可选个数
     * @param {string} template 提示文案模板，%s 会被替换成上限
     */
    function init(qid, max, template) {
        var limit = parseLimit(max);
        // 浏览器里 jQuery 由 LimeSurvey 保证存在；取不到就直接放弃（不报错打断其他脚本）
        var jq = (typeof globalThis !== 'undefined' && globalThis.jQuery) || root.jQuery;
        if (limit === null || !jq) {
            return;
        }
        var $ = jq;

        // 内联脚本可能在题目 DOM 渲染完成之前执行；若容器还不存在，
        // 等 document ready 后再试一次，而不是直接放弃。
        function bind() {
            var $container = $('#question' + qid + ' .ls-answers');
            if (!$container.length || $container.data('lsAnswerLimitBound')) {
                return;
            }
            $container.data('lsAnswerLimitBound', true);

            function selectedCount() {
                return $container.find('input[type=checkbox]:checked').length;
            }

            /**
             * 在指定题目容器前显示/更新提示。
             *
             * @param {object} $list 题目答案容器
             * @param {number} lim 该题上限
             */
            function showMessageFor($list, lim) {
                var id = $list.attr('data-question-id') || qid;
                var $msg = $('#ls-answer-limit-msg-' + id);
                if (!$msg.length) {
                    $msg = $('<div class="ls-multi-answer-limit-message alert alert-danger"></div>')
                        .attr('id', 'ls-answer-limit-msg-' + id)
                        .attr('role', 'alert');
                    // 放在题目容器内部靠前的位置：在 PJAX/主题布局里
                    // 直接插到 <ul> 前可能落在被隐藏的区块中
                    var $host = $list.closest('.ls-answers, .question-container, .answer-container');
                    if (!$host.length) {
                        $host = $list.parent();
                    }
                    $host.prepend($msg);
                }
                $msg.text(formatLimitMessage(template, lim));
                $msg.show();
            }

            function showMessage() {
                showMessageFor($container, limit);
            }

            function clearMessage() {
                $container.find('.ls-multi-answer-limit-message').hide();
            }

            // 提交拦截：页面里已有的答案可能已经超限（回填、多标签页、脚本绕过），
            // 点「下一题」时必须拦下，而不是一路走到问卷末尾才报错。
            var $form = $container.closest('form');
            if ($form.length && !$form.data('lsAnswerLimitGuard')) {
                $form.data('lsAnswerLimitGuard', true);
                $form.on('submit', function () {
                    var over = false;
                    $('ul.ls-answers[data-max-answers]').each(function () {
                        var $list = $(this);
                        var lim = parseLimit($list.attr('data-max-answers'));
                        if (lim === null) {
                            return;
                        }
                        if ($list.find('input[type=checkbox]:checked').length > lim) {
                            over = true;
                            showMessageFor($list, lim);
                        }
                    });
                    if (!over) {
                        return true;
                    }
                    // 阻止提交，并把用户视线带回第一个提示
                    var $msg = $('.ls-multi-answer-limit-message:visible').first();
                    if ($msg.length && $msg[0].scrollIntoView) {
                        $msg[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return false;
                });
            }

            $container.on('click', 'input[type=checkbox]', function (event) {
                var $box = $(this);
                if ($box.is(':checked') && !canSelect(selectedCount() - 1, limit)) {
                    // 取消本次勾选，让界面状态与"最多 N 项"保持一致
                    $box.prop('checked', false);
                    event.preventDefault();
                    showMessage();
                    return false;
                }
                clearMessage();
            });
        }

        function bindWhenReady() {
            if ($('#question' + qid + ' .ls-answers').length) {
                bind();
            } else {
                $(bind);
            }
        }

        if (typeof document !== 'undefined' && document.readyState === 'loading') {
            $(bindWhenReady);
        } else {
            bindWhenReady();
        }
    }

    return {
        parseLimit: parseLimit,
        canSelect: canSelect,
        formatLimitMessage: formatLimitMessage,
        init: init
    };
}));
