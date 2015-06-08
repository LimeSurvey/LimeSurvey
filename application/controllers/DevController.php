<?php
namespace ls\controllers;
use Cake\Utility\Hash;
use Cake\Utility\Text;
use ls\expressionmanager\Parser;
use ls\expressionmanager\Tokenizer;
use \Yii;
class DevController extends \CController {
    public function accessRules() {
        return [['allow']];
    }
    public function actionRandomTest() {
        $size = 400;
        $image = imagecreate($size, $size);

        $background_color = imagecolorallocate($image, 0, 0, 0);

        $seed = Text::uuid();
        $white = imagecolorallocate($image, 255, 0, 0);
        $hash = md5($seed, true);
        for ($i = 0; $i < $size / 2; $i++) {
            for ($j = 0; $j< $size / 2 ; $j++) {
                $hash = md5($hash, true);
                $int = unpack('V', $hash)[1];
                $pixel = $int % ($size * $size);
                $x = $pixel % $size;
//                $x = $i;
                $y = intval($pixel / $size);
//                $y = $j;

                if (!imagesetpixel($image, $x, $y, $white)) {

                }
            }
        }
        header('Content-Type: image/png');
        imagepng($image);
    }

    public function actionSession() {
        $copy = $_SESSION;
        unset($copy['LSWebUsermodel']);
//        unset($copy['SSM']);
        unset($copy['fieldmap-652359en']);

        echo '<pre>';
        var_dump(App()->surveySessionManager->sessions->toArray());


    }

    protected function parseExpression(array &$tokens, array &$operandStack, array &$operatorStack)
    {
        /**
         * Check production rules:
         * LP => LP EXPR RP
         * NUM => NUM
         * NAME => NAME
         * OP => OP EXPR
         */
        $token = array_shift($tokens);
        echo $token[2] . '<br>';
        switch($token[2]) {
            case 'LP':

                while($this->parseExpression($tokens, $operandStack, $operatorStack)) {}
                $rp = array_shift($tokens);
                if ($rp[2] != 'RP'){
                    die("Expected RP, got {$rp[2]}");
                }
                break;
            case 'NUMBER':
            case 'WORD':
                array_push($operandStack, $token);
                break;
            case 'AND_OR':
            case 'COMPARE':
            case 'BINARYOP':
                $operand1 = array_pop($operandStack);
                array_push($operatorStack, $token);
                $this->parseExpression($tokens, $operandStack, $operatorStack);
                $operand2 = array_pop($operandStack);
                array_push($operandStack, [$token, $operand1, $operand2]);
                break;
            default:
                array_unshift($tokens, $token);
                return false;
        }
        return true;
    }

    protected function dumpAST($tree, $expression = null) {
        /* @var \ls\expressionmanager\Token $token */
        if (is_array($tree)) {
            $token = array_shift($tree);

            if ($token->context == 'FUNC') {
                $subtree = $tree[0];
            } else {
                $subtree = $tree;
            }
            $subContent = \CHtml::openTag('ul');
            foreach($subtree as $child) {
               $subContent .= \CHtml::tag('li', [], $this->dumpAST($child, $expression));
            }

            $subContent .= \CHtml::closeTag('ul');
//            die(htmlentities($subContent));
            return \CHtml::tag('ul', [], \CHtml::tag('li', [], $this->dumpAST($token, $expression) . $subContent));
        } else {
            if (is_bool($tree->value)) {
                $value = $tree->value ? 'true' : 'false';
            } elseif ($tree->value === "") {
                $value = "EMPTY STRING";
            } else {
                $value = $tree->value;
            }
            if (strlen($value) == 0) {
                var_dump($tree);
                die('no');
            }
            return \CHtml::link($value);
        }

    }

