<?php

    /**
     * TODO: Why is it here? Move it to root tests folder!
     */
class ExpressionManagerTest extends CTestCase
{
    /**
     *
     * @var ExpressionManager
     */
    protected $em;

    public function setUp(): void
    {
        parent::setUp();
        Yii::import('application.helpers.expressions.em_core_helper', 'true');
        if (!function_exists('gT')) {
            // Create gT function that ExpressionManager uses (but ideally should not).
            eval('function gT() { }');
        }
        $this->em = new ExpressionManager();
    }

//      public function testVariables()
//      {
//          $vars = array(
//              'one' => array('sgqa'=>'one', 'code'=>1, 'jsName'=>'java_one', 'readWrite'=>'Y', 'gseq'=>2,'qseq'=>4),
//              'two' => array('sgqa'=>'two', 'code'=>2, 'jsName'=>'java_two', 'readWrite'=>'Y', 'gseq'=>2,'qseq'=>4),
//              'three' => array('sgqa'=>'three', 'code'=>3, 'jsName'=>'java_three', 'readWrite'=>'Y', 'gseq'=>2,'qseq'=>4),
//              'four' => array('sgqa'=>'four', 'code'=>4, 'jsName'=>'java_four', 'readWrite'=>'Y', 'gseq'=>2,'qseq'=>1),
//              'five' => array('sgqa'=>'five', 'code'=>5, 'jsName'=>'java_five', 'readWrite'=>'Y', 'gseq'=>2,'qseq'=>1),
//              'six' => array('sgqa'=>'six', 'code'=>6, 'jsName'=>'java_six', 'readWrite'=>'Y', 'gseq'=>2,'qseq'=>1),
//              'seven' => array('sgqa'=>'seven', 'code'=>7, 'jsName'=>'java_seven', 'readWrite'=>'Y', 'gseq'=>2,'qseq'=>5),
//              'eight' => array('sgqa'=>'eight', 'code'=>8, 'jsName'=>'java_eight', 'readWrite'=>'Y', 'gseq'=>2,'qseq'=>5),
//              'nine' => array('sgqa'=>'nine', 'code'=>9, 'jsName'=>'java_nine', 'readWrite'=>'Y', 'gseq'=>2,'qseq'=>5),
//              'ten' => array('sgqa'=>'ten', 'code'=>10, 'jsName'=>'java_ten', 'readWrite'=>'Y', 'gseq'=>1,'qseq'=>1),
//              'half' => array('sgqa'=>'half', 'code'=>.5, 'jsName'=>'java_half', 'readWrite'=>'Y', 'gseq'=>1,'qseq'=>1),
//              'hi' => array('sgqa'=>'hi', 'code'=>'there', 'jsName'=>'java_hi', 'readWrite'=>'Y', 'gseq'=>1,'qseq'=>1),
//              'hello' => array('sgqa'=>'hello', 'code'=>"Tom", 'jsName'=>'java_hello', 'readWrite'=>'Y', 'gseq'=>1,'qseq'=>1),
//              'a' => array('sgqa'=>'a', 'code'=>0, 'jsName'=>'java_a', 'readWrite'=>'Y', 'gseq'=>2,'qseq'=>2),
//              'b' => array('sgqa'=>'b', 'code'=>0, 'jsName'=>'java_b', 'readWrite'=>'Y', 'gseq'=>2,'qseq'=>2),
//              'c' => array('sgqa'=>'c', 'code'=>0, 'jsName'=>'java_c', 'readWrite'=>'Y', 'gseq'=>2,'qseq'=>2),
//              'd' => array('sgqa'=>'d', 'code'=>0, 'jsName'=>'java_d', 'readWrite'=>'Y', 'gseq'=>2,'qseq'=>2),
//              'eleven' => array('sgqa'=>'eleven', 'code'=>11, 'jsName'=>'java_eleven', 'readWrite'=>'Y', 'gseq'=>1,'qseq'=>1),
//              'twelve' => array('sgqa'=>'twelve', 'code'=>12, 'jsName'=>'java_twelve', 'readWrite'=>'Y', 'gseq'=>1,'qseq'=>1),
//              // Constants
//              'ASSESSMENT_HEADING' => array('sgqa'=>'ASSESSMENT_HEADING', 'code'=>'"Can strings contain embedded \"quoted passages\" (and parentheses + other characters?)?"', 'jsName'=>'', 'readWrite'=>'N'),
//              'QID' => array('sgqa'=>'QID', 'code'=>'value for {QID}', 'jsName'=>'', 'readWrite'=>'N'),
//              'QUESTIONHELP' => array('sgqa'=>'QUESTIONHELP', 'code'=>'"can single quoted strings" . \'contain nested \'quoted sections\'?', 'jsName'=>'', 'readWrite'=>'N'),
//              'QUESTION_HELP' => array('sgqa'=>'QUESTION_HELP', 'code'=>'Can strings have embedded <tags> like <html>, or even unbalanced "quotes or entities without terminal semicolons like &amp and  &lt?', 'jsName'=>'', 'readWrite'=>'N'),
//              'NUMBEROFQUESTIONS' => array('sgqa'=>'NUMBEROFQUESTIONS', 'code'=>'value for {NUMBEROFQUESTIONS}', 'jsName'=>'', 'readWrite'=>'N'),
//              'THEREAREXQUESTIONS' => array('sgqa'=>'THEREAREXQUESTIONS', 'code'=>'value for {THEREAREXQUESTIONS}', 'jsName'=>'', 'readWrite'=>'N'),
//              'TOKEN:FIRSTNAME' => array('sgqa'=>'TOKEN:FIRSTNAME', 'code' => 'value for {TOKEN:FIRSTNAME}', 'jsName' => '', 'readWrite' => 'N'),
//              'WELCOME' => array('sgqa'=>'WELCOME', 'code'=>'value for {WELCOME}', 'jsName'=>'', 'readWrite'=>'N'),
//              // also include SGQA values and read-only variable attributes
//              '12X34X56' => array('sgqa'=>'12X34X56', 'code'=>5, 'jsName'=>'', 'readWrite'=>'N', 'gseq'=>1,'qseq'=>1),
//              '12X3X5lab1_ber' => array('sgqa'=>'12X3X5lab1_ber', 'code'=>10, 'jsName'=>'', 'readWrite'=>'N', 'gseq'=>1,'qseq'=>1),
//              'q5pointChoice' => array('sgqa'=>'q5pointChoice', 'code'=> 3, 'jsName'=>'java_q5pointChoice', 'readWrite'=>'N','shown'=>'Father', 'relevance'=>1, 'type'=>'5', 'question'=>'(question for q5pointChoice)', 'qid'=>14,'gseq'=>2,'qseq'=>14),
//              'qArrayNumbers_ls1_min' => array('sgqa'=>'qArrayNumbers_ls1_min', 'code'=> 7, 'jsName'=>'java_qArrayNumbers_ls1_min', 'readWrite'=>'N','shown'=> 'I love LimeSurvey', 'relevance'=>1, 'type'=>'A', 'question'=>'(question for qArrayNumbers)', 'qid'=>6,'gseq'=>2,'qseq'=>6),
//              '12X3X5lab1_ber#1' => array('sgqa'=>'12X3X5lab1_ber#1', 'code'=> 15, 'jsName'=>'', 'readWrite'=>'N', 'gseq'=>1,'qseq'=>1),
//              'zero' => array('sgqa'=>'zero', 'code'=>0, 'jsName'=>'java_zero', 'gseq'=>0,'qseq'=>0),
//              'empty' => array('sgqa'=>'empty', 'code'=>'', 'jsName'=>'java_empty', 'gseq'=>0,'qseq'=>0),
//              'BREAKS' => array('sgqa'=>'BREAKS', 'code'=>"1\n2\n3", 'jsName'=>'', 'readWrite'=>'N'),
//          );
//          $this->lem->setTempVars($vars);
//
//          foreach ($vars as $var => $attributes)
//          {
//              foreach ($attributes as $key => $val)
//              {
//                  $this->assertEquals($val, $this->lem->GetVarAttribute($var, $key, null, 0, 0), "Failed GetVarAttribute: $var.$key");
//              }
//          }
//
//      }
//
    public function testEvaluator()
    {
        $booleanExpressions = array(
            "1" => true,
            "0" => false,
            "" => false,
            "1 == 1" => true,
            "0 == 1" => false,
            "1 && 0" => false,
            "1 && 1" => true,
            "1 || 0" => true,
            "0 || 0" => false,
        );

        foreach ($booleanExpressions as $expr => $expected) {
            $this->assertEquals($expected, $this->em->ProcessBooleanExpression($expr), "Expression: '$expr'");
        }
    }

