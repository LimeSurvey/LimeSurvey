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
     * 判断题目是否参与校验。
     *
     * LimeSurvey 用 .ls-irrelevant / .ls-hidden 标记当前不展示的题目；
     * 这类题目的历史答案不应阻止用户继续（否则用户会卡在一个看不见的错误上）。
     *
     * @param {{hasHiddenAncestor: boolean}} state
     * @returns {boolean}
     */
    function isQuestionActive(state) {
        return !(state && state.hasHiddenAncestor);
    }

    /**
     * 该题是否应当在浏览器端被拦截。
     *
     * @param {{limit: (number|null), active: boolean}} state
     * @returns {boolean}
     */
    function shouldEnforceLimit(state) {
        if (!state) {
            return false;
        }
        return parseLimit(state.limit) !== null && isQuestionActive({ hasHiddenAncestor: !state.active });
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

        /**
         * 题目当前是否展示。
         *
         * LimeSurvey 的「不相关」有两种粒度：
         *   - 题目级：question 容器带 .ls-irrelevant
         *   - 选项级：单个 <li> 带 .ls-irrelevant（子题相关/array_filter 排除了这一项）
         * 两者都要考虑，否则一个看不见的选项仍会把计数顶到上限之外。
         *
         * @param {object} $list
         * @returns {boolean}
         */
        function isActive($list) {
            return isQuestionActive({
                hasHiddenAncestor: $list.closest('.ls-hidden').length > 0
                    || $list.closest('.question-container.ls-irrelevant').length > 0,
            });
        }

        /**
         * 统计实际可选的已勾选项数量（排除被标记为不相关的选项）。
         *
         * @param {object} $list
         * @returns {number}
         */
        function countActiveChecked($list) {
            return $list.find('input[type=checkbox]:checked').filter(function () {
                return $(this).closest('li').not('.ls-irrelevant').length > 0;
            }).length;
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
                var $host = $list.closest('.question-container, .answer-container') || $list.parent();
                $host.prepend($msg);
            }
            $msg.text(formatLimitMessage(template, lim));
            $msg.show();
        }

        /**
         * 绑定这一道题的点击拦截。
         *
         * 每道限题各自调用一次 init，因此这里是"一题一绑"，
         * 而不是全局只绑一次 —— 后者会导致同页只有最后一道题生效。
         *
         * @returns {void}
         */
        function bind() {
            var $container = $('#question' + qid + ' .ls-answers');
            if (!$container.length || $container.data('lsAnswerLimitBound')) {
                return;
            }
            $container.data('lsAnswerLimitBound', true);

            $container.on('click', 'input[type=checkbox]', function (event) {
                var $box = $(this);
                if (!$box.is(':checked')) {
                    return true;
                }
                if (!shouldEnforceLimit({ limit: limit, active: isActive($container) })) {
                    return true;
                }
                var selected = countActiveChecked($container);
                if (!canSelect(selected - 1, limit)) {
                    // 取消本次勾选，让界面状态与"最多 N 项"保持一致
                    $box.prop('checked', false);
                    event.preventDefault();
                    showMessageFor($container, limit);
                    return false;
                }
                $('#ls-answer-limit-msg-' + ($container.attr('data-question-id') || qid)).hide();
                return true;
            });

            bindSubmitGuard($container);
        }

        /**
         * 提交守卫：页面里已有的答案可能已经超限（回填、多标签页、脚本绕过），
         * 点「下一题」时必须拦下，而不是一路走到问卷末尾才报错。
         *
         * 整个 form 只需要挂一次监听，因此用 data 标记做幂等。
         *
         * @param {object} $container
         * @returns {void}
         */
        function bindSubmitGuard($container) {
            var $form = $container.closest('form');
            if (!$form.length || $form.data('lsAnswerLimitGuard')) {
                return;
            }
            $form.data('lsAnswerLimitGuard', true);
            $form.on('submit', function () {
                var over = false;
                $('ul.ls-answers[data-max-answers]').each(function () {
                    var $list = $(this);
                    var lim = parseLimit($list.attr('data-max-answers'));
                    if (lim === null) {
                        return;
                    }
                    // 不相关/隐藏的题目带着历史答案时不应阻止用户继续，
                    // 否则用户会卡在一个看不见的错误上且没有任何提示
                    if (!isActive($list)) {
                        return;
                    }
                    if (countActiveChecked($list) > lim) {
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

        // 内联脚本可能在题目 DOM 渲染完成之前执行；若容器还不存在，
        // 等 document ready 后再试一次，而不是直接放弃。
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
        isQuestionActive: isQuestionActive,
        shouldEnforceLimit: shouldEnforceLimit,
        init: init
    };
}));
