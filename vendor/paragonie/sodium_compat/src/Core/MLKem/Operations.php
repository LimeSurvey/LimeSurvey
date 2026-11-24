<?php

if (class_exists('ParagonIE_Sodium_Core_MLKem_Operations', false)) {
    return;
}

/**
 * @internal
 */
final class ParagonIE_Sodium_Core_MLKem_Operations extends ParagonIE_Sodium_Core_Util
{
    /** @var int[] */
    public static $ZETAS = array(
        1,
        1729,
        2580,
        3289,
        2642,
        630,
        1897,
        848,
        1062,
        1919,
        193,
        797,
        2786,
        3260,
        569,
        1746,
        296,
        2447,
        1339,
        1476,
        3046,
        56,
        2240,
        1333,
        1426,
        2094,
        535,
        2882,
        2393,
        2879,
        1974,
        821,
        289,
        331,
        3253,
        1756,
        1197,
        2304,
        2277,
        2055,
        650,
        1977,
        2513,
        632,
        2865,
        33,
        1320,
        1915,
        2319,
        1435,
        807,
        452,
        1438,
        2868,
        1534,
        2402,
        2647,
        2617,
        1481,
        648,
        2474,
        3110,
        1227,
        910,
        17,
        2761,
        583,
        2649,
        1637,
        723,
        2288,
        1100,
        1409,
        2662,
        3281,
        233,
        756,
        2156,
        3015,
        3050,
        1703,
        1651,
        2789,
        1789,
        1847,
        952,
        1461,
        2687,
        939,
        2308,
        2437,
        2388,
        733,
        2337,
        268,
        641,
        1584,
        2298,
        2037,
        3220,
        375,
        2549,
        2090,
        1645,
        1063,
        319,
        2773,
        757,
        2099,
        561,
        2466,
        2594,
        2804,
        1092,
        403,
        1026,
        1143,
        2150,
        2775,
        886,
        1722,
        1212,
        1874,
        1029,
        2110,
        2935,
        885,
        2154
    );
    /** @var int[] */
    public static $GAMMAS = array(
        17,
        3312,
        2761,
        568,
        583,
        2746,
        2649,
        680,
        1637,
        1692,
        723,
        2606,
        2288,
        1041,
        1100,
        2229,
        1409,
        1920,
        2662,
        667,
        3281,
        48,
        233,
        3096,
        756,
        2573,
        2156,
        1173,
        3015,
        314,
        3050,
        279,
        1703,
        1626,
        1651,
        1678,
        2789,
        540,
        1789,
        1540,
        1847,
        1482,
        952,
        2377,
        1461,
        1868,
        2687,
        642,
        939,
        2390,
        2308,
        1021,
        2437,
        892,
        2388,
        941,
        733,
        2596,
        2337,
        992,
        268,
        3061,
        641,
        2688,
        1584,
        1745,
        2298,
        1031,
        2037,
        1292,
        3220,
        109,
        375,
        2954,
        2549,
        780,
        2090,
        1239,
        1645,
        1684,
        1063,
        2266,
        319,
        3010,
        2773,
        556,
        757,
        2572,
        2099,
        1230,
        561,
        2768,
        2466,
        863,
        2594,
        735,
        2804,
        525,
        1092,
        2237,
        403,
        2926,
        1026,
        2303,
        1143,
        2186,
        2150,
        1179,
        2775,
        554,
        886,
        2443,
        1722,
        1607,
        1212,
        2117,
        1874,
        1455,
        1029,
        2300,
        2110,
        1219,
        2935,
        394,
        885,
        2444,
        2154,
        1175
    );
    /**
     * @template T of ParagonIE_Sodium_Core_MLKem_RingElement|ParagonIE_Sodium_Core_MLKem_NttElement
     * @param T $a
     * @param T $b
     * @return T
     */
    public static function polyAdd($a, $b)
    {
        $r = clone $a;
        $r->c0 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c0, $b->c0);
        $r->c1 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c1, $b->c1);
        $r->c2 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c2, $b->c2);
        $r->c3 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c3, $b->c3);
        $r->c4 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c4, $b->c4);
        $r->c5 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c5, $b->c5);
        $r->c6 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c6, $b->c6);
        $r->c7 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c7, $b->c7);
        $r->c8 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c8, $b->c8);
        $r->c9 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c9, $b->c9);
        $r->c10 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c10, $b->c10);
        $r->c11 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c11, $b->c11);
        $r->c12 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c12, $b->c12);
        $r->c13 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c13, $b->c13);
        $r->c14 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c14, $b->c14);
        $r->c15 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c15, $b->c15);
        $r->c16 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c16, $b->c16);
        $r->c17 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c17, $b->c17);
        $r->c18 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c18, $b->c18);
        $r->c19 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c19, $b->c19);
        $r->c20 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c20, $b->c20);
        $r->c21 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c21, $b->c21);
        $r->c22 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c22, $b->c22);
        $r->c23 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c23, $b->c23);
        $r->c24 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c24, $b->c24);
        $r->c25 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c25, $b->c25);
        $r->c26 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c26, $b->c26);
        $r->c27 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c27, $b->c27);
        $r->c28 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c28, $b->c28);
        $r->c29 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c29, $b->c29);
        $r->c30 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c30, $b->c30);
        $r->c31 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c31, $b->c31);
        $r->c32 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c32, $b->c32);
        $r->c33 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c33, $b->c33);
        $r->c34 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c34, $b->c34);
        $r->c35 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c35, $b->c35);
        $r->c36 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c36, $b->c36);
        $r->c37 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c37, $b->c37);
        $r->c38 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c38, $b->c38);
        $r->c39 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c39, $b->c39);
        $r->c40 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c40, $b->c40);
        $r->c41 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c41, $b->c41);
        $r->c42 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c42, $b->c42);
        $r->c43 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c43, $b->c43);
        $r->c44 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c44, $b->c44);
        $r->c45 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c45, $b->c45);
        $r->c46 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c46, $b->c46);
        $r->c47 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c47, $b->c47);
        $r->c48 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c48, $b->c48);
        $r->c49 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c49, $b->c49);
        $r->c50 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c50, $b->c50);
        $r->c51 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c51, $b->c51);
        $r->c52 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c52, $b->c52);
        $r->c53 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c53, $b->c53);
        $r->c54 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c54, $b->c54);
        $r->c55 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c55, $b->c55);
        $r->c56 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c56, $b->c56);
        $r->c57 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c57, $b->c57);
        $r->c58 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c58, $b->c58);
        $r->c59 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c59, $b->c59);
        $r->c60 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c60, $b->c60);
        $r->c61 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c61, $b->c61);
        $r->c62 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c62, $b->c62);
        $r->c63 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c63, $b->c63);
        $r->c64 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c64, $b->c64);
        $r->c65 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c65, $b->c65);
        $r->c66 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c66, $b->c66);
        $r->c67 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c67, $b->c67);
        $r->c68 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c68, $b->c68);
        $r->c69 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c69, $b->c69);
        $r->c70 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c70, $b->c70);
        $r->c71 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c71, $b->c71);
        $r->c72 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c72, $b->c72);
        $r->c73 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c73, $b->c73);
        $r->c74 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c74, $b->c74);
        $r->c75 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c75, $b->c75);
        $r->c76 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c76, $b->c76);
        $r->c77 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c77, $b->c77);
        $r->c78 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c78, $b->c78);
        $r->c79 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c79, $b->c79);
        $r->c80 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c80, $b->c80);
        $r->c81 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c81, $b->c81);
        $r->c82 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c82, $b->c82);
        $r->c83 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c83, $b->c83);
        $r->c84 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c84, $b->c84);
        $r->c85 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c85, $b->c85);
        $r->c86 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c86, $b->c86);
        $r->c87 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c87, $b->c87);
        $r->c88 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c88, $b->c88);
        $r->c89 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c89, $b->c89);
        $r->c90 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c90, $b->c90);
        $r->c91 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c91, $b->c91);
        $r->c92 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c92, $b->c92);
        $r->c93 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c93, $b->c93);
        $r->c94 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c94, $b->c94);
        $r->c95 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c95, $b->c95);
        $r->c96 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c96, $b->c96);
        $r->c97 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c97, $b->c97);
        $r->c98 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c98, $b->c98);
        $r->c99 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c99, $b->c99);
        $r->c100 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c100, $b->c100);
        $r->c101 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c101, $b->c101);
        $r->c102 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c102, $b->c102);
        $r->c103 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c103, $b->c103);
        $r->c104 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c104, $b->c104);
        $r->c105 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c105, $b->c105);
        $r->c106 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c106, $b->c106);
        $r->c107 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c107, $b->c107);
        $r->c108 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c108, $b->c108);
        $r->c109 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c109, $b->c109);
        $r->c110 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c110, $b->c110);
        $r->c111 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c111, $b->c111);
        $r->c112 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c112, $b->c112);
        $r->c113 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c113, $b->c113);
        $r->c114 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c114, $b->c114);
        $r->c115 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c115, $b->c115);
        $r->c116 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c116, $b->c116);
        $r->c117 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c117, $b->c117);
        $r->c118 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c118, $b->c118);
        $r->c119 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c119, $b->c119);
        $r->c120 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c120, $b->c120);
        $r->c121 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c121, $b->c121);
        $r->c122 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c122, $b->c122);
        $r->c123 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c123, $b->c123);
        $r->c124 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c124, $b->c124);
        $r->c125 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c125, $b->c125);
        $r->c126 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c126, $b->c126);
        $r->c127 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c127, $b->c127);
        $r->c128 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c128, $b->c128);
        $r->c129 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c129, $b->c129);
        $r->c130 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c130, $b->c130);
        $r->c131 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c131, $b->c131);
        $r->c132 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c132, $b->c132);
        $r->c133 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c133, $b->c133);
        $r->c134 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c134, $b->c134);
        $r->c135 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c135, $b->c135);
        $r->c136 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c136, $b->c136);
        $r->c137 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c137, $b->c137);
        $r->c138 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c138, $b->c138);
        $r->c139 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c139, $b->c139);
        $r->c140 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c140, $b->c140);
        $r->c141 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c141, $b->c141);
        $r->c142 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c142, $b->c142);
        $r->c143 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c143, $b->c143);
        $r->c144 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c144, $b->c144);
        $r->c145 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c145, $b->c145);
        $r->c146 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c146, $b->c146);
        $r->c147 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c147, $b->c147);
        $r->c148 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c148, $b->c148);
        $r->c149 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c149, $b->c149);
        $r->c150 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c150, $b->c150);
        $r->c151 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c151, $b->c151);
        $r->c152 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c152, $b->c152);
        $r->c153 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c153, $b->c153);
        $r->c154 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c154, $b->c154);
        $r->c155 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c155, $b->c155);
        $r->c156 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c156, $b->c156);
        $r->c157 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c157, $b->c157);
        $r->c158 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c158, $b->c158);
        $r->c159 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c159, $b->c159);
        $r->c160 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c160, $b->c160);
        $r->c161 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c161, $b->c161);
        $r->c162 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c162, $b->c162);
        $r->c163 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c163, $b->c163);
        $r->c164 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c164, $b->c164);
        $r->c165 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c165, $b->c165);
        $r->c166 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c166, $b->c166);
        $r->c167 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c167, $b->c167);
        $r->c168 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c168, $b->c168);
        $r->c169 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c169, $b->c169);
        $r->c170 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c170, $b->c170);
        $r->c171 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c171, $b->c171);
        $r->c172 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c172, $b->c172);
        $r->c173 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c173, $b->c173);
        $r->c174 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c174, $b->c174);
        $r->c175 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c175, $b->c175);
        $r->c176 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c176, $b->c176);
        $r->c177 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c177, $b->c177);
        $r->c178 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c178, $b->c178);
        $r->c179 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c179, $b->c179);
        $r->c180 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c180, $b->c180);
        $r->c181 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c181, $b->c181);
        $r->c182 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c182, $b->c182);
        $r->c183 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c183, $b->c183);
        $r->c184 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c184, $b->c184);
        $r->c185 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c185, $b->c185);
        $r->c186 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c186, $b->c186);
        $r->c187 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c187, $b->c187);
        $r->c188 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c188, $b->c188);
        $r->c189 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c189, $b->c189);
        $r->c190 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c190, $b->c190);
        $r->c191 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c191, $b->c191);
        $r->c192 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c192, $b->c192);
        $r->c193 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c193, $b->c193);
        $r->c194 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c194, $b->c194);
        $r->c195 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c195, $b->c195);
        $r->c196 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c196, $b->c196);
        $r->c197 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c197, $b->c197);
        $r->c198 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c198, $b->c198);
        $r->c199 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c199, $b->c199);
        $r->c200 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c200, $b->c200);
        $r->c201 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c201, $b->c201);
        $r->c202 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c202, $b->c202);
        $r->c203 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c203, $b->c203);
        $r->c204 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c204, $b->c204);
        $r->c205 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c205, $b->c205);
        $r->c206 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c206, $b->c206);
        $r->c207 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c207, $b->c207);
        $r->c208 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c208, $b->c208);
        $r->c209 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c209, $b->c209);
        $r->c210 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c210, $b->c210);
        $r->c211 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c211, $b->c211);
        $r->c212 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c212, $b->c212);
        $r->c213 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c213, $b->c213);
        $r->c214 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c214, $b->c214);
        $r->c215 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c215, $b->c215);
        $r->c216 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c216, $b->c216);
        $r->c217 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c217, $b->c217);
        $r->c218 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c218, $b->c218);
        $r->c219 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c219, $b->c219);
        $r->c220 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c220, $b->c220);
        $r->c221 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c221, $b->c221);
        $r->c222 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c222, $b->c222);
        $r->c223 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c223, $b->c223);
        $r->c224 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c224, $b->c224);
        $r->c225 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c225, $b->c225);
        $r->c226 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c226, $b->c226);
        $r->c227 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c227, $b->c227);
        $r->c228 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c228, $b->c228);
        $r->c229 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c229, $b->c229);
        $r->c230 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c230, $b->c230);
        $r->c231 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c231, $b->c231);
        $r->c232 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c232, $b->c232);
        $r->c233 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c233, $b->c233);
        $r->c234 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c234, $b->c234);
        $r->c235 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c235, $b->c235);
        $r->c236 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c236, $b->c236);
        $r->c237 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c237, $b->c237);
        $r->c238 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c238, $b->c238);
        $r->c239 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c239, $b->c239);
        $r->c240 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c240, $b->c240);
        $r->c241 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c241, $b->c241);
        $r->c242 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c242, $b->c242);
        $r->c243 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c243, $b->c243);
        $r->c244 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c244, $b->c244);
        $r->c245 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c245, $b->c245);
        $r->c246 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c246, $b->c246);
        $r->c247 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c247, $b->c247);
        $r->c248 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c248, $b->c248);
        $r->c249 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c249, $b->c249);
        $r->c250 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c250, $b->c250);
        $r->c251 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c251, $b->c251);
        $r->c252 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c252, $b->c252);
        $r->c253 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c253, $b->c253);
        $r->c254 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c254, $b->c254);
        $r->c255 = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a->c255, $b->c255);
        return $r;
    }

    /**
     * @template T of ParagonIE_Sodium_Core_MLKem_RingElement|ParagonIE_Sodium_Core_MLKem_NttElement
     * @param T $a
     * @param T $b
     * @return T
     */
    public static function polySub($a, $b)
    {
        $r = clone $a;
        $r->c0 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c0, $b->c0);
        $r->c1 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c1, $b->c1);
        $r->c2 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c2, $b->c2);
        $r->c3 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c3, $b->c3);
        $r->c4 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c4, $b->c4);
        $r->c5 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c5, $b->c5);
        $r->c6 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c6, $b->c6);
        $r->c7 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c7, $b->c7);
        $r->c8 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c8, $b->c8);
        $r->c9 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c9, $b->c9);
        $r->c10 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c10, $b->c10);
        $r->c11 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c11, $b->c11);
        $r->c12 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c12, $b->c12);
        $r->c13 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c13, $b->c13);
        $r->c14 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c14, $b->c14);
        $r->c15 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c15, $b->c15);
        $r->c16 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c16, $b->c16);
        $r->c17 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c17, $b->c17);
        $r->c18 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c18, $b->c18);
        $r->c19 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c19, $b->c19);
        $r->c20 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c20, $b->c20);
        $r->c21 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c21, $b->c21);
        $r->c22 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c22, $b->c22);
        $r->c23 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c23, $b->c23);
        $r->c24 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c24, $b->c24);
        $r->c25 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c25, $b->c25);
        $r->c26 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c26, $b->c26);
        $r->c27 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c27, $b->c27);
        $r->c28 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c28, $b->c28);
        $r->c29 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c29, $b->c29);
        $r->c30 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c30, $b->c30);
        $r->c31 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c31, $b->c31);
        $r->c32 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c32, $b->c32);
        $r->c33 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c33, $b->c33);
        $r->c34 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c34, $b->c34);
        $r->c35 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c35, $b->c35);
        $r->c36 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c36, $b->c36);
        $r->c37 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c37, $b->c37);
        $r->c38 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c38, $b->c38);
        $r->c39 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c39, $b->c39);
        $r->c40 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c40, $b->c40);
        $r->c41 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c41, $b->c41);
        $r->c42 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c42, $b->c42);
        $r->c43 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c43, $b->c43);
        $r->c44 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c44, $b->c44);
        $r->c45 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c45, $b->c45);
        $r->c46 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c46, $b->c46);
        $r->c47 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c47, $b->c47);
        $r->c48 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c48, $b->c48);
        $r->c49 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c49, $b->c49);
        $r->c50 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c50, $b->c50);
        $r->c51 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c51, $b->c51);
        $r->c52 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c52, $b->c52);
        $r->c53 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c53, $b->c53);
        $r->c54 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c54, $b->c54);
        $r->c55 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c55, $b->c55);
        $r->c56 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c56, $b->c56);
        $r->c57 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c57, $b->c57);
        $r->c58 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c58, $b->c58);
        $r->c59 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c59, $b->c59);
        $r->c60 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c60, $b->c60);
        $r->c61 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c61, $b->c61);
        $r->c62 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c62, $b->c62);
        $r->c63 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c63, $b->c63);
        $r->c64 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c64, $b->c64);
        $r->c65 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c65, $b->c65);
        $r->c66 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c66, $b->c66);
        $r->c67 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c67, $b->c67);
        $r->c68 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c68, $b->c68);
        $r->c69 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c69, $b->c69);
        $r->c70 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c70, $b->c70);
        $r->c71 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c71, $b->c71);
        $r->c72 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c72, $b->c72);
        $r->c73 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c73, $b->c73);
        $r->c74 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c74, $b->c74);
        $r->c75 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c75, $b->c75);
        $r->c76 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c76, $b->c76);
        $r->c77 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c77, $b->c77);
        $r->c78 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c78, $b->c78);
        $r->c79 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c79, $b->c79);
        $r->c80 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c80, $b->c80);
        $r->c81 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c81, $b->c81);
        $r->c82 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c82, $b->c82);
        $r->c83 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c83, $b->c83);
        $r->c84 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c84, $b->c84);
        $r->c85 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c85, $b->c85);
        $r->c86 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c86, $b->c86);
        $r->c87 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c87, $b->c87);
        $r->c88 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c88, $b->c88);
        $r->c89 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c89, $b->c89);
        $r->c90 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c90, $b->c90);
        $r->c91 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c91, $b->c91);
        $r->c92 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c92, $b->c92);
        $r->c93 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c93, $b->c93);
        $r->c94 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c94, $b->c94);
        $r->c95 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c95, $b->c95);
        $r->c96 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c96, $b->c96);
        $r->c97 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c97, $b->c97);
        $r->c98 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c98, $b->c98);
        $r->c99 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c99, $b->c99);
        $r->c100 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c100, $b->c100);
        $r->c101 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c101, $b->c101);
        $r->c102 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c102, $b->c102);
        $r->c103 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c103, $b->c103);
        $r->c104 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c104, $b->c104);
        $r->c105 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c105, $b->c105);
        $r->c106 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c106, $b->c106);
        $r->c107 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c107, $b->c107);
        $r->c108 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c108, $b->c108);
        $r->c109 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c109, $b->c109);
        $r->c110 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c110, $b->c110);
        $r->c111 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c111, $b->c111);
        $r->c112 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c112, $b->c112);
        $r->c113 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c113, $b->c113);
        $r->c114 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c114, $b->c114);
        $r->c115 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c115, $b->c115);
        $r->c116 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c116, $b->c116);
        $r->c117 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c117, $b->c117);
        $r->c118 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c118, $b->c118);
        $r->c119 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c119, $b->c119);
        $r->c120 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c120, $b->c120);
        $r->c121 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c121, $b->c121);
        $r->c122 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c122, $b->c122);
        $r->c123 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c123, $b->c123);
        $r->c124 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c124, $b->c124);
        $r->c125 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c125, $b->c125);
        $r->c126 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c126, $b->c126);
        $r->c127 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c127, $b->c127);
        $r->c128 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c128, $b->c128);
        $r->c129 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c129, $b->c129);
        $r->c130 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c130, $b->c130);
        $r->c131 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c131, $b->c131);
        $r->c132 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c132, $b->c132);
        $r->c133 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c133, $b->c133);
        $r->c134 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c134, $b->c134);
        $r->c135 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c135, $b->c135);
        $r->c136 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c136, $b->c136);
        $r->c137 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c137, $b->c137);
        $r->c138 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c138, $b->c138);
        $r->c139 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c139, $b->c139);
        $r->c140 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c140, $b->c140);
        $r->c141 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c141, $b->c141);
        $r->c142 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c142, $b->c142);
        $r->c143 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c143, $b->c143);
        $r->c144 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c144, $b->c144);
        $r->c145 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c145, $b->c145);
        $r->c146 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c146, $b->c146);
        $r->c147 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c147, $b->c147);
        $r->c148 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c148, $b->c148);
        $r->c149 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c149, $b->c149);
        $r->c150 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c150, $b->c150);
        $r->c151 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c151, $b->c151);
        $r->c152 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c152, $b->c152);
        $r->c153 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c153, $b->c153);
        $r->c154 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c154, $b->c154);
        $r->c155 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c155, $b->c155);
        $r->c156 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c156, $b->c156);
        $r->c157 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c157, $b->c157);
        $r->c158 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c158, $b->c158);
        $r->c159 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c159, $b->c159);
        $r->c160 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c160, $b->c160);
        $r->c161 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c161, $b->c161);
        $r->c162 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c162, $b->c162);
        $r->c163 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c163, $b->c163);
        $r->c164 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c164, $b->c164);
        $r->c165 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c165, $b->c165);
        $r->c166 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c166, $b->c166);
        $r->c167 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c167, $b->c167);
        $r->c168 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c168, $b->c168);
        $r->c169 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c169, $b->c169);
        $r->c170 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c170, $b->c170);
        $r->c171 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c171, $b->c171);
        $r->c172 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c172, $b->c172);
        $r->c173 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c173, $b->c173);
        $r->c174 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c174, $b->c174);
        $r->c175 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c175, $b->c175);
        $r->c176 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c176, $b->c176);
        $r->c177 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c177, $b->c177);
        $r->c178 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c178, $b->c178);
        $r->c179 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c179, $b->c179);
        $r->c180 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c180, $b->c180);
        $r->c181 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c181, $b->c181);
        $r->c182 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c182, $b->c182);
        $r->c183 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c183, $b->c183);
        $r->c184 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c184, $b->c184);
        $r->c185 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c185, $b->c185);
        $r->c186 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c186, $b->c186);
        $r->c187 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c187, $b->c187);
        $r->c188 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c188, $b->c188);
        $r->c189 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c189, $b->c189);
        $r->c190 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c190, $b->c190);
        $r->c191 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c191, $b->c191);
        $r->c192 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c192, $b->c192);
        $r->c193 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c193, $b->c193);
        $r->c194 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c194, $b->c194);
        $r->c195 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c195, $b->c195);
        $r->c196 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c196, $b->c196);
        $r->c197 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c197, $b->c197);
        $r->c198 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c198, $b->c198);
        $r->c199 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c199, $b->c199);
        $r->c200 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c200, $b->c200);
        $r->c201 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c201, $b->c201);
        $r->c202 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c202, $b->c202);
        $r->c203 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c203, $b->c203);
        $r->c204 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c204, $b->c204);
        $r->c205 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c205, $b->c205);
        $r->c206 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c206, $b->c206);
        $r->c207 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c207, $b->c207);
        $r->c208 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c208, $b->c208);
        $r->c209 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c209, $b->c209);
        $r->c210 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c210, $b->c210);
        $r->c211 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c211, $b->c211);
        $r->c212 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c212, $b->c212);
        $r->c213 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c213, $b->c213);
        $r->c214 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c214, $b->c214);
        $r->c215 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c215, $b->c215);
        $r->c216 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c216, $b->c216);
        $r->c217 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c217, $b->c217);
        $r->c218 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c218, $b->c218);
        $r->c219 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c219, $b->c219);
        $r->c220 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c220, $b->c220);
        $r->c221 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c221, $b->c221);
        $r->c222 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c222, $b->c222);
        $r->c223 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c223, $b->c223);
        $r->c224 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c224, $b->c224);
        $r->c225 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c225, $b->c225);
        $r->c226 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c226, $b->c226);
        $r->c227 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c227, $b->c227);
        $r->c228 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c228, $b->c228);
        $r->c229 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c229, $b->c229);
        $r->c230 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c230, $b->c230);
        $r->c231 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c231, $b->c231);
        $r->c232 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c232, $b->c232);
        $r->c233 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c233, $b->c233);
        $r->c234 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c234, $b->c234);
        $r->c235 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c235, $b->c235);
        $r->c236 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c236, $b->c236);
        $r->c237 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c237, $b->c237);
        $r->c238 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c238, $b->c238);
        $r->c239 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c239, $b->c239);
        $r->c240 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c240, $b->c240);
        $r->c241 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c241, $b->c241);
        $r->c242 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c242, $b->c242);
        $r->c243 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c243, $b->c243);
        $r->c244 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c244, $b->c244);
        $r->c245 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c245, $b->c245);
        $r->c246 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c246, $b->c246);
        $r->c247 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c247, $b->c247);
        $r->c248 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c248, $b->c248);
        $r->c249 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c249, $b->c249);
        $r->c250 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c250, $b->c250);
        $r->c251 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c251, $b->c251);
        $r->c252 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c252, $b->c252);
        $r->c253 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c253, $b->c253);
        $r->c254 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c254, $b->c254);
        $r->c255 = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a->c255, $b->c255);
        return $r;
    }

    /**
     * FIPS 203, Algorithm 11
     *
     * @param ParagonIE_Sodium_Core_MLKem_NttElement $f
     * @param ParagonIE_Sodium_Core_MLKem_NttElement $g
     * @return ParagonIE_Sodium_Core_MLKem_NttElement
     */
    public static function nttMul($f, $g)
    {
        $h = ParagonIE_Sodium_Core_MLKem_NttElement::zero();
        $h->c0 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c0,
            $g->c0,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c1, $g->c1),
            self::$GAMMAS[0]
        );
        $h->c1 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c0, $g->c1, $f->c1, $g->c0);
        $h->c2 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c2,
            $g->c2,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c3, $g->c3),
            self::$GAMMAS[1]
        );
        $h->c3 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c2, $g->c3, $f->c3, $g->c2);
        $h->c4 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c4,
            $g->c4,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c5, $g->c5),
            self::$GAMMAS[2]
        );
        $h->c5 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c4, $g->c5, $f->c5, $g->c4);
        $h->c6 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c6,
            $g->c6,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c7, $g->c7),
            self::$GAMMAS[3]
        );
        $h->c7 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c6, $g->c7, $f->c7, $g->c6);
        $h->c8 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c8,
            $g->c8,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c9, $g->c9),
            self::$GAMMAS[4]
        );
        $h->c9 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c8, $g->c9, $f->c9, $g->c8);
        $h->c10 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c10,
            $g->c10,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c11, $g->c11),
            self::$GAMMAS[5]
        );
        $h->c11 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c10, $g->c11, $f->c11, $g->c10);
        $h->c12 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c12,
            $g->c12,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c13, $g->c13),
            self::$GAMMAS[6]
        );
        $h->c13 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c12, $g->c13, $f->c13, $g->c12);
        $h->c14 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c14,
            $g->c14,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c15, $g->c15),
            self::$GAMMAS[7]
        );
        $h->c15 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c14, $g->c15, $f->c15, $g->c14);
        $h->c16 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c16,
            $g->c16,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c17, $g->c17),
            self::$GAMMAS[8]
        );
        $h->c17 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c16, $g->c17, $f->c17, $g->c16);
        $h->c18 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c18,
            $g->c18,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c19, $g->c19),
            self::$GAMMAS[9]
        );
        $h->c19 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c18, $g->c19, $f->c19, $g->c18);
        $h->c20 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c20,
            $g->c20,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c21, $g->c21),
            self::$GAMMAS[10]
        );
        $h->c21 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c20, $g->c21, $f->c21, $g->c20);
        $h->c22 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c22,
            $g->c22,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c23, $g->c23),
            self::$GAMMAS[11]
        );
        $h->c23 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c22, $g->c23, $f->c23, $g->c22);
        $h->c24 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c24,
            $g->c24,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c25, $g->c25),
            self::$GAMMAS[12]
        );
        $h->c25 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c24, $g->c25, $f->c25, $g->c24);
        $h->c26 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c26,
            $g->c26,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c27, $g->c27),
            self::$GAMMAS[13]
        );
        $h->c27 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c26, $g->c27, $f->c27, $g->c26);
        $h->c28 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c28,
            $g->c28,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c29, $g->c29),
            self::$GAMMAS[14]
        );
        $h->c29 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c28, $g->c29, $f->c29, $g->c28);
        $h->c30 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c30,
            $g->c30,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c31, $g->c31),
            self::$GAMMAS[15]
        );
        $h->c31 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c30, $g->c31, $f->c31, $g->c30);
        $h->c32 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c32,
            $g->c32,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c33, $g->c33),
            self::$GAMMAS[16]
        );
        $h->c33 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c32, $g->c33, $f->c33, $g->c32);
        $h->c34 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c34,
            $g->c34,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c35, $g->c35),
            self::$GAMMAS[17]
        );
        $h->c35 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c34, $g->c35, $f->c35, $g->c34);
        $h->c36 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c36,
            $g->c36,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c37, $g->c37),
            self::$GAMMAS[18]
        );
        $h->c37 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c36, $g->c37, $f->c37, $g->c36);
        $h->c38 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c38,
            $g->c38,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c39, $g->c39),
            self::$GAMMAS[19]
        );
        $h->c39 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c38, $g->c39, $f->c39, $g->c38);
        $h->c40 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c40,
            $g->c40,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c41, $g->c41),
            self::$GAMMAS[20]
        );
        $h->c41 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c40, $g->c41, $f->c41, $g->c40);
        $h->c42 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c42,
            $g->c42,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c43, $g->c43),
            self::$GAMMAS[21]
        );
        $h->c43 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c42, $g->c43, $f->c43, $g->c42);
        $h->c44 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c44,
            $g->c44,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c45, $g->c45),
            self::$GAMMAS[22]
        );
        $h->c45 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c44, $g->c45, $f->c45, $g->c44);
        $h->c46 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c46,
            $g->c46,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c47, $g->c47),
            self::$GAMMAS[23]
        );
        $h->c47 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c46, $g->c47, $f->c47, $g->c46);
        $h->c48 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c48,
            $g->c48,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c49, $g->c49),
            self::$GAMMAS[24]
        );
        $h->c49 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c48, $g->c49, $f->c49, $g->c48);
        $h->c50 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c50,
            $g->c50,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c51, $g->c51),
            self::$GAMMAS[25]
        );
        $h->c51 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c50, $g->c51, $f->c51, $g->c50);
        $h->c52 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c52,
            $g->c52,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c53, $g->c53),
            self::$GAMMAS[26]
        );
        $h->c53 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c52, $g->c53, $f->c53, $g->c52);
        $h->c54 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c54,
            $g->c54,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c55, $g->c55),
            self::$GAMMAS[27]
        );
        $h->c55 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c54, $g->c55, $f->c55, $g->c54);
        $h->c56 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c56,
            $g->c56,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c57, $g->c57),
            self::$GAMMAS[28]
        );
        $h->c57 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c56, $g->c57, $f->c57, $g->c56);
        $h->c58 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c58,
            $g->c58,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c59, $g->c59),
            self::$GAMMAS[29]
        );
        $h->c59 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c58, $g->c59, $f->c59, $g->c58);
        $h->c60 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c60,
            $g->c60,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c61, $g->c61),
            self::$GAMMAS[30]
        );
        $h->c61 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c60, $g->c61, $f->c61, $g->c60);
        $h->c62 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c62,
            $g->c62,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c63, $g->c63),
            self::$GAMMAS[31]
        );
        $h->c63 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c62, $g->c63, $f->c63, $g->c62);
        $h->c64 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c64,
            $g->c64,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c65, $g->c65),
            self::$GAMMAS[32]
        );
        $h->c65 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c64, $g->c65, $f->c65, $g->c64);
        $h->c66 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c66,
            $g->c66,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c67, $g->c67),
            self::$GAMMAS[33]
        );
        $h->c67 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c66, $g->c67, $f->c67, $g->c66);
        $h->c68 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c68,
            $g->c68,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c69, $g->c69),
            self::$GAMMAS[34]
        );
        $h->c69 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c68, $g->c69, $f->c69, $g->c68);
        $h->c70 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c70,
            $g->c70,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c71, $g->c71),
            self::$GAMMAS[35]
        );
        $h->c71 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c70, $g->c71, $f->c71, $g->c70);
        $h->c72 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c72,
            $g->c72,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c73, $g->c73),
            self::$GAMMAS[36]
        );
        $h->c73 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c72, $g->c73, $f->c73, $g->c72);
        $h->c74 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c74,
            $g->c74,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c75, $g->c75),
            self::$GAMMAS[37]
        );
        $h->c75 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c74, $g->c75, $f->c75, $g->c74);
        $h->c76 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c76,
            $g->c76,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c77, $g->c77),
            self::$GAMMAS[38]
        );
        $h->c77 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c76, $g->c77, $f->c77, $g->c76);
        $h->c78 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c78,
            $g->c78,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c79, $g->c79),
            self::$GAMMAS[39]
        );
        $h->c79 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c78, $g->c79, $f->c79, $g->c78);
        $h->c80 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c80,
            $g->c80,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c81, $g->c81),
            self::$GAMMAS[40]
        );
        $h->c81 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c80, $g->c81, $f->c81, $g->c80);
        $h->c82 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c82,
            $g->c82,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c83, $g->c83),
            self::$GAMMAS[41]
        );
        $h->c83 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c82, $g->c83, $f->c83, $g->c82);
        $h->c84 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c84,
            $g->c84,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c85, $g->c85),
            self::$GAMMAS[42]
        );
        $h->c85 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c84, $g->c85, $f->c85, $g->c84);
        $h->c86 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c86,
            $g->c86,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c87, $g->c87),
            self::$GAMMAS[43]
        );
        $h->c87 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c86, $g->c87, $f->c87, $g->c86);
        $h->c88 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c88,
            $g->c88,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c89, $g->c89),
            self::$GAMMAS[44]
        );
        $h->c89 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c88, $g->c89, $f->c89, $g->c88);
        $h->c90 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c90,
            $g->c90,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c91, $g->c91),
            self::$GAMMAS[45]
        );
        $h->c91 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c90, $g->c91, $f->c91, $g->c90);
        $h->c92 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c92,
            $g->c92,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c93, $g->c93),
            self::$GAMMAS[46]
        );
        $h->c93 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c92, $g->c93, $f->c93, $g->c92);
        $h->c94 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c94,
            $g->c94,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c95, $g->c95),
            self::$GAMMAS[47]
        );
        $h->c95 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c94, $g->c95, $f->c95, $g->c94);
        $h->c96 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c96,
            $g->c96,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c97, $g->c97),
            self::$GAMMAS[48]
        );
        $h->c97 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c96, $g->c97, $f->c97, $g->c96);
        $h->c98 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c98,
            $g->c98,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c99, $g->c99),
            self::$GAMMAS[49]
        );
        $h->c99 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c98, $g->c99, $f->c99, $g->c98);
        $h->c100 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c100,
            $g->c100,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c101, $g->c101),
            self::$GAMMAS[50]
        );
        $h->c101 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c100, $g->c101, $f->c101, $g->c100);
        $h->c102 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c102,
            $g->c102,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c103, $g->c103),
            self::$GAMMAS[51]
        );
        $h->c103 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c102, $g->c103, $f->c103, $g->c102);
        $h->c104 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c104,
            $g->c104,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c105, $g->c105),
            self::$GAMMAS[52]
        );
        $h->c105 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c104, $g->c105, $f->c105, $g->c104);
        $h->c106 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c106,
            $g->c106,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c107, $g->c107),
            self::$GAMMAS[53]
        );
        $h->c107 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c106, $g->c107, $f->c107, $g->c106);
        $h->c108 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c108,
            $g->c108,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c109, $g->c109),
            self::$GAMMAS[54]
        );
        $h->c109 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c108, $g->c109, $f->c109, $g->c108);
        $h->c110 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c110,
            $g->c110,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c111, $g->c111),
            self::$GAMMAS[55]
        );
        $h->c111 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c110, $g->c111, $f->c111, $g->c110);
        $h->c112 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c112,
            $g->c112,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c113, $g->c113),
            self::$GAMMAS[56]
        );
        $h->c113 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c112, $g->c113, $f->c113, $g->c112);
        $h->c114 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c114,
            $g->c114,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c115, $g->c115),
            self::$GAMMAS[57]
        );
        $h->c115 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c114, $g->c115, $f->c115, $g->c114);
        $h->c116 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c116,
            $g->c116,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c117, $g->c117),
            self::$GAMMAS[58]
        );
        $h->c117 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c116, $g->c117, $f->c117, $g->c116);
        $h->c118 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c118,
            $g->c118,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c119, $g->c119),
            self::$GAMMAS[59]
        );
        $h->c119 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c118, $g->c119, $f->c119, $g->c118);
        $h->c120 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c120,
            $g->c120,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c121, $g->c121),
            self::$GAMMAS[60]
        );
        $h->c121 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c120, $g->c121, $f->c121, $g->c120);
        $h->c122 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c122,
            $g->c122,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c123, $g->c123),
            self::$GAMMAS[61]
        );
        $h->c123 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c122, $g->c123, $f->c123, $g->c122);
        $h->c124 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c124,
            $g->c124,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c125, $g->c125),
            self::$GAMMAS[62]
        );
        $h->c125 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c124, $g->c125, $f->c125, $g->c124);
        $h->c126 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c126,
            $g->c126,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c127, $g->c127),
            self::$GAMMAS[63]
        );
        $h->c127 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c126, $g->c127, $f->c127, $g->c126);
        $h->c128 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c128,
            $g->c128,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c129, $g->c129),
            self::$GAMMAS[64]
        );
        $h->c129 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c128, $g->c129, $f->c129, $g->c128);
        $h->c130 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c130,
            $g->c130,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c131, $g->c131),
            self::$GAMMAS[65]
        );
        $h->c131 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c130, $g->c131, $f->c131, $g->c130);
        $h->c132 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c132,
            $g->c132,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c133, $g->c133),
            self::$GAMMAS[66]
        );
        $h->c133 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c132, $g->c133, $f->c133, $g->c132);
        $h->c134 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c134,
            $g->c134,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c135, $g->c135),
            self::$GAMMAS[67]
        );
        $h->c135 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c134, $g->c135, $f->c135, $g->c134);
        $h->c136 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c136,
            $g->c136,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c137, $g->c137),
            self::$GAMMAS[68]
        );
        $h->c137 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c136, $g->c137, $f->c137, $g->c136);
        $h->c138 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c138,
            $g->c138,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c139, $g->c139),
            self::$GAMMAS[69]
        );
        $h->c139 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c138, $g->c139, $f->c139, $g->c138);
        $h->c140 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c140,
            $g->c140,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c141, $g->c141),
            self::$GAMMAS[70]
        );
        $h->c141 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c140, $g->c141, $f->c141, $g->c140);
        $h->c142 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c142,
            $g->c142,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c143, $g->c143),
            self::$GAMMAS[71]
        );
        $h->c143 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c142, $g->c143, $f->c143, $g->c142);
        $h->c144 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c144,
            $g->c144,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c145, $g->c145),
            self::$GAMMAS[72]
        );
        $h->c145 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c144, $g->c145, $f->c145, $g->c144);
        $h->c146 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c146,
            $g->c146,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c147, $g->c147),
            self::$GAMMAS[73]
        );
        $h->c147 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c146, $g->c147, $f->c147, $g->c146);
        $h->c148 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c148,
            $g->c148,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c149, $g->c149),
            self::$GAMMAS[74]
        );
        $h->c149 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c148, $g->c149, $f->c149, $g->c148);
        $h->c150 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c150,
            $g->c150,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c151, $g->c151),
            self::$GAMMAS[75]
        );
        $h->c151 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c150, $g->c151, $f->c151, $g->c150);
        $h->c152 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c152,
            $g->c152,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c153, $g->c153),
            self::$GAMMAS[76]
        );
        $h->c153 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c152, $g->c153, $f->c153, $g->c152);
        $h->c154 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c154,
            $g->c154,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c155, $g->c155),
            self::$GAMMAS[77]
        );
        $h->c155 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c154, $g->c155, $f->c155, $g->c154);
        $h->c156 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c156,
            $g->c156,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c157, $g->c157),
            self::$GAMMAS[78]
        );
        $h->c157 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c156, $g->c157, $f->c157, $g->c156);
        $h->c158 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c158,
            $g->c158,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c159, $g->c159),
            self::$GAMMAS[79]
        );
        $h->c159 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c158, $g->c159, $f->c159, $g->c158);
        $h->c160 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c160,
            $g->c160,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c161, $g->c161),
            self::$GAMMAS[80]
        );
        $h->c161 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c160, $g->c161, $f->c161, $g->c160);
        $h->c162 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c162,
            $g->c162,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c163, $g->c163),
            self::$GAMMAS[81]
        );
        $h->c163 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c162, $g->c163, $f->c163, $g->c162);
        $h->c164 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c164,
            $g->c164,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c165, $g->c165),
            self::$GAMMAS[82]
        );
        $h->c165 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c164, $g->c165, $f->c165, $g->c164);
        $h->c166 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c166,
            $g->c166,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c167, $g->c167),
            self::$GAMMAS[83]
        );
        $h->c167 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c166, $g->c167, $f->c167, $g->c166);
        $h->c168 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c168,
            $g->c168,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c169, $g->c169),
            self::$GAMMAS[84]
        );
        $h->c169 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c168, $g->c169, $f->c169, $g->c168);
        $h->c170 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c170,
            $g->c170,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c171, $g->c171),
            self::$GAMMAS[85]
        );
        $h->c171 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c170, $g->c171, $f->c171, $g->c170);
        $h->c172 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c172,
            $g->c172,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c173, $g->c173),
            self::$GAMMAS[86]
        );
        $h->c173 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c172, $g->c173, $f->c173, $g->c172);
        $h->c174 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c174,
            $g->c174,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c175, $g->c175),
            self::$GAMMAS[87]
        );
        $h->c175 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c174, $g->c175, $f->c175, $g->c174);
        $h->c176 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c176,
            $g->c176,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c177, $g->c177),
            self::$GAMMAS[88]
        );
        $h->c177 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c176, $g->c177, $f->c177, $g->c176);
        $h->c178 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c178,
            $g->c178,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c179, $g->c179),
            self::$GAMMAS[89]
        );
        $h->c179 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c178, $g->c179, $f->c179, $g->c178);
        $h->c180 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c180,
            $g->c180,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c181, $g->c181),
            self::$GAMMAS[90]
        );
        $h->c181 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c180, $g->c181, $f->c181, $g->c180);
        $h->c182 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c182,
            $g->c182,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c183, $g->c183),
            self::$GAMMAS[91]
        );
        $h->c183 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c182, $g->c183, $f->c183, $g->c182);
        $h->c184 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c184,
            $g->c184,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c185, $g->c185),
            self::$GAMMAS[92]
        );
        $h->c185 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c184, $g->c185, $f->c185, $g->c184);
        $h->c186 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c186,
            $g->c186,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c187, $g->c187),
            self::$GAMMAS[93]
        );
        $h->c187 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c186, $g->c187, $f->c187, $g->c186);
        $h->c188 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c188,
            $g->c188,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c189, $g->c189),
            self::$GAMMAS[94]
        );
        $h->c189 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c188, $g->c189, $f->c189, $g->c188);
        $h->c190 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c190,
            $g->c190,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c191, $g->c191),
            self::$GAMMAS[95]
        );
        $h->c191 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c190, $g->c191, $f->c191, $g->c190);
        $h->c192 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c192,
            $g->c192,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c193, $g->c193),
            self::$GAMMAS[96]
        );
        $h->c193 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c192, $g->c193, $f->c193, $g->c192);
        $h->c194 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c194,
            $g->c194,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c195, $g->c195),
            self::$GAMMAS[97]
        );
        $h->c195 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c194, $g->c195, $f->c195, $g->c194);
        $h->c196 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c196,
            $g->c196,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c197, $g->c197),
            self::$GAMMAS[98]
        );
        $h->c197 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c196, $g->c197, $f->c197, $g->c196);
        $h->c198 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c198,
            $g->c198,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c199, $g->c199),
            self::$GAMMAS[99]
        );
        $h->c199 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c198, $g->c199, $f->c199, $g->c198);
        $h->c200 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c200,
            $g->c200,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c201, $g->c201),
            self::$GAMMAS[100]
        );
        $h->c201 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c200, $g->c201, $f->c201, $g->c200);
        $h->c202 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c202,
            $g->c202,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c203, $g->c203),
            self::$GAMMAS[101]
        );
        $h->c203 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c202, $g->c203, $f->c203, $g->c202);
        $h->c204 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c204,
            $g->c204,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c205, $g->c205),
            self::$GAMMAS[102]
        );
        $h->c205 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c204, $g->c205, $f->c205, $g->c204);
        $h->c206 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c206,
            $g->c206,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c207, $g->c207),
            self::$GAMMAS[103]
        );
        $h->c207 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c206, $g->c207, $f->c207, $g->c206);
        $h->c208 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c208,
            $g->c208,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c209, $g->c209),
            self::$GAMMAS[104]
        );
        $h->c209 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c208, $g->c209, $f->c209, $g->c208);
        $h->c210 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c210,
            $g->c210,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c211, $g->c211),
            self::$GAMMAS[105]
        );
        $h->c211 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c210, $g->c211, $f->c211, $g->c210);
        $h->c212 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c212,
            $g->c212,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c213, $g->c213),
            self::$GAMMAS[106]
        );
        $h->c213 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c212, $g->c213, $f->c213, $g->c212);
        $h->c214 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c214,
            $g->c214,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c215, $g->c215),
            self::$GAMMAS[107]
        );
        $h->c215 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c214, $g->c215, $f->c215, $g->c214);
        $h->c216 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c216,
            $g->c216,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c217, $g->c217),
            self::$GAMMAS[108]
        );
        $h->c217 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c216, $g->c217, $f->c217, $g->c216);
        $h->c218 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c218,
            $g->c218,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c219, $g->c219),
            self::$GAMMAS[109]
        );
        $h->c219 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c218, $g->c219, $f->c219, $g->c218);
        $h->c220 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c220,
            $g->c220,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c221, $g->c221),
            self::$GAMMAS[110]
        );
        $h->c221 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c220, $g->c221, $f->c221, $g->c220);
        $h->c222 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c222,
            $g->c222,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c223, $g->c223),
            self::$GAMMAS[111]
        );
        $h->c223 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c222, $g->c223, $f->c223, $g->c222);
        $h->c224 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c224,
            $g->c224,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c225, $g->c225),
            self::$GAMMAS[112]
        );
        $h->c225 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c224, $g->c225, $f->c225, $g->c224);
        $h->c226 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c226,
            $g->c226,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c227, $g->c227),
            self::$GAMMAS[113]
        );
        $h->c227 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c226, $g->c227, $f->c227, $g->c226);
        $h->c228 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c228,
            $g->c228,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c229, $g->c229),
            self::$GAMMAS[114]
        );
        $h->c229 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c228, $g->c229, $f->c229, $g->c228);
        $h->c230 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c230,
            $g->c230,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c231, $g->c231),
            self::$GAMMAS[115]
        );
        $h->c231 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c230, $g->c231, $f->c231, $g->c230);
        $h->c232 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c232,
            $g->c232,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c233, $g->c233),
            self::$GAMMAS[116]
        );
        $h->c233 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c232, $g->c233, $f->c233, $g->c232);
        $h->c234 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c234,
            $g->c234,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c235, $g->c235),
            self::$GAMMAS[117]
        );
        $h->c235 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c234, $g->c235, $f->c235, $g->c234);
        $h->c236 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c236,
            $g->c236,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c237, $g->c237),
            self::$GAMMAS[118]
        );
        $h->c237 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c236, $g->c237, $f->c237, $g->c236);
        $h->c238 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c238,
            $g->c238,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c239, $g->c239),
            self::$GAMMAS[119]
        );
        $h->c239 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c238, $g->c239, $f->c239, $g->c238);
        $h->c240 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c240,
            $g->c240,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c241, $g->c241),
            self::$GAMMAS[120]
        );
        $h->c241 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c240, $g->c241, $f->c241, $g->c240);
        $h->c242 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c242,
            $g->c242,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c243, $g->c243),
            self::$GAMMAS[121]
        );
        $h->c243 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c242, $g->c243, $f->c243, $g->c242);
        $h->c244 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c244,
            $g->c244,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c245, $g->c245),
            self::$GAMMAS[122]
        );
        $h->c245 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c244, $g->c245, $f->c245, $g->c244);
        $h->c246 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c246,
            $g->c246,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c247, $g->c247),
            self::$GAMMAS[123]
        );
        $h->c247 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c246, $g->c247, $f->c247, $g->c246);
        $h->c248 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c248,
            $g->c248,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c249, $g->c249),
            self::$GAMMAS[124]
        );
        $h->c249 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c248, $g->c249, $f->c249, $g->c248);
        $h->c250 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c250,
            $g->c250,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c251, $g->c251),
            self::$GAMMAS[125]
        );
        $h->c251 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c250, $g->c251, $f->c251, $g->c250);
        $h->c252 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c252,
            $g->c252,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c253, $g->c253),
            self::$GAMMAS[126]
        );
        $h->c253 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c252, $g->c253, $f->c253, $g->c252);
        $h->c254 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul(
            $f->c254,
            $g->c254,
            ParagonIE_Sodium_Core_MLKem_FieldElement::mul($f->c255, $g->c255),
            self::$GAMMAS[127]
        );
        $h->c255 = ParagonIE_Sodium_Core_MLKem_FieldElement::addMul($f->c254, $g->c255, $f->c255, $g->c254);
        return $h;
    }

    /**
     * FIPS 203, Algorithm 9
     *
     * @param ParagonIE_Sodium_Core_MLKem_RingElement $f
     * @return ParagonIE_Sodium_Core_MLKem_NttElement
     */
    public static function ntt($f)
    {
        $c = $f->toArray();
        $k = 1;
        for ($len = 128; $len >= 2; $len >>= 1) {
            for ($start = 0; $start < 256; $start += 2 * $len) {
                $zeta = self::$ZETAS[$k++];
                for ($j = 0; $j < $len; $j++) {
                    $p = $start + $j;
                    $q = $start + $len + $j;
                    $a = $c[$p];
                    $t = ParagonIE_Sodium_Core_MLKem_FieldElement::mul($zeta, $c[$q]);
                    $c[$p] = ParagonIE_Sodium_Core_MLKem_FieldElement::add($a, $t);
                    $c[$q] = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($a, $t);
                }
            }
        }
        $poly = new ParagonIE_Sodium_Core_MLKem_NttElement();
        foreach ($c as $index => $coefficient) {
            $poly->{'c' . $index} = $coefficient;
        }
        return $poly;
    }

    /**
     * FIPS 203, Algorithm 10
     *
     * @param ParagonIE_Sodium_Core_MLKem_NttElement $f
     * @return ParagonIE_Sodium_Core_MLKem_RingElement
     */
    public static function inverseNTT($f)
    {
        $c = $f->toArray();
        $k = 127;
        for ($len = 2; $len <= 128; $len <<= 1) {
            for ($start = 0; $start < 256; $start += 2 * $len) {
                $zeta = self::$ZETAS[$k--];
                for ($j = 0; $j < $len; $j++) {
                    $p = $start + $j;
                    $q = $start + $len + $j;
                    $t = $c[$p];
                    $c[$p] = ParagonIE_Sodium_Core_MLKem_FieldElement::add($t, $c[$q]);
                    $c[$q] = ParagonIE_Sodium_Core_MLKem_FieldElement::mulSub($zeta, $c[$q], $t);
                }
            }
        }
        for ($i = 0; $i < 256; $i++) {
            $c[$i] = ParagonIE_Sodium_Core_MLKem_FieldElement::mul($c[$i], 3303);
        }
        $poly = new ParagonIE_Sodium_Core_MLKem_RingElement();
        foreach ($c as $index => $coefficient) {
            $poly->{'c' . $index} = $coefficient;
        }
        return $poly;
    }

    /**
     * FIPS 203, Algorithm 7
     *
     * @param string $rho
     * @param int $ii
     * @param int $jj
     * @return ParagonIE_Sodium_Core_MLKem_NttElement
     */
    public static function sampleNTT($rho, $ii, $jj)
    {
        $xof = ParagonIE_Sodium_Core_Keccak::shake128();
        $xof->absorb($rho . self::intToChr($ii) . self::intToChr($jj));
        $buf = $xof->squeeze(504);
        $bufLen = 504;
        $bufIdx = 0;
        $a = ParagonIE_Sodium_Core_MLKem_NttElement::zero();
        $j = 0;
        while ($j < 256) {
            if ($bufIdx + 3 > $bufLen) {
                $buf .= $xof->squeeze(168);
                $bufLen += 168;
            }
            $b0 = self::chrToInt($buf[$bufIdx]);
            $b1 = self::chrToInt($buf[$bufIdx + 1]);
            $b2 = self::chrToInt($buf[$bufIdx + 2]);
            $bufIdx += 3;
            $d1 = ($b0 | $b1 << 8) & 0xfff;
            $d2 = ($b1 | $b2 << 8) >> 4 & 0xfff;
            if ($d1 < ParagonIE_Sodium_Core_MLKem_FieldElement::Q) {
                $a[$j++] = $d1;
                if ($j >= 256) {
                    break;
                }
            }
            if ($d2 < ParagonIE_Sodium_Core_MLKem_FieldElement::Q) {
                $a[$j++] = $d2;
            }
        }
        return $a;
    }

    /**
     * FIPS 203, Algorithm 8
     *
     * @throws SodiumException
     *
     * @param string $sigma
     * @param int $counter
     * @param int $eta
     * @return ParagonIE_Sodium_Core_MLKem_RingElement
     */
    public static function samplePolyCBD($sigma, $counter, $eta)
    {
        $prf = ParagonIE_Sodium_Core_Keccak::shake256();
        $prf->absorb($sigma . self::intToChr($counter));
        $B = $prf->squeeze(64 * $eta);
        $f = ParagonIE_Sodium_Core_MLKem_RingElement::zero();
        if ($eta === 2) {
            // 4 bits per coefficient, 2 coefficients per byte.
            for ($i = 0; $i < 256; $i += 2) {
                $b = self::chrToInt($B[$i >> 1]);
                $f[$i] = ParagonIE_Sodium_Core_MLKem_FieldElement::sub(
                    ($b & 1) + ($b >> 1 & 1),
                    ($b >> 2 & 1) + ($b >> 3 & 1)
                );
                $f[$i + 1] = ParagonIE_Sodium_Core_MLKem_FieldElement::sub(
                    ($b >> 4 & 1) + ($b >> 5 & 1),
                    ($b >> 6 & 1) + ($b >> 7 & 1)
                );
            }
            return $f;
        } elseif ($eta === 3) {
            // 6 bits per coefficient, 4 coefficients per 3 bytes.
            $byteIdx = 0;
            for ($i = 0; $i < 256; $i += 4) {
                $w = self::chrToInt($B[$byteIdx]) | self::chrToInt($B[$byteIdx + 1]) << 8 | self::chrToInt($B[$byteIdx + 2]) << 16;
                $byteIdx += 3;
                for ($j = 0; $j < 4; $j++) {
                    $bits = $w >> 6 * $j & 0x3f;
                    $x = ($bits & 1) + ($bits >> 1 & 1) + ($bits >> 2 & 1);
                    $y = ($bits >> 3 & 1) + ($bits >> 4 & 1) + ($bits >> 5 & 1);
                    $f[$i + $j] = ParagonIE_Sodium_Core_MLKem_FieldElement::sub($x, $y);
                }
            }
            return $f;
        }
        throw new SodiumException("Unsupported eta: {$eta}");
    }

    /**
     * FIPS 203, Algorithm 5
     *
     * @param ParagonIE_Sodium_Core_MLKem_NttElement $f
     * @return string
     */
    public static function polyByteEncode($f)
    {
        $b = '';
        for ($i = 0; $i < 256; $i += 2) {
            $x = $f[$i] | $f[$i + 1] << 12;
            $b .= self::intToChr($x & 0xff) . self::intToChr($x >> 8 & 0xff) . self::intToChr($x >> 16 & 0xff);
        }
        return $b;
    }

    /**
     * @throws SodiumException
     *
     * @param string $b
     * @return ParagonIE_Sodium_Core_MLKem_NttElement
     */
    public static function polyByteDecode(
        #[\SensitiveParameter]
        $b
    ) {
        if (ParagonIE_Sodium_Core_Util::strlen($b) !== 384) {
            throw new SodiumException('Invalid encoding length');
        }
        $f = ParagonIE_Sodium_Core_MLKem_NttElement::zero();
        for ($i = 0; $i < 256; $i += 2) {
            $j = ($i >> 1) * 3;
            $d = self::chrToInt($b[$j]) | self::chrToInt($b[$j + 1]) << 8 | self::chrToInt($b[$j + 2]) << 16;
            $f[$i] = ParagonIE_Sodium_Core_MLKem_FieldElement::checkReduced($d & 0xfff);
            $f[$i + 1] = ParagonIE_Sodium_Core_MLKem_FieldElement::checkReduced($d >> 12);
        }
        return $f;
    }

    /**
     * @param ParagonIE_Sodium_Core_MLKem_RingElement $f
     * @return string
     */
    public static function ringCompressAndEncode1($f)
    {
        $b = str_repeat("\x00", 32);
        for ($i = 0; $i < 256; $i++) {
            $b[$i >> 3] = self::intToChr(self::chrToInt($b[$i >> 3]) | ParagonIE_Sodium_Core_MLKem_FieldElement::compress($f[$i], 1) << ($i & 7));
        }
        return $b;
    }

    /**
     * @param string $b
     * @return ParagonIE_Sodium_Core_MLKem_RingElement
     */
    public static function ringDecodeAndDecompress1($b)
    {
        $f = ParagonIE_Sodium_Core_MLKem_RingElement::zero();
        $halfQ = ParagonIE_Sodium_Core_MLKem_FieldElement::Q + 1 >> 1;
        for ($i = 0; $i < 256; $i++) {
            $bit = self::chrToInt($b[$i >> 3]) >> ($i & 7) & 1;
            $f[$i] = $bit * $halfQ;
        }
        return $f;
    }

    /**
     * @param ParagonIE_Sodium_Core_MLKem_RingElement $f
     * @return string
     */
    public static function ringCompressAndEncode4($f)
    {
        $b = '';
        for ($i = 0; $i < 256; $i += 2) {
            $b .= self::intToChr(ParagonIE_Sodium_Core_MLKem_FieldElement::compress($f[$i], 4) | ParagonIE_Sodium_Core_MLKem_FieldElement::compress($f[$i + 1], 4) << 4);
        }
        return $b;
    }

    /**
     * @param string $b
     * @return ParagonIE_Sodium_Core_MLKem_RingElement
     */
    public static function ringDecodeAndDecompress4($b)
    {
        $f = ParagonIE_Sodium_Core_MLKem_RingElement::zero();
        for ($i = 0; $i < 256; $i += 2) {
            $byte = self::chrToInt($b[$i >> 1]);
            $f[$i] = ParagonIE_Sodium_Core_MLKem_FieldElement::decompress($byte & 0xf, 4);
            $f[$i + 1] = ParagonIE_Sodium_Core_MLKem_FieldElement::decompress($byte >> 4, 4);
        }
        return $f;
    }

    /**
     * @param ParagonIE_Sodium_Core_MLKem_RingElement $f
     * @return string
     */
    public static function ringCompressAndEncode10($f)
    {
        $b = '';
        for ($i = 0; $i < 256; $i += 4) {
            $c0 = ParagonIE_Sodium_Core_MLKem_FieldElement::compress($f[$i], 10);
            $c1 = ParagonIE_Sodium_Core_MLKem_FieldElement::compress($f[$i + 1], 10);
            $c2 = ParagonIE_Sodium_Core_MLKem_FieldElement::compress($f[$i + 2], 10);
            $c3 = ParagonIE_Sodium_Core_MLKem_FieldElement::compress($f[$i + 3], 10);
            $b .= self::intToChr($c0 & 255)
                . self::intToChr((($c0 >> 8) | ($c1 << 2)) & 255)
                . self::intToChr((($c1 >> 6) | ($c2 << 4)) & 255)
                . self::intToChr((($c2 >> 4) | ($c3 << 6)) & 255)
                . self::intToChr($c3 >> 2);
        }
        return $b;
    }

    /**
     * @param string $b
     * @return ParagonIE_Sodium_Core_MLKem_RingElement
     */
    public static function ringDecodeAndDecompress10($b)
    {
        $f = ParagonIE_Sodium_Core_MLKem_RingElement::zero();
        for ($i = 0, $j = 0; $i < 256; $i += 4, $j += 5) {
            $b0 = self::chrToInt($b[$j]);
            $b1 = self::chrToInt($b[$j + 1]);
            $b2 = self::chrToInt($b[$j + 2]);
            $b3 = self::chrToInt($b[$j + 3]);
            $b4 = self::chrToInt($b[$j + 4]);
            $f[$i] = ParagonIE_Sodium_Core_MLKem_FieldElement::decompress(($b0 | ($b1 << 8)) & 1023, 10);
            $f[$i + 1] = ParagonIE_Sodium_Core_MLKem_FieldElement::decompress((($b1 >> 2) | ($b2 << 6)) & 1023, 10);
            $f[$i + 2] = ParagonIE_Sodium_Core_MLKem_FieldElement::decompress((($b2 >> 4) | ($b3 << 4)) & 1023, 10);
            $f[$i + 3] = ParagonIE_Sodium_Core_MLKem_FieldElement::decompress(($b3 >> 6) | ($b4 << 2), 10);
        }
        return $f;
    }

    /**
     * @param ParagonIE_Sodium_Core_MLKem_RingElement $f
     * @param int $d
     * @return string
     */
    public static function ringCompressAndEncode($f, $d)
    {
        switch ($d) {
            case 1:
                return self::ringCompressAndEncode1($f);
            case 4:
                return self::ringCompressAndEncode4($f);
            case 10:
                return self::ringCompressAndEncode10($f);
            default:
                throw new SodiumException('Unsupported ML-KEM-768 bit width');
        }
    }

    /**
     * @throws SodiumException
     *
     * @param string $b
     * @param int $d
     * @return ParagonIE_Sodium_Core_MLKem_RingElement
     */
    public static function ringDecodeAndDecompress($b, $d)
    {
        switch ($d) {
            case 1:
                return self::ringDecodeAndDecompress1($b);
            case 4:
                return self::ringDecodeAndDecompress4($b);
            case 10:
                return self::ringDecodeAndDecompress10($b);
            default:
                throw new SodiumException('Unsupported ML-KEM-768 bit width');
        }
    }

    /**
     * @throws SodiumException
     *
     * @param int $k
     * @param string $ekBytes
     * @return array
     */
    public static function parseEncapsulationKey($k, $ekBytes)
    {
        $expectedLen = $k * 384 + 32;
        if (ParagonIE_Sodium_Core_Util::strlen($ekBytes) !== $expectedLen) {
            throw new SodiumException('Invalid encapsulation key length');
        }
        $h = ParagonIE_Sodium_Core_Keccak::sha3_256($ekBytes);
        $t = array();
        $offset = 0;
        for ($i = 0; $i < $k; $i++) {
            $t[$i] = self::polyByteDecode(ParagonIE_Sodium_Core_Util::substr($ekBytes, $offset, 384));
            $offset += 384;
        }
        $rho = ParagonIE_Sodium_Core_Util::substr($ekBytes, $offset, 32);
        $a = array();
        for ($i = 0; $i < $k; $i++) {
            for ($j = 0; $j < $k; $j++) {
                $a[$i * $k + $j] = self::sampleNTT($rho, $j, $i);
            }
        }
        return array('t' => $t, 'a' => $a, 'h' => $h, 'rho' => $rho);
    }

    /**
     * @throws SodiumException
     *
     * @param int $k
     * @param int $eta1
     * @param string $d
     * @param string $z
     * @return array
     */
    public static function kemKeyGen($k, $eta1, $d, $z)
    {
        $g = ParagonIE_Sodium_Core_Keccak::sha3_512($d . self::intToChr($k));
        $rho = ParagonIE_Sodium_Core_Util::substr($g, 0, 32);
        $sigma = ParagonIE_Sodium_Core_Util::substr($g, 32, 32);
        $a = array();
        for ($i = 0; $i < $k; $i++) {
            for ($j = 0; $j < $k; $j++) {
                $a[$i * $k + $j] = self::sampleNTT($rho, $j, $i);
            }
        }
        $N = 0;
        $s = array();
        for ($i = 0; $i < $k; $i++) {
            $s[$i] = self::ntt(self::samplePolyCBD($sigma, $N, $eta1));
            $N++;
        }
        $e = array();
        for ($i = 0; $i < $k; $i++) {
            $e[$i] = self::ntt(self::samplePolyCBD($sigma, $N, $eta1));
            $N++;
        }
        $t = array();
        for ($i = 0; $i < $k; $i++) {
            $t[$i] = $e[$i];
            for ($j = 0; $j < $k; $j++) {
                $t[$i] = self::polyAdd($t[$i], self::nttMul($a[$i * $k + $j], $s[$j]));
            }
        }
        $ek = '';
        for ($i = 0; $i < $k; $i++) {
            $ek .= self::polyByteEncode($t[$i]);
        }
        $ek .= $rho;
        $h = ParagonIE_Sodium_Core_Keccak::sha3_256($ek);
        return array(
            'encapsulationKeyBytes' => $ek,
            'd' => $d,
            'z' => $z,
            'rho' => $rho,
            'h' => $h,
            't' => $t,
            'a' => $a,
            's' => $s
        );
    }

    /**
     * @throws SodiumException
     *
     * @param int $k
     * @param int $eta1
     * @param int $du
     * @param int $dv
     * @param array $ek
     * @param string $m
     * @param string $rnd
     * @return string
     */
    public static function pkeEncrypt($k, $eta1, $du, $dv, $ek, $m, $rnd)
    {
        $N = 0;
        $r = array();
        for ($i = 0; $i < $k; $i++) {
            $r[$i] = self::ntt(self::samplePolyCBD($rnd, $N, $eta1));
            $N++;
        }
        $e1 = array();
        for ($i = 0; $i < $k; $i++) {
            $e1[$i] = self::samplePolyCBD($rnd, $N, 2);
            $N++;
        }
        $e2 = self::samplePolyCBD($rnd, $N, 2);
        $u = array();
        for ($i = 0; $i < $k; $i++) {
            $u[$i] = $e1[$i];
            for ($j = 0; $j < $k; $j++) {
                $u[$i] = self::polyAdd($u[$i], self::inverseNTT(self::nttMul($ek['a'][$j * $k + $i], $r[$j])));
            }
        }
        $mu = self::ringDecodeAndDecompress1($m);
        $vNTT = ParagonIE_Sodium_Core_MLKem_NttElement::zero();
        for ($i = 0; $i < $k; $i++) {
            $vNTT = self::polyAdd($vNTT, self::nttMul($ek['t'][$i], $r[$i]));
        }
        $v = self::polyAdd(self::polyAdd(self::inverseNTT($vNTT), $e2), $mu);
        // Encode ciphertext.
        $c = '';
        for ($i = 0; $i < $k; $i++) {
            $c .= self::ringCompressAndEncode($u[$i], $du);
        }
        $c .= self::ringCompressAndEncode($v, $dv);
        return $c;
    }

    /**
     * @throws SodiumException
     *
     * @param int $k
     * @param int $du
     * @param int $dv
     * @param array $s
     * @param string $ciphertext
     * @return string
     */
    public static function pkeDecrypt($k, $du, $dv, $s, $ciphertext)
    {
        $encodingDu = 32 * $du;
        $u = array();
        for ($i = 0; $i < $k; $i++) {
            $chunk = ParagonIE_Sodium_Core_Util::substr($ciphertext, $encodingDu * $i, $encodingDu);
            $u[$i] = self::ringDecodeAndDecompress($chunk, $du);
        }
        $encodingDv = 32 * $dv;
        $vBytes = ParagonIE_Sodium_Core_Util::substr($ciphertext, $encodingDu * $k, $encodingDv);
        $v = self::ringDecodeAndDecompress($vBytes, $dv);
        $mask = ParagonIE_Sodium_Core_MLKem_NttElement::zero();
        for ($i = 0; $i < $k; $i++) {
            $mask = self::polyAdd($mask, self::nttMul($s[$i], self::ntt($u[$i])));
        }
        $w = self::polySub($v, self::inverseNTT($mask));
        return self::ringCompressAndEncode1($w);
    }

    /**
     * @throws SodiumException
     *
     * @param int $k
     * @param int $eta1
     * @param int $du
     * @param int $dv
     * @param array $ek
     * @param string $m
     * @return array
     */
    public static function kemEncaps($k, $eta1, $du, $dv, $ek, $m)
    {
        $g = ParagonIE_Sodium_Core_Keccak::sha3_512($m . $ek['h']);
        $K = ParagonIE_Sodium_Core_Util::substr($g, 0, 32);
        $r = ParagonIE_Sodium_Core_Util::substr($g, 32, 32);
        $c = self::pkeEncrypt($k, $eta1, $du, $dv, $ek, $m, $r);
        return array('sharedKey' => $K, 'ciphertext' => $c);
    }

    /**
     * @throws SodiumException
     *
     * @param int $k
     * @param int $eta1
     * @param int $du
     * @param int $dv
     * @param string $z
     * @param string $h
     * @param array $encKey
     * @param array $s
     * @param string $ciphertext
     * @return string
     */
    public static function kemDecaps($k, $eta1, $du, $dv, $z, $h, $encKey, $s, $ciphertext)
    {
        $m = self::pkeDecrypt($k, $du, $dv, $s, $ciphertext);
        $g = ParagonIE_Sodium_Core_Keccak::sha3_512($m . $h);
        $Kprime = ParagonIE_Sodium_Core_Util::substr($g, 0, 32);
        $r = ParagonIE_Sodium_Core_Util::substr($g, 32, 32);
        // Implicit rejection
        $shake = ParagonIE_Sodium_Core_Keccak::shake256();
        $shake->absorb($z . $ciphertext);
        $Kout = $shake->squeeze(32);
        // Re-encrypt.
        $ek = array('t' => $encKey['t'], 'a' => $encKey['a']);
        $c1 = self::pkeEncrypt($k, $eta1, $du, $dv, $ek, $m, $r);
        // Constant-time compare and conditional copy.
        $eq = (int) ParagonIE_Sodium_Core_Util::hashEquals($ciphertext, $c1);
        $mask = -$eq;
        for ($i = 0; $i < 32; $i++) {
            $kp = self::chrToInt($Kprime[$i]);
            $ko = self::chrToInt($Kout[$i]);
            $Kout[$i] = self::intToChr($ko ^ $mask & ($kp ^ $ko));
        }
        return $Kout;
    }
}