    public function testFunctions()
    {
        $functions = array(
            'abs(5)' => 5,
            'abs(-5)' => 5,
            'abs(0)' => 0,
            'acos(0.5)' => acos(0.5),
            'acos(0.1)' => acos(0.1),

        );
        foreach ($functions as $function => $expected) {
            $this->assertEquals($expected, $this->em->sProcessStringContainingExpressions('{' . $function . '}'));
        }
    }

    public function testEscapes()
    {
        $strings = array(
            '\{1+1}' => '{1+1}',
            'x{1+1}' => 'x2',
            'x{1+1\}' => 'x{1+1}',
        );
        foreach ($strings as $escaped => $expected) {
            $this->assertEquals($expected, $this->em->sProcessStringContainingExpressions($escaped));
        }
    }

    public function testJuggling()
    {
        $equalities = array(
            '"1" == 1' => 1,
            '"5" + "2"' => 7,
            '"1" == 0' => '', // False is an empty string.
            '1 == "1"' => 1,
            '1 + "2"' => 3,
            '"1" + "a"' => '1a',
            '1 + "a"' => '1a',
            '"05" + "1"' => 6,
            '"" + "1" + "2"' => 12
        );
        foreach ($equalities as $expression => $expected) {
            $result = $this->em->sProcessStringContainingExpressions('{' . $expression . '}');
            $this->assertEquals($expected, $result);
        }
    }
    public function oldTestEvaluator()
    {


        // Syntax for $tests is
        // expectedResult~expression
        // if the expected result is an error, use NULL for the expected result
        $tests = <<<EOD
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

        $atests = explode("\n", $tests);
        $atests[] = "1\n2\n3~BREAKS";
        $atests[] = "1<br />\n2<br />\n3~nl2br(BREAKS)";
        $atests[] = "hi<br />\nthere<br />\nhow<br />\nare<br />\nyou?~nl2br('hi\\nthere\\nhow\\nare\\nyou?')";
        $atests[] = "hi<br />\nthere,<br />\nuser!~nl2br(implode('\\n','hi','there,','user!'))";

        $LEM = & LimeExpressionManager::singleton();
        $em = new ExpressionManager();
        $LEM->setTempVars($vars);

        //$LEMsessid = responses_' . Yii::app()->getConfig('surveyID');
        $LEMsessid = 'responses_12345';
        // manually set relevance status
        $_SESSION[$LEMsessid]['relevanceStatus'] = array();
        foreach ($vars as $var) {
            if (isset($var['qseq'])) {
                $_SESSION[$LEMsessid]['relevanceStatus'][$var['qseq']] = 1;
            }
        }

        $allJsVarnamesUsed = array();
        $body = '';
        $body .= '<table border="1"><tr><th>Expression</th><th>PHP Result</th><th>Expected</th><th>JavaScript Result</th><th>VarNames</th><th>JavaScript Eqn</th></tr>';
        $i = 0;
        $javaScript = array();
        foreach ($atests as $test) {
            ++$i;
            $values = explode("~", $test);
            $expectedResult = array_shift($values);
            $expr = implode("~", $values);
            $resultStatus = 'ok';
            $em->groupSeq = 2;
            $em->questionSeq = 3;
            $status = $em->RDP_Evaluate($expr);
            if ($status) {
                $allJsVarnamesUsed = array_merge($allJsVarnamesUsed, $em->GetJsVarsUsed());
            }
            $result = $em->GetResult();
            $valToShow = $result; // htmlspecialchars($result,ENT_QUOTES,'UTF-8',false);
            $expectedToShow = $expectedResult; // htmlspecialchars($expectedResult,ENT_QUOTES,'UTF-8',false);
            $body .= "<tr>";
            $body .= "<td>" . $em->GetPrettyPrintString() . "</td>\n";
            if (is_null($result)) {
                $valToShow = "NULL";
            }
            if ($valToShow != $expectedToShow) {
                $resultStatus = 'error';
            }
            $body .= "<td class='" . $resultStatus . "'>" . $valToShow . "</td>\n";
            $body .= '<td>' . $expectedToShow . "</td>\n";
            $javaScript[] = $em->GetJavascriptTestforExpression($expectedToShow, $i);
            $body .= "<td id='test_" . $i . "'>&nbsp;</td>\n";
            $varsUsed = $em->GetVarsUsed();
            if (is_array($varsUsed) and count($varsUsed) > 0) {
                $varDesc = array();
                foreach ($varsUsed as $v) {
                    $varDesc[] = $v;
                }
                $body .= '<td>' . implode(',<br/>', $varDesc) . "</td>\n";
            } else {
                $body .= "<td>&nbsp;</td>\n";
            }
            $jsEqn = $em->GetJavaScriptEquivalentOfExpression();
            if ($jsEqn == '') {
                $body .= "<td>&nbsp;</td>\n";
            } else {
                $body .= '<td>' . $jsEqn . "</td>\n";
            }
            $body .= '</tr>';
        }
        $body .= '</table>';
        $body .= "<script type='text/javascript'>\n";
        $body .= "<!--\n";
        $body .= "var LEMgseq=2;\n";
        $body .= "var LEMmode='group';\n";
        $body .= "function recompute() {\n";
        $body .= implode("\n", $javaScript);
        $body .= "}\n//-->\n</script>\n";

        $allJsVarnamesUsed = array_unique($allJsVarnamesUsed);
        asort($allJsVarnamesUsed);
        $pre = '';
        $pre .= "<h3>Change some Relevance values to 0 to see how it affects computations</h3>\n";
        $pre .= '<table border="1"><tr><th>#</th><th>JsVarname</th><th>Starting Value</th><th>Relevance</th></tr>';
        $i = 0;
        $LEMvarNameAttr = array();
        $LEMalias2varName = array();
        foreach ($allJsVarnamesUsed as $jsVarName) {
            ++$i;
            $pre .= "<tr><td>" . $i . "</td><td>" . $jsVarName;
            foreach ($vars as $k => $v) {
                if ($v['jsName'] == $jsVarName) {
                    $value = $v['code'];
                }
            }
            $pre .= "</td><td>" . $value . "</td><td><input type='text' id='relevance" . $i . "' value='1' onchange='recompute()'/>\n";
            $pre .= "<input type='hidden' id='" . $jsVarName . "' name='" . $jsVarName . "' value='" . $value . "'/>\n";
            $pre .= "</td></tr>\n";
            $LEMalias2varName[] = "'" . substr((string) $jsVarName, 5) . "':'" . $jsVarName . "'";
            $LEMalias2varName[] = "'" . $jsVarName . "':'" . $jsVarName . "'";
            $attrInfo = "'" . $jsVarName . "': {'jsName':'" . $jsVarName . "'";

            $varInfo = $vars[substr((string) $jsVarName, 5)];
            foreach ($varInfo as $k => $v) {
                if ($k == 'code') {
                    continue; // will access it from hidden node
                }
                if ($k == 'shown') {
                    $k = 'shown';
                    $v = htmlspecialchars(preg_replace("/[[:space:]]/", ' ', (string) $v), ENT_QUOTES);
                }
                if ($k == 'jsName') {
                    continue; // since already set
                }
                $attrInfo .= ", '" . $k . "':'" . $v . "'";
            }
            $attrInfo .= ",'qid':" . $i . "}";
            $LEMvarNameAttr[] = $attrInfo;
        }
        $pre .= "</table>\n";

        $pre .= "<script type='text/javascript'>\n";
        $pre .= "<!--\n";
        $pre .= "var LEMalias2varName= {" . implode(",\n", $LEMalias2varName) . "};\n";
        $pre .= "var LEMvarNameAttr= {" . implode(",\n", $LEMvarNameAttr) . "};\n";
        $pre .= "var LEMradix = '.';\n";
        $pre .= "//-->\n</script>\n";

        print $pre;
        print $body;
    }