    public function actionIndex() {
        echo '<pre>';
        var_dump(get_declared_classes()); die();
        $parser = new Parser();

        $expressions = [
            'abs.test',
            '5 + 3',
            '4 * -2',
            'abs..'
        ];
        $tests  = <<<EOD
<B>Empty Vs. Empty</B>~"<B>Empty Vs. Empty</B>"
1~'' == ''
0~'' != ''
0~'' > ''
1~'' >= ''
0~'' < ''
1~'' <= ''
1~!''
~('' and '')
~('' or '')
<B>Empty Vs. Zero</B>~"<B>Empty Vs. Zero</B>"
0~'' == 0
1~'' != 0
0~'' > 0
0~'' >= 0
0~'' < 0
0~'' <= 0
1~!''
1~!0
0~('' and 0)
0~('' or 0)
<B>Empty Vs. Constant</B>~"<B>Empty Vs. Constant</B>"
0~'' == 3
1~'' != 3
0~'' > 3
0~'' >= 3
0~'' < 3
0~'' <= 3
1~!''
0~!3
0~('' and 3)
1~('' or 3)
<B>Empty Vs. Empty_Var</B>~"<B>Empty Vs. Empty_Var</B>"
1~'' == empty
0~'' != empty
0~'' > empty
1~'' >= empty
0~'' < empty
1~'' <= empty
1~!''
1~!empty
~('' and empty)
~('' or empty)
<B>Empty_Var Vs. Zero</B>~"<B>Empty_Var Vs. Zero</B>"
0~empty == 0
1~empty != 0
0~empty > 0
0~empty >= 0
0~empty < 0
0~empty <= 0
1~!empty
1~!0
0~(empty and 0)
0~(empty or 0)
<B>Empty_Var Vs. Zero</B>~"<B>Empty_Var Vs. Zero</B>"
0~empty == zero
1~empty != zero
0~empty > zero
0~empty >= zero
0~empty < zero
0~empty <= zero
1~!empty
1~!zero
0~(empty and zero)
0~(empty or zero)
<B>Empty_Var Vs. Constant</B>~"<B>Empty_Var Vs. Constant</B>"
0~empty == 3
1~empty != 3
0~empty > 3
0~empty >= 3
0~empty < 3
0~empty <= 3
1~!empty
0~!3
0~(empty and 3)
1~(empty or 3)
<B>Solution: Empty_Var Vs. Zero</B>~"<B>Solution: Empty_Var Vs. Zero</B>"
0~!is_empty(empty) && (empty == 0)
0~!is_empty(five) && (five == 0)
1~!is_empty(zero) && (zero == 0)
0~!is_empty(empty) && (empty > 0)
0~!is_empty(empty) && (empty >= 0)
0~!is_empty(empty) && (empty < 0)
0~!is_empty(empty) && (empty <= 0)
0~!is_empty(empty) && ((empty and 0))
0~!is_empty(empty) && ((empty or 0))
<B>Solution: Empty_Var Vs. Zero</B>~"<B>Solution: Empty_Var Vs. Zero</B>"
0~!is_empty(empty) && (empty == zero)
0~!is_empty(five) && (five == zero)
1~!is_empty(zero) && (zero == zero)
0~!is_empty(empty) && (empty > zero)
0~!is_empty(empty) && (empty >= zero)
0~!is_empty(empty) && (empty < zero)
0~!is_empty(empty) && (empty <= zero)
0~!is_empty(empty) && ((empty and zero))
0~!is_empty(empty) && ((empty or zero))
<B>Solution: Empty_Var Vs. Constant</B>~"<B>Solution: Empty_Var Vs. Constant</B>"
0~!is_empty(empty) && (empty < 3)
0~!is_empty(empty) && (empty <= 3)
<B>Solution: Empty_Var Vs. Variable</B>~"<B>Solution: Empty_Var Vs. Variable</B>"
0~!is_empty(empty) && (empty < five)
0~!is_empty(empty) && (empty <= five)
<B>Solution: The Hard One is Empty_Var != 0</B>~"<B>Solution: The Hard One is Empty_Var != 0</B>"
1~(empty != 0)
1~!is_empty(empty) && (empty != 0)
1~is_empty(empty) || (empty != 0)
1~is_empty(empty) || (empty != zero)
0~is_empty(zero) || (zero != 0)
1~is_empty(five) || (five != 0)
<b>SETUP</b>~'<b>SETUP</b>'
&quot;Can strings contain embedded \&quot;quoted passages\&quot; (and parentheses + other characters?)?&quot;~a=htmlspecialchars(ASSESSMENT_HEADING)
&quot;can single quoted strings&quot; . &#039;contain nested &#039;quoted sections&#039;?~b=htmlspecialchars(QUESTIONHELP)
Can strings have embedded &lt;tags&gt; like &lt;html&gt;, or even unbalanced &quot;quotes or entities without terminal semicolons like &amp;amp and  &amp;lt?~c=htmlspecialchars(QUESTION_HELP)
<span id="d" style="border-style: solid; border-width: 2px; border-color: green">Hi there!</span>~d='<span id="d" style="border-style: solid; border-width: 2px; border-color: green">Hi there!</span>'
<b>FUNCTIONS</b>~'<b>FUNCTIONS</b>'
5~abs(five)
5~abs(-five)
0.2~acos(cos(0.2))
0~acos(cos(pi()))-pi()
&quot;Can strings contain embedded \\&quot;quoted passages\\&quot; (and parentheses + other characters?)?&quot;~addslashes(a)
&quot;can single quoted strings&quot; . &#039;contain nested &#039;quoted sections&#039;?~addslashes(b)
Can strings have embedded &lt;tags&gt; like &lt;html&gt;, or even unbalanced &quot;quotes or entities without terminal semicolons like &amp;amp and  &amp;lt?~addslashes(c)
0.2~asin(sin(0.2))
0.2~atan(tan(0.2))
0~atan2(0,1)
1~ceil(0.3)
1~ceil(0.7)
0~ceil(-0.3)
0~ceil(-0.7)
10~ceil(9.1)
1~checkdate(1,29,1967)
0~checkdate(2,29,1967)
0.2~cos(acos(0.2))
5~count(1,2,3,4,5)
0~count()
5~count(one,two,three,four,five)
2~count(a,'',c)
NULL~date('F j, Y, g:i a',time())
April 5, 2006, 1:02 am~date('F j, Y, g:i a',mktime(1,2,3,4,5,6))
20~floor(exp(3))
0~floor(asin(sin(pi())))
9~floor(9.9)
3~floor(pi())
January 12, 2012, 5:27 pm~date('F j, Y, g:i a',1326410867)
January 12, 2012, 11:27 pm~gmdate('F j, Y, g:i a',1326410867)
"Can strings contain embedded \"quoted passages\" (and parentheses + other characters?)?"~html_entity_decode(a)
"can single quoted strings" . &#039;contain nested &#039;quoted sections&#039;?~html_entity_decode(b)
Can strings have embedded <tags> like <html>, or even unbalanced "quotes or entities without terminal semicolons like &amp and  &lt?~html_entity_decode(c)
&quot;Can strings contain embedded \&quot;quoted passages\&quot; (and parentheses + other characters?)?&quot;~htmlentities(a)
&quot;can single quoted strings&quot; . &#039;contain nested &#039;quoted sections&#039;?~htmlentities(b)
Can strings have embedded &lt;tags&gt; like &lt;html&gt;, or even unbalanced &quot;quotes or entities without terminal semicolons like &amp;amp and &amp;lt?~htmlentities(c)
1~c==htmlspecialchars(htmlspecialchars_decode(c))
1~b==htmlspecialchars(htmlspecialchars_decode(b))
1~a==htmlspecialchars(htmlspecialchars_decode(a))
"Can strings contain embedded \"quoted passages\" (and parentheses + other characters?)?"~htmlspecialchars_decode(a)
"can single quoted strings" . 'contain nested 'quoted sections'?~htmlspecialchars_decode(b)
Can strings have embedded like , or even unbalanced "quotes or entities without terminal semicolons like & and <?~htmlspecialchars_decode(c)
"Can strings contain embedded \"quoted passages\" (and parentheses + other characters?)?"~htmlspecialchars(a)
"can single quoted strings" . 'contain nested 'quoted sections'?~htmlspecialchars(b)
Can strings have embedded <tags> like <html>, or even unbalanced "quotes or entities without terminal semicolons like &amp and &lt?~htmlspecialchars(c)
9~idate('B',1326410867)
0~if('0',1,0)
0~if(0,1,0)
1~if(!0,1,0)
0~if(!(!0),1,0)
1~if('true',1,0)
1~if('false',1,0)
1~if('00',1,0)
0~if('',1,0)
1~if('A',1,0)
0~if(empty,1,0)
4~if(5 > 7,2,4)
1~if(' ',1,0)
there~if((one > two),'hi','there')
64~if((one < two),pow(2,6),pow(6,2))
H e l l o~implode(' ','H','e','l','l','o')
1|2|3|4|5~implode('|',one,two,three,four,five)
123~join(1,2,3)
123 5~join(one,2,three," ",five)
4~intval('4')
4~intval('100',2)
5~intval(5.7)
0~is_empty(four)
1~is_empty(empty)
1~is_empty('')
0~is_empty(0)
0~is_empty('0')
0~is_empty('false')
0~is_empty('NULL')
0~is_empty(1)
1~is_empty(one==two)
0~!is_empty(one==two)
1~is_float(half)
0~is_float(one)
1~is_float(pi())
0~is_float(5)
0~is_int(half)
1~is_int(one)
0~is_nan(half)
1~is_nan(WELCOME)
1~is_null(sdfjskdfj)
0~is_null(four)
0~is_numeric(empty)
1~is_numeric('1')
1~is_numeric(four)
0~is_numeric('hi')
1~is_numeric(five)
0~is_numeric(hi)
0~is_string(four)
1~is_string('hi')
1~is_string(hi)
1, 2, 3, 4, 5~list(one,two,three,min(four,five,six),max(three,four,five))
11, 12~list(eleven,twelve)
0, 1, 3, 5~list(0,one,'',three,'',five)
1~log(exp(1))
2~log(exp(2))
I was trimmed   ~ltrim('     I was trimmed   ')
10~max(5,6,10,-20)
6~max(five,(one + (two * four)- three))
6~max((one + (two * four)- three))
212~5 + max(1,(2+3),(4 + (5 + 6)),((7 + 8) + 9),((10 + 11), 12),(13 + (14 * 15) - 16))
29~five + max(one, (two + three), (four + (five + six)),((seven + eight) + nine),((ten + eleven), twelve),(one + (two * three) - four))
1024~max(one,(two*three),pow(four,five),six)
2~max(one,two)
5~max(one,two,three,four,five)
-5~min(-5,10,15,12,-3)
1~min(five,four,one,two,three)
1344765967~mktime(5,6,7,8)
1144191723~mktime(1,2,3,4,5,6)
1,000~number_format(1000)
1,000.23~number_format(1000.23)
1,234,567~number_format(1234567)
315~ceil(100*pi())
1~pi() == pi() * 2 - pi()
4~pow(2,2)
27~pow(3,3)
=~quoted_printable_decode(quoted_printable_encode('='))
\\$~quotemeta('$')
IGNORE THIS ERROR~rand(3,5)
0~(a=rand())-a
1~regexMatch('/embedded/',c)
1~regexMatch('/^.*embedded.*$/',c)
0~regexMatch('/joe/',c)
1~regexMatch('/(?:dog|cat)food/','catfood stinks')
1~regexMatch('/(?:dog|cat)food/','catfood stinks')
1~regexMatch('/[0-9]{3}-[0-9]{2}-[0-9]{4}/','123-45-6789')
1~regexMatch('/\d{3}-\d{2}-\d{4}/','123-45-6789')
1~regexMatch('/(?:\(\d{3}\))\s*\d{3}-\d{4}/','(212) 555-1212')
0~round(0.2)
1~round(.8)
0.07~0.01 + 0.06
0.07~round(0.01 + 0.06,10)
     I was trimmed~rtrim('     I was trimmed   ')
0.2~sin(asin(0.2))
1~sin(pi()/2)
1~sin(pi()/2) == sin(.5 * pi())
1~sin(0.5 * pi())
hello,5~sprintf('%s,%d','hello',5)
2~sqrt(4)
158~round(stddev(4,5,6,7,8)*100)
hello-----~str_pad('hello',10,'-')
hello     ~str_pad('hello',10)
hello~str_pad('hello',3)
testtesttest~str_repeat('test',3)
I am awesome~str_replace('You are','I am','You are awesome')
I love LimeSurvey~str_replace('like','love','I like LimeSurvey')
1~0==strcasecmp('Hello','hello')
0~0==strcasecmp('Hello','hi')
1~0==strcmp('Hello','Hello')
0~0==strcmp('Hello','hi')
Hi there!~c=strip_tags(d)
hello~strip_tags('<b>hello</b>')
5~stripos('ABCDEFGHI','f')
hi~stripslashes('\\h\\i')
FGHI~stristr('ABCDEFGHI','fg')
5~strlen('12345')
5~strlen(hi)
0~strpos('ABCDEFGHI','f')
5~strpos('ABCDEFGHI','F')
2~strpos('I like LimeSurvey','like')
54321~strrev('12345')
0~strstr('ABCDEFGHI','fg')
FGHI~strstr('ABCDEFGHI','FG')
hi there!~strtolower(c)
HI THERE!~strtoupper(c)
3600~strtotime("27 Mar 1976 8:20")-strtotime("1976/03/27 7:20")
10~(strtotime("13 Apr 2013")-strtotime("2013-04-03"))/60/60/24
1985-11-05 00:00:00~date("Y-m-d H:i:s",strtotime("05 Nov 1985"))
HOURS PASSED SINCE 1970~round(strtotime("now")/60/60)
~""
1985-11-05 00:00:00~date("Y-m-d H:i:s",strtotime("11/5/85"))
2010-08-09 00:00:00~date("Y-m-d H:i:s",strtotime("8/9/10"))
2010-08-09 00:00:00~date("Y-m-d H:i:s",strtotime("8/9/2010"))
2010-08-09 00:00:00~date("Y-m-d H:i:s",strtotime("2010/8/9"))
~""
1985-11-05 00:00:00~date("Y-m-d H:i:s",strtotime("85-11-5"))
2010-08-09 00:00:00~date("Y-m-d H:i:s",strtotime("10-8-9"))
2010-08-09 00:00:00~date("Y-m-d H:i:s",strtotime("9-8-2010"))
2010-08-09 00:00:00~date("Y-m-d H:i:s",strtotime("2010-8-9"))
~""
1985-11-05 00:53:20~date("Y-m-d H:i:s",strtotime("85-11-5 0:53:20"))
2010-08-09 00:53:20~date("Y-m-d H:i:s",strtotime("10-8-9 0:53:20"))
2010-08-09 11:12:13~date("Y-m-d H:i:s",strtotime("9-8-2010 11:12:13"))
2010-08-09 11:12:13~date("Y-m-d H:i:s",strtotime("2010-8-9 11:12:13"))
~""
Today 11:11:59~date("Y-m-d H:i:s",strtotime("11.11.59"))
Today 9:08:10~date("Y-m-d H:i:s",strtotime("9.8.10"))
2010-08-09 00:00:00~date("Y-m-d H:i:s",strtotime("9.8.2010"))
~""
1985-11-05 00:53:20~date("Y-m-d H:i:s",strtotime("5.11.85 0:53:20"))
2010-08-09 11:12:13~date("Y-m-d H:i:s",strtotime("9.8.2010 11:12:13"))
~""
1970-01-01 00:00:00~date("Y-m-d H:i:s",strtotime("70-01-01"))
1999-01-01 00:00:00~date("Y-m-d H:i:s",strtotime("99-01-01"))
2001-01-01 00:00:00~date("Y-m-d H:i:s",strtotime("01-01-01"))
1902-01-01 00:00:00~date("Y-m-d H:i:s",strtotime("1902-01-01"))
~""
today 2:15:00~date("Y-m-d H:i:s",strtotime("2:15:00"))
Some dates that are not (correctly) parsed:~"Some dates that are not (correctly) parsed:"
1969-01-19 00:00:00~date("Y-m-d H:i:s",strtotime("69-01-19"))
1985-11-05 00:00:00~date("Y-m-d H:i:s",strtotime("85/11/5"))
1985-11-05 00:00:00~date("Y-m-d H:i:s",strtotime("5-11-85"))
2010-08-09 00:00:00~date("Y-m-d H:i:s",strtotime("2010.8.9"))
1985-11-05 00:00:00~date("Y-m-d H:i:s",strtotime("85.11.5"))
1985-11-05 00:53:20~date("Y-m-d H:i:s",strtotime("85.11.5 0:53:20"))
2010-08-09 11:12:13~date("Y-m-d H:i:s",strtotime("9.8.10 11:12:13"))
678~substr('1234567890',5,3)
15~sum(1,2,3,4,5)
15~sum(one,two,three,four,five)
0.2~tan(atan(0.2))
IGNORE THIS ERROR~time()
I was trimmed~trim('     I was trimmed   ')
Hi There You~ucwords('hi there you')
<b>EXPRESSIONS</b>~'<b>EXPRESSIONS</b>'
1~!'0'
1~0 eq '0'
0~0 ne '0'
0~0 eq empty
1~0 ne empty
0~0 eq ''
1~0 ne ''
0~'' < 10
0~0 < empty
1~0 <= empty
0~0 > empty
1~0 >= empty
0~'0' eq empty
1~'0' ne empty
0~'0' < empty
1~'0' <= empty
0~'0' > empty
1~'0' >= empty
1~empty eq empty
0~empty ne empty
0~'' > 0
0~' ' > 0
1~!0
0~!' '
0~!'A'
0~!1
0~!'1'
1~!''
1~!empty
1~'0'==0
0~'A'>0
0~'A'<0
0~'A'==0
0~'A'>=0
0~'A'<=0
0~0>'A'
0~0>='B'
0~0=='C'
0~0<'D'
0~0<='E'
1~0!='F'
1~'A' or 'B'
1~'A' and 'B'
0~'A' eq 'B'
1~'A' ne 'B'
1~'A' < 'B'
1~'A' <= 'B'
0~'A' > 'B'
0~'A' >= 'B'
AB~'A' + 'B'
NAN~'A' - 'B'
NAN~'A' * 'B'
NAN~'A' / 'B'
1~'A' or empty
0~'A' and empty
0~'A' eq empty
1~'A' ne empty
0~'A' < empty
0~'A' <= empty
1~'A' > empty
1~'A' >= empty
A~'A' + empty
NAN~'A' - empty
NAN~'A' * empty
NAN~'A' / empty
0~0 or empty
0~0 and empty
0~0 + empty
0~0 - empty
0~0 * empty
NAN~0 / empty
0~(-1 > 0)
0~zero
~empty
1~five > zero
1~five > empty
1~empty < 16
1~zero == empty
3~q5pointChoice.code
5~q5pointChoice.type
(question for q5pointChoice)~q5pointChoice.question
1~q5pointChoice.relevance
4~q5pointChoice.NAOK + 1
NULL~q5pointChoice.bogus
14~q5pointChoice.qid
7~qArrayNumbers_ls1_min.code
1~(one * (two + (three - four) + five) / six)
2.4~(one  * two) + (three * four) / (five * six)
50~12X34X56 * 12X3X5lab1_ber
1~c == 'Hi there!'
1~c == "Hi there!"
3~a=three
3~c=a
12~c*=four
15~c+=a
5~c/=a
-1~c-=six
24~one * two * three * four
-4~five - four - three - two
0~two * three - two - two - two
4~two * three - two
105~5 + 1, 7 * 15
7~7
15~10 + 5
24~12 * 2
10~13 - 3
3.5~14 / 4
5~3 + 1 * 2
1~one
there~hi
6.25~one * two - three / four + five
1~one + hi
1~two > one
1~two gt one
1~three >= two
1~three ge  two
0~four < three
0~four lt three
0~four <= three
0~four le three
0~four == three
0~four eq three
1~four != three
0~four ne four
NAN~one * hi
0~a='hello',b='',c=0
hello~a
0~c
0~one && 0
0~two and 0
1~five && 6
1~seven && eight
1~one or 0
1~one || 0
1~(one and 0) || (two and three)
value for {QID}~QID
"Can strings contain embedded \"quoted passages\" (and parentheses + other characters?)?"~ASSESSMENT_HEADING
"can single quoted strings" . 'contain nested 'quoted sections'?~QUESTIONHELP
Can strings have embedded <tags> like <html>, or even unbalanced "quotes or entities without terminal semicolons like &amp and  &lt?~QUESTION_HELP
value for {TOKEN:FIRSTNAME}~TOKEN:FIRSTNAME
value for {THEREAREXQUESTIONS}~THEREAREXQUESTIONS
15~12X3X5lab1_ber#1
1~three == three
1~three == 3
11~eleven
144~twelve * twelve
0~!three
8~five + + three
2~five + - three
<b>SYNTAX ERRORS</b>~'<b>SYNTAX ERRORS</b>'
NULL~*
NULL~three +
NULL~four * / seven
NULL~(five - three
NULL~five + three)
NULL~seven + = four
NULL~>
NULL~five > > three
NULL~seven > = four
NULL~seven >=
NULL~three &&
NULL~three ||
NULL~three +
NULL~three >=
NULL~three +=
NULL~three !
NULL~three *
NULL~five ! three
NULL~(5 + 7) = 8
NULL~&& four
NULL~min(
NULL~max three, four, five)
NULL~three four
NULL~max(three,four,five) six
NULL~WELCOME='Good morning'
NULL~TOKEN:FIRSTNAME='Tom'
NULL~NUMBEROFQUESTIONS+=3
NULL~NUMBEROFQUESTIONS*=4
NULL~NUMBEROFQUESTIONS/=5
NULL~NUMBEROFQUESTIONS-=6
NULL~'Tom'='tired'
NULL~max()
NULL~convert_value( 10, 1, '0,5,10,15,20', '0,5,10,15')
100~convert_value( 10, 1, '0,5,10,15,20', '0,50,100,150,200')
NULL~convert_value( 10, 0, '0,5,10,15,20', '0,50,100,150,200')
100~convert_value( 8, 0, '0,5,10,15,20', '0,50,100,150,200')
100~convert_value( 12, 0, '0,5,10,15,20', '0,50,100,150,200')
0~convert_value( 0, 0, '0,5,10,15,20', '0,50,100,150,200')
0~convert_value( -10000, 0, '0,5,10,15,20', '0,50,100,150,200')
NULL~convert_value( -10000, 1, '0,5,10,15,20', '0,50,100,150,200')
200~convert_value( 20, 0, '0,5,10,15,20', '0,50,100,150,200')
200~convert_value( 20, 1, '0,5,10,15,20', '0,50,100,150,200')
200~convert_value( 30, 0, '0,5,10,15,20', '0,50,100,150,200')
NULL~convert_value( 30, 1, '0,5,10,15,20', '0,50,100,150,200')
EOD;


//        die();
        $expressions = [];
        foreach(explode("\n", $tests) as $testDef) {
            if (false === $pos = strpos($testDef, '~')) {
                echo htmlentities($testDef) . '<br>';
                continue;
            } elseif ($pos > 0) {
                list($expected, $expressions[]) = explode('~', $testDef, 2);
            } else {
                $expressions[] = substr($testDef, 1);
            }
        }
//        $expressions = [];
        $expressions = array_merge($expressions, [
            '5 + 87',

            'test(3, 5)',
            'test(3, 4 + 5)'
//            'max(1,(2+3),(4 + (5 + 6)),((7 + 8) + 9))',
//            'max(1,(2+3),(4 + (5 + 6)),((7 + 8) + 9),((10 + 11), 12),(13 + (14 * 15) - 16))',
//            '5 + max(1,(2+3),(4 + (5 + 6)),((7 + 8) + 9),(10 + 11), 12,(13 + (14 * 15) - 16))',
//            '!false',
//            '(-five)',
//            'abs(-five)',
//            'acos(cos(pi()))-pi()',

        ]);

        $outputs = [];

        foreach($expressions as $expression) {
            ob_start();
            echo "******************************************<br>";
            echo $expression ."<br>";
            echo "------------------------------------------<br>";
            echo '<pre>';
            $ast = $parser->parse($expression);
            echo '</pre>';
            $outputs[] = [
                'debug' => ob_get_clean(),
                'tree' => isset($ast) ? \CHtml::tag('div', ['class' => 'tree'], $this->dumpAST($ast, $expression)) : 'no tree'
            ];
        }
        ob_get_clean();
        App()->getClientScript()->registerCssFile('/styles/ast.css');
        $result = '';
        foreach($outputs as $output) {
            $result .= $output['debug'] . $output['tree'];
        }
        $this->renderText($result);
        die();


    }

