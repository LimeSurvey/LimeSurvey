# ExpressionAnswerOptions

Expression Script: make answer option text available inside survey by expression function.

## Usage

The plugin can be used on single choice question and array question , all question with realted answers editable by administrator.

The function getAnswerOptionText get 3 parameters

1. The question id or title
2. The answer code to get
3. The scale (for array dual scale question)

Some sample `getAnswerOptionText(self.qid,"A1")`, `getAnswerOptionText("Qcode","A1")` or `getAnswerOptionText(Qcode_SQ01.qid,"A1")`.