        /**
         * Unit test the asSplitStringOnExpressions() function to ensure that accurately parses out all expressions
         * surrounded by curly braces, allowing for strings and escaped curly braces.
         */

    public function oldStringSplitter()
    {
        $tests = <<<EOD
This string does not contain an expression
"This is only a string"
"this is a string that contains {something in curly brace}"
How about nested curly braces, like {INSERTANS:{SGQ}}?
This example has escaped curly braces like \{this is not an equation\}
Should the parser check for unmatched { opening curly braces?
What about for unmatched } closing curly braces?
What if there is a { space after the opening brace?}
What about a {space before the closing brace }?
What about an { expression nested {within a string} that has white space after the opening brace}?
This {expression has {a nested curly brace { plus ones with whitespace around them} - they should be properly captured} into an expression  with sub-expressions.
This {is a string {since it does not close } all of its curly} braces.
Can {expressions contain 'single' or "double" quoted strings}?
Can an expression contain a perl regular expression like this {'/^\d{3}-\d{2}-\d{4}$/'}?
[img src="images/mine_{Q1}.png"/]
[img src="images/mine_" + {Q1} + ".png"/]
[img src={implode('','"images/mine_',Q1,'.png"')}/]
[img src="images/mine_{if(Q1=="Y",'yes','no')}.png"/]
[img src="images/mine_{if(Q1=="Y",'sq with {nested braces}',"dq with {nested braces}")}.png"/]
{name}, you said that you are {age} years old, and that you have {numKids} {if((numKids==1),'child','children')} and {numPets} {if((numPets==1),'pet','pets')} running around the house. So, you have {numKids + numPets} wild {if((numKids + numPets ==1),'beast','beasts')} to chase around every day.
Since you have more {if((INSERT61764X1X3 > INSERT61764X1X4),'children','pets')} than you do {if((INSERT61764X1X3 > INSERT61764X1X4),'pets','children')}, do you feel that the {if((INSERT61764X1X3 > INSERT61764X1X4),'pets','children')} are at a disadvantage?
Here is a String that failed to parse prior to fixing the preg_split() command to avoid recursive search of sub-strings: [{((617167X9X3241 == "Y" or 617167X9X3242 == "Y" or 617167X9X3243 == "Y" or 617167X9X3244 == "Y" or 617167X9X3245 == "Y" or 617167X9X3246 == "Y" or 617167X9X3247 == "Y" or 617167X9X3248 == "Y" or 617167X9X3249 == "Y") and (617167X9X3301 == "Y" or 617167X9X3302 == "Y" or 617167X9X3303 == "Y" or 617167X9X3304 == "Y" or 617167X9X3305 == "Y" or 617167X9X3306 == "Y" or 617167X9X3307 == "Y" or 617167X9X3308 == "Y" or 617167X9X3309 == "Y"))}] Here is the question.
EOD;
// Here is a String that failed to parse prior to fixing the preg_split() command to avoid recursive search of sub-strings: [{((617167X9X3241 == "Y" or 617167X9X3242 == "Y" or 617167X9X3243 == "Y" or 617167X9X3244 == "Y" or 617167X9X3245 == "Y" or 617167X9X3246 == "Y" or 617167X9X3247 == "Y" or 617167X9X3248 == "Y" or 617167X9X3249 == "Y") and (617167X9X3301 == "Y" or 617167X9X3302 == "Y" or 617167X9X3303 == "Y" or 617167X9X3304 == "Y" or 617167X9X3305 == "Y" or 617167X9X3306 == "Y" or 617167X9X3307 == "Y" or 617167X9X3308 == "Y" or 617167X9X3309 == "Y"))}] Here is the question.

        $em = new ExpressionManager();

        $atests = explode("\n", $tests);
        array_push($atests, '"hi\nthere\nhow\nare\nyou?\n"');

        foreach ($atests as $test) {
            $tokens = $em->asSplitStringOnExpressions($test);
            print '<b>' . $test . '</b><hr/>';
            print '<code>';
            print implode("<br/>\n", explode("\n", print_r($tokens, true)));
            print '</code><hr/>';
        }
    }

    /**
     * Unit test the Tokenizer - Tokenize and generate a HTML-compatible print-out of a comprehensive set of test cases
     */

    public function oldTokenizer()
    {
        // Comprehensive test cases for tokenizing
        $tests = <<<EOD
        String:  "what about regular expressions, like for SSN (^\d{3}-\d{2}-\d{4}) or US phone# ((?:\(\d{3}\)\s*\d{3}-\d{4})"
        String:  "Can strings contain embedded \"quoted passages\" (and parentheses + other characters?)?"
        String:  "can single quoted strings" . 'contain nested \'quoted sections\'?';
        Parens:  upcase('hello');
        Numbers:  42 72.35 -15 +37 42A .5 0.7
        And_Or: (this and that or the other);  Sandles, sorting; (a && b || c)
        Words:  hi there, my name is C3PO!
        UnaryOps: ++a, --b !b
        BinaryOps:  (a + b * c / d)
        Comparators:  > >= < <= == != gt ge lt le eq ne (target large gents built agile less equal)
        Assign:  = += -= *= /=
        SGQA:  1X6X12 1X6X12ber1 1X6X12ber1_lab1 3583X84X249 12X3X5lab1_ber#1 1X6X12.NAOK 1X6X12ber1.NAOK 1X6X12ber1_lab1.NAOK 3583X84X249.NAOK 12X3X5lab1_ber#1.NAOK
        Errors: Apt # 10C; (2 > 0) ? 'hi' : 'there'; array[30]; >>> <<< /* this is not a comment */ // neither is this
        Words:  q5pointChoice q5pointChoice.bogus q5pointChoice.code q5pointChoice.mandatory q5pointChoice.NAOK q5pointChoice.qid q5pointChoice.question q5pointChoice.relevance q5pointChoice.shown q5pointChoice.type
EOD;

        $em = new ExpressionManager();

        foreach (explode("\n", $tests) as $test) {
            $tokens = array(); //$em->RDP_Tokenize($test);
            print '<b>' . $test . '</b><hr/>';
            print '<code>';
            print implode("<br/>\n", explode("\n", print_r($tokens, true)));
            print '</code><hr/>';
        }
    }
}
