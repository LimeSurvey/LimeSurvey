/**
 * multiplechoice_answer_limit.js 的单元测试（无需浏览器）。
 *
 * 运行: node --test tests/js/multiplechoice_answer_limit.test.js
 *
 * 注意顺序：这里先写测试、后写实现，是为了让每条规则都"先红再绿"，
 * 而不是写完代码再补一个恰好通过的断言。
 */
'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const { parseLimit, canSelect, formatLimitMessage } = require('../../assets/scripts/multiplechoice_answer_limit.js');

test('parseLimit 接受纯数字上限', () => {
    assert.equal(parseLimit('2'), 2);
    assert.equal(parseLimit(' 3 '), 3);
    assert.equal(parseLimit(5), 5);
});

test('parseLimit 对空值/表达式/非法值返回 null（视为不限）', () => {
    assert.equal(parseLimit(null), null);
    assert.equal(parseLimit(undefined), null);
    assert.equal(parseLimit(''), null);
    assert.equal(parseLimit('{Q1.NAOK}'), null, '表达式无法在浏览器端静态求值，必须视为不限');
    assert.equal(parseLimit('0'), null, '0 不是有效的上限');
    assert.equal(parseLimit('-1'), null);
    assert.equal(parseLimit('2.5'), null);
});

test('canSelect 在未达上限时允许勾选', () => {
    assert.equal(canSelect(0, 2), true);
    assert.equal(canSelect(1, 2), true);
});

test('canSelect 达到上限后阻止继续勾选', () => {
    assert.equal(canSelect(2, 2), false, '已选 2 个、上限 2 时不应再允许勾选');
    assert.equal(canSelect(3, 2), false);
});

test('canSelect 在上限为空时永远允许', () => {
    assert.equal(canSelect(0, null), true);
    assert.equal(canSelect(99, null), true);
    assert.equal(canSelect(99, '{Q1.NAOK}'), true, '表达式上限不参与浏览器端拦截');
});

test('formatLimitMessage 用上限替换 {limit} 占位符（PHP 侧传入的格式）', () => {
    assert.equal(
        formatLimitMessage('You can select at most {limit} answer(s).', 2),
        'You can select at most 2 answer(s).'
    );
});

test('formatLimitMessage 兼容 %s 占位符', () => {
    assert.equal(formatLimitMessage('最多选择 %s 项', 2), '最多选择 2 项');
});

test('formatLimitMessage 在无上限或无模板时返回空串', () => {
    assert.equal(formatLimitMessage('最多选择 %s 项', null), '');
    assert.equal(formatLimitMessage('', 2), '');
    assert.equal(formatLimitMessage(undefined, 2), '');
});