    public function actionMigrateTest() {
        App()->loadHelper('globalsettings');
        var_dump(getUpdateInfo());
        $zip = new \ZipArchive();
        $zip->open('/home/sam/Downloads/limesurvey205plus-build150211.zip');
        $zip2 = new \ZipArchive();
        $zip2->open('/tmp/new.zip');
        
        // Hashes:
        $hashesFrom = [];
        $hashesTo = [];
        $count = $zip->numFiles;
        $start = microtime(true);
        for ($i = 0; $i < $count; $i++) {
            $hashesFrom[$zip->getNameIndex($i)] = md5($zip->getFromIndex($i));
        }
        $count2 = $zip2->numFiles;
        
        var_dump($count2);
        for ($i = 0; $i < $count2; $i++) {
            $hashesTo[$zip2->getNameIndex($i)] = md5($zip2->getFromIndex($i));
        }
        
        // Deleted files:
        $deleted = array_diff_key($hashesFrom, $hashesTo);
        
        // Created files:
        $created = array_diff_key($hashesTo, $hashesFrom);
        
        // Changed files:
        $changed = [];
        foreach($hashesFrom as $file => $hash) {
            if (isset($hashesTo[$file]) && $hashesTo[$file] != $hash) {
                $changed[$file] = $hash;
            }
        }
         
        echo "Deleted " . count($deleted) ." files.<br>";
//        var_dump($deleted);
        echo "Created " . count($created) ." files.<br>";
//        var_dump($created);
        echo "Changed " . count($changed) ." files.<br>";
//        var_dump($changed);
        echo "Source files: " . $zip->numFiles;
        echo "Target files: " . $zip2->numFiles;
        
        $end = microtime(true) - $start;
        var_dump($end);

        
    }
    
    public function actionModule() {
        $id = array_keys(App()->modules)[0];
//        die(App()->createUrl("$id/dashboard"));
    }
}