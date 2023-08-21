# FunctionStatic

Expression Script: allow to make any expression static for using in other expression

## Usage

The plugin can be used any expression, it just return a static value of this expression. Can be used to test update of current answer

The function getStatic get 1 parameter : the expression

Some sample `getStatic(self.shown)`, usage example : `{if(getStatic(self.shown) == self.shown, "You update current answer")}`
