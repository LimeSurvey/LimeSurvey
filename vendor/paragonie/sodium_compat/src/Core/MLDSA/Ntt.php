<?php

if (class_exists('ParagonIE_Sodium_Core_MLDSA_Ntt', false)) {
    return;
}

/**
 * @internal
 * @psalm-suppress PossiblyUnusedMethod
 * @implements ArrayAccess<int, int>
 */
final class ParagonIE_Sodium_Core_MLDSA_Ntt extends ParagonIE_Sodium_Core_Object256 implements ArrayAccess
{
    /**
     * @return self
     */
    public static function zero()
    {
        return new ParagonIE_Sodium_Core_MLDSA_Ntt();
    }

    /**
     * FIPS 204, Algorithm 44
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Ntt $other
     * @return ParagonIE_Sodium_Core_MLDSA_Ntt
     * @psalm-suppress PossiblyUnusedMethod Covered by internal primitive tests.
     */
    public function add($other)
    {
        $r = new ParagonIE_Sodium_Core_MLDSA_Ntt();
        $r->c0 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c0, $other->c0);
        $r->c1 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c1, $other->c1);
        $r->c2 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c2, $other->c2);
        $r->c3 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c3, $other->c3);
        $r->c4 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c4, $other->c4);
        $r->c5 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c5, $other->c5);
        $r->c6 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c6, $other->c6);
        $r->c7 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c7, $other->c7);
        $r->c8 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c8, $other->c8);
        $r->c9 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c9, $other->c9);
        $r->c10 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c10, $other->c10);
        $r->c11 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c11, $other->c11);
        $r->c12 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c12, $other->c12);
        $r->c13 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c13, $other->c13);
        $r->c14 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c14, $other->c14);
        $r->c15 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c15, $other->c15);
        $r->c16 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c16, $other->c16);
        $r->c17 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c17, $other->c17);
        $r->c18 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c18, $other->c18);
        $r->c19 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c19, $other->c19);
        $r->c20 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c20, $other->c20);
        $r->c21 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c21, $other->c21);
        $r->c22 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c22, $other->c22);
        $r->c23 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c23, $other->c23);
        $r->c24 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c24, $other->c24);
        $r->c25 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c25, $other->c25);
        $r->c26 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c26, $other->c26);
        $r->c27 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c27, $other->c27);
        $r->c28 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c28, $other->c28);
        $r->c29 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c29, $other->c29);
        $r->c30 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c30, $other->c30);
        $r->c31 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c31, $other->c31);
        $r->c32 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c32, $other->c32);
        $r->c33 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c33, $other->c33);
        $r->c34 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c34, $other->c34);
        $r->c35 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c35, $other->c35);
        $r->c36 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c36, $other->c36);
        $r->c37 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c37, $other->c37);
        $r->c38 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c38, $other->c38);
        $r->c39 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c39, $other->c39);
        $r->c40 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c40, $other->c40);
        $r->c41 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c41, $other->c41);
        $r->c42 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c42, $other->c42);
        $r->c43 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c43, $other->c43);
        $r->c44 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c44, $other->c44);
        $r->c45 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c45, $other->c45);
        $r->c46 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c46, $other->c46);
        $r->c47 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c47, $other->c47);
        $r->c48 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c48, $other->c48);
        $r->c49 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c49, $other->c49);
        $r->c50 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c50, $other->c50);
        $r->c51 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c51, $other->c51);
        $r->c52 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c52, $other->c52);
        $r->c53 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c53, $other->c53);
        $r->c54 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c54, $other->c54);
        $r->c55 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c55, $other->c55);
        $r->c56 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c56, $other->c56);
        $r->c57 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c57, $other->c57);
        $r->c58 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c58, $other->c58);
        $r->c59 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c59, $other->c59);
        $r->c60 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c60, $other->c60);
        $r->c61 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c61, $other->c61);
        $r->c62 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c62, $other->c62);
        $r->c63 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c63, $other->c63);
        $r->c64 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c64, $other->c64);
        $r->c65 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c65, $other->c65);
        $r->c66 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c66, $other->c66);
        $r->c67 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c67, $other->c67);
        $r->c68 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c68, $other->c68);
        $r->c69 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c69, $other->c69);
        $r->c70 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c70, $other->c70);
        $r->c71 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c71, $other->c71);
        $r->c72 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c72, $other->c72);
        $r->c73 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c73, $other->c73);
        $r->c74 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c74, $other->c74);
        $r->c75 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c75, $other->c75);
        $r->c76 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c76, $other->c76);
        $r->c77 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c77, $other->c77);
        $r->c78 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c78, $other->c78);
        $r->c79 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c79, $other->c79);
        $r->c80 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c80, $other->c80);
        $r->c81 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c81, $other->c81);
        $r->c82 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c82, $other->c82);
        $r->c83 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c83, $other->c83);
        $r->c84 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c84, $other->c84);
        $r->c85 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c85, $other->c85);
        $r->c86 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c86, $other->c86);
        $r->c87 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c87, $other->c87);
        $r->c88 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c88, $other->c88);
        $r->c89 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c89, $other->c89);
        $r->c90 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c90, $other->c90);
        $r->c91 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c91, $other->c91);
        $r->c92 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c92, $other->c92);
        $r->c93 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c93, $other->c93);
        $r->c94 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c94, $other->c94);
        $r->c95 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c95, $other->c95);
        $r->c96 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c96, $other->c96);
        $r->c97 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c97, $other->c97);
        $r->c98 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c98, $other->c98);
        $r->c99 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c99, $other->c99);
        $r->c100 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c100, $other->c100);
        $r->c101 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c101, $other->c101);
        $r->c102 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c102, $other->c102);
        $r->c103 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c103, $other->c103);
        $r->c104 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c104, $other->c104);
        $r->c105 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c105, $other->c105);
        $r->c106 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c106, $other->c106);
        $r->c107 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c107, $other->c107);
        $r->c108 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c108, $other->c108);
        $r->c109 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c109, $other->c109);
        $r->c110 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c110, $other->c110);
        $r->c111 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c111, $other->c111);
        $r->c112 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c112, $other->c112);
        $r->c113 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c113, $other->c113);
        $r->c114 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c114, $other->c114);
        $r->c115 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c115, $other->c115);
        $r->c116 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c116, $other->c116);
        $r->c117 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c117, $other->c117);
        $r->c118 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c118, $other->c118);
        $r->c119 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c119, $other->c119);
        $r->c120 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c120, $other->c120);
        $r->c121 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c121, $other->c121);
        $r->c122 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c122, $other->c122);
        $r->c123 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c123, $other->c123);
        $r->c124 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c124, $other->c124);
        $r->c125 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c125, $other->c125);
        $r->c126 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c126, $other->c126);
        $r->c127 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c127, $other->c127);
        $r->c128 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c128, $other->c128);
        $r->c129 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c129, $other->c129);
        $r->c130 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c130, $other->c130);
        $r->c131 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c131, $other->c131);
        $r->c132 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c132, $other->c132);
        $r->c133 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c133, $other->c133);
        $r->c134 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c134, $other->c134);
        $r->c135 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c135, $other->c135);
        $r->c136 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c136, $other->c136);
        $r->c137 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c137, $other->c137);
        $r->c138 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c138, $other->c138);
        $r->c139 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c139, $other->c139);
        $r->c140 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c140, $other->c140);
        $r->c141 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c141, $other->c141);
        $r->c142 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c142, $other->c142);
        $r->c143 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c143, $other->c143);
        $r->c144 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c144, $other->c144);
        $r->c145 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c145, $other->c145);
        $r->c146 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c146, $other->c146);
        $r->c147 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c147, $other->c147);
        $r->c148 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c148, $other->c148);
        $r->c149 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c149, $other->c149);
        $r->c150 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c150, $other->c150);
        $r->c151 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c151, $other->c151);
        $r->c152 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c152, $other->c152);
        $r->c153 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c153, $other->c153);
        $r->c154 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c154, $other->c154);
        $r->c155 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c155, $other->c155);
        $r->c156 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c156, $other->c156);
        $r->c157 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c157, $other->c157);
        $r->c158 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c158, $other->c158);
        $r->c159 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c159, $other->c159);
        $r->c160 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c160, $other->c160);
        $r->c161 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c161, $other->c161);
        $r->c162 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c162, $other->c162);
        $r->c163 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c163, $other->c163);
        $r->c164 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c164, $other->c164);
        $r->c165 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c165, $other->c165);
        $r->c166 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c166, $other->c166);
        $r->c167 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c167, $other->c167);
        $r->c168 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c168, $other->c168);
        $r->c169 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c169, $other->c169);
        $r->c170 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c170, $other->c170);
        $r->c171 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c171, $other->c171);
        $r->c172 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c172, $other->c172);
        $r->c173 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c173, $other->c173);
        $r->c174 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c174, $other->c174);
        $r->c175 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c175, $other->c175);
        $r->c176 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c176, $other->c176);
        $r->c177 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c177, $other->c177);
        $r->c178 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c178, $other->c178);
        $r->c179 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c179, $other->c179);
        $r->c180 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c180, $other->c180);
        $r->c181 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c181, $other->c181);
        $r->c182 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c182, $other->c182);
        $r->c183 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c183, $other->c183);
        $r->c184 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c184, $other->c184);
        $r->c185 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c185, $other->c185);
        $r->c186 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c186, $other->c186);
        $r->c187 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c187, $other->c187);
        $r->c188 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c188, $other->c188);
        $r->c189 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c189, $other->c189);
        $r->c190 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c190, $other->c190);
        $r->c191 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c191, $other->c191);
        $r->c192 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c192, $other->c192);
        $r->c193 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c193, $other->c193);
        $r->c194 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c194, $other->c194);
        $r->c195 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c195, $other->c195);
        $r->c196 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c196, $other->c196);
        $r->c197 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c197, $other->c197);
        $r->c198 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c198, $other->c198);
        $r->c199 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c199, $other->c199);
        $r->c200 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c200, $other->c200);
        $r->c201 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c201, $other->c201);
        $r->c202 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c202, $other->c202);
        $r->c203 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c203, $other->c203);
        $r->c204 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c204, $other->c204);
        $r->c205 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c205, $other->c205);
        $r->c206 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c206, $other->c206);
        $r->c207 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c207, $other->c207);
        $r->c208 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c208, $other->c208);
        $r->c209 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c209, $other->c209);
        $r->c210 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c210, $other->c210);
        $r->c211 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c211, $other->c211);
        $r->c212 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c212, $other->c212);
        $r->c213 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c213, $other->c213);
        $r->c214 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c214, $other->c214);
        $r->c215 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c215, $other->c215);
        $r->c216 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c216, $other->c216);
        $r->c217 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c217, $other->c217);
        $r->c218 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c218, $other->c218);
        $r->c219 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c219, $other->c219);
        $r->c220 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c220, $other->c220);
        $r->c221 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c221, $other->c221);
        $r->c222 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c222, $other->c222);
        $r->c223 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c223, $other->c223);
        $r->c224 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c224, $other->c224);
        $r->c225 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c225, $other->c225);
        $r->c226 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c226, $other->c226);
        $r->c227 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c227, $other->c227);
        $r->c228 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c228, $other->c228);
        $r->c229 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c229, $other->c229);
        $r->c230 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c230, $other->c230);
        $r->c231 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c231, $other->c231);
        $r->c232 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c232, $other->c232);
        $r->c233 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c233, $other->c233);
        $r->c234 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c234, $other->c234);
        $r->c235 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c235, $other->c235);
        $r->c236 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c236, $other->c236);
        $r->c237 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c237, $other->c237);
        $r->c238 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c238, $other->c238);
        $r->c239 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c239, $other->c239);
        $r->c240 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c240, $other->c240);
        $r->c241 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c241, $other->c241);
        $r->c242 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c242, $other->c242);
        $r->c243 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c243, $other->c243);
        $r->c244 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c244, $other->c244);
        $r->c245 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c245, $other->c245);
        $r->c246 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c246, $other->c246);
        $r->c247 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c247, $other->c247);
        $r->c248 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c248, $other->c248);
        $r->c249 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c249, $other->c249);
        $r->c250 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c250, $other->c250);
        $r->c251 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c251, $other->c251);
        $r->c252 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c252, $other->c252);
        $r->c253 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c253, $other->c253);
        $r->c254 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c254, $other->c254);
        $r->c255 = ParagonIE_Sodium_Core_MLDSA_Field::add($this->c255, $other->c255);
        return $r;
    }

    /**
     * @param ParagonIE_Sodium_Core_MLDSA_Ntt $other
     * @return ParagonIE_Sodium_Core_MLDSA_Ntt
     */
    public function sub($other)
    {
        $r = new ParagonIE_Sodium_Core_MLDSA_Ntt();
        $r->c0 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c0, $other->c0);
        $r->c1 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c1, $other->c1);
        $r->c2 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c2, $other->c2);
        $r->c3 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c3, $other->c3);
        $r->c4 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c4, $other->c4);
        $r->c5 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c5, $other->c5);
        $r->c6 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c6, $other->c6);
        $r->c7 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c7, $other->c7);
        $r->c8 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c8, $other->c8);
        $r->c9 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c9, $other->c9);
        $r->c10 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c10, $other->c10);
        $r->c11 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c11, $other->c11);
        $r->c12 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c12, $other->c12);
        $r->c13 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c13, $other->c13);
        $r->c14 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c14, $other->c14);
        $r->c15 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c15, $other->c15);
        $r->c16 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c16, $other->c16);
        $r->c17 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c17, $other->c17);
        $r->c18 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c18, $other->c18);
        $r->c19 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c19, $other->c19);
        $r->c20 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c20, $other->c20);
        $r->c21 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c21, $other->c21);
        $r->c22 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c22, $other->c22);
        $r->c23 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c23, $other->c23);
        $r->c24 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c24, $other->c24);
        $r->c25 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c25, $other->c25);
        $r->c26 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c26, $other->c26);
        $r->c27 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c27, $other->c27);
        $r->c28 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c28, $other->c28);
        $r->c29 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c29, $other->c29);
        $r->c30 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c30, $other->c30);
        $r->c31 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c31, $other->c31);
        $r->c32 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c32, $other->c32);
        $r->c33 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c33, $other->c33);
        $r->c34 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c34, $other->c34);
        $r->c35 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c35, $other->c35);
        $r->c36 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c36, $other->c36);
        $r->c37 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c37, $other->c37);
        $r->c38 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c38, $other->c38);
        $r->c39 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c39, $other->c39);
        $r->c40 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c40, $other->c40);
        $r->c41 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c41, $other->c41);
        $r->c42 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c42, $other->c42);
        $r->c43 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c43, $other->c43);
        $r->c44 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c44, $other->c44);
        $r->c45 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c45, $other->c45);
        $r->c46 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c46, $other->c46);
        $r->c47 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c47, $other->c47);
        $r->c48 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c48, $other->c48);
        $r->c49 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c49, $other->c49);
        $r->c50 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c50, $other->c50);
        $r->c51 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c51, $other->c51);
        $r->c52 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c52, $other->c52);
        $r->c53 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c53, $other->c53);
        $r->c54 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c54, $other->c54);
        $r->c55 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c55, $other->c55);
        $r->c56 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c56, $other->c56);
        $r->c57 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c57, $other->c57);
        $r->c58 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c58, $other->c58);
        $r->c59 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c59, $other->c59);
        $r->c60 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c60, $other->c60);
        $r->c61 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c61, $other->c61);
        $r->c62 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c62, $other->c62);
        $r->c63 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c63, $other->c63);
        $r->c64 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c64, $other->c64);
        $r->c65 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c65, $other->c65);
        $r->c66 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c66, $other->c66);
        $r->c67 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c67, $other->c67);
        $r->c68 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c68, $other->c68);
        $r->c69 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c69, $other->c69);
        $r->c70 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c70, $other->c70);
        $r->c71 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c71, $other->c71);
        $r->c72 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c72, $other->c72);
        $r->c73 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c73, $other->c73);
        $r->c74 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c74, $other->c74);
        $r->c75 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c75, $other->c75);
        $r->c76 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c76, $other->c76);
        $r->c77 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c77, $other->c77);
        $r->c78 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c78, $other->c78);
        $r->c79 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c79, $other->c79);
        $r->c80 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c80, $other->c80);
        $r->c81 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c81, $other->c81);
        $r->c82 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c82, $other->c82);
        $r->c83 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c83, $other->c83);
        $r->c84 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c84, $other->c84);
        $r->c85 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c85, $other->c85);
        $r->c86 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c86, $other->c86);
        $r->c87 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c87, $other->c87);
        $r->c88 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c88, $other->c88);
        $r->c89 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c89, $other->c89);
        $r->c90 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c90, $other->c90);
        $r->c91 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c91, $other->c91);
        $r->c92 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c92, $other->c92);
        $r->c93 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c93, $other->c93);
        $r->c94 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c94, $other->c94);
        $r->c95 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c95, $other->c95);
        $r->c96 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c96, $other->c96);
        $r->c97 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c97, $other->c97);
        $r->c98 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c98, $other->c98);
        $r->c99 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c99, $other->c99);
        $r->c100 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c100, $other->c100);
        $r->c101 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c101, $other->c101);
        $r->c102 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c102, $other->c102);
        $r->c103 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c103, $other->c103);
        $r->c104 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c104, $other->c104);
        $r->c105 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c105, $other->c105);
        $r->c106 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c106, $other->c106);
        $r->c107 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c107, $other->c107);
        $r->c108 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c108, $other->c108);
        $r->c109 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c109, $other->c109);
        $r->c110 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c110, $other->c110);
        $r->c111 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c111, $other->c111);
        $r->c112 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c112, $other->c112);
        $r->c113 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c113, $other->c113);
        $r->c114 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c114, $other->c114);
        $r->c115 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c115, $other->c115);
        $r->c116 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c116, $other->c116);
        $r->c117 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c117, $other->c117);
        $r->c118 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c118, $other->c118);
        $r->c119 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c119, $other->c119);
        $r->c120 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c120, $other->c120);
        $r->c121 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c121, $other->c121);
        $r->c122 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c122, $other->c122);
        $r->c123 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c123, $other->c123);
        $r->c124 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c124, $other->c124);
        $r->c125 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c125, $other->c125);
        $r->c126 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c126, $other->c126);
        $r->c127 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c127, $other->c127);
        $r->c128 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c128, $other->c128);
        $r->c129 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c129, $other->c129);
        $r->c130 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c130, $other->c130);
        $r->c131 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c131, $other->c131);
        $r->c132 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c132, $other->c132);
        $r->c133 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c133, $other->c133);
        $r->c134 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c134, $other->c134);
        $r->c135 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c135, $other->c135);
        $r->c136 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c136, $other->c136);
        $r->c137 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c137, $other->c137);
        $r->c138 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c138, $other->c138);
        $r->c139 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c139, $other->c139);
        $r->c140 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c140, $other->c140);
        $r->c141 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c141, $other->c141);
        $r->c142 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c142, $other->c142);
        $r->c143 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c143, $other->c143);
        $r->c144 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c144, $other->c144);
        $r->c145 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c145, $other->c145);
        $r->c146 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c146, $other->c146);
        $r->c147 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c147, $other->c147);
        $r->c148 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c148, $other->c148);
        $r->c149 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c149, $other->c149);
        $r->c150 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c150, $other->c150);
        $r->c151 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c151, $other->c151);
        $r->c152 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c152, $other->c152);
        $r->c153 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c153, $other->c153);
        $r->c154 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c154, $other->c154);
        $r->c155 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c155, $other->c155);
        $r->c156 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c156, $other->c156);
        $r->c157 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c157, $other->c157);
        $r->c158 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c158, $other->c158);
        $r->c159 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c159, $other->c159);
        $r->c160 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c160, $other->c160);
        $r->c161 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c161, $other->c161);
        $r->c162 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c162, $other->c162);
        $r->c163 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c163, $other->c163);
        $r->c164 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c164, $other->c164);
        $r->c165 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c165, $other->c165);
        $r->c166 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c166, $other->c166);
        $r->c167 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c167, $other->c167);
        $r->c168 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c168, $other->c168);
        $r->c169 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c169, $other->c169);
        $r->c170 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c170, $other->c170);
        $r->c171 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c171, $other->c171);
        $r->c172 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c172, $other->c172);
        $r->c173 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c173, $other->c173);
        $r->c174 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c174, $other->c174);
        $r->c175 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c175, $other->c175);
        $r->c176 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c176, $other->c176);
        $r->c177 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c177, $other->c177);
        $r->c178 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c178, $other->c178);
        $r->c179 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c179, $other->c179);
        $r->c180 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c180, $other->c180);
        $r->c181 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c181, $other->c181);
        $r->c182 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c182, $other->c182);
        $r->c183 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c183, $other->c183);
        $r->c184 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c184, $other->c184);
        $r->c185 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c185, $other->c185);
        $r->c186 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c186, $other->c186);
        $r->c187 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c187, $other->c187);
        $r->c188 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c188, $other->c188);
        $r->c189 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c189, $other->c189);
        $r->c190 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c190, $other->c190);
        $r->c191 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c191, $other->c191);
        $r->c192 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c192, $other->c192);
        $r->c193 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c193, $other->c193);
        $r->c194 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c194, $other->c194);
        $r->c195 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c195, $other->c195);
        $r->c196 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c196, $other->c196);
        $r->c197 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c197, $other->c197);
        $r->c198 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c198, $other->c198);
        $r->c199 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c199, $other->c199);
        $r->c200 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c200, $other->c200);
        $r->c201 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c201, $other->c201);
        $r->c202 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c202, $other->c202);
        $r->c203 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c203, $other->c203);
        $r->c204 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c204, $other->c204);
        $r->c205 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c205, $other->c205);
        $r->c206 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c206, $other->c206);
        $r->c207 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c207, $other->c207);
        $r->c208 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c208, $other->c208);
        $r->c209 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c209, $other->c209);
        $r->c210 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c210, $other->c210);
        $r->c211 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c211, $other->c211);
        $r->c212 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c212, $other->c212);
        $r->c213 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c213, $other->c213);
        $r->c214 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c214, $other->c214);
        $r->c215 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c215, $other->c215);
        $r->c216 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c216, $other->c216);
        $r->c217 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c217, $other->c217);
        $r->c218 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c218, $other->c218);
        $r->c219 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c219, $other->c219);
        $r->c220 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c220, $other->c220);
        $r->c221 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c221, $other->c221);
        $r->c222 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c222, $other->c222);
        $r->c223 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c223, $other->c223);
        $r->c224 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c224, $other->c224);
        $r->c225 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c225, $other->c225);
        $r->c226 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c226, $other->c226);
        $r->c227 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c227, $other->c227);
        $r->c228 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c228, $other->c228);
        $r->c229 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c229, $other->c229);
        $r->c230 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c230, $other->c230);
        $r->c231 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c231, $other->c231);
        $r->c232 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c232, $other->c232);
        $r->c233 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c233, $other->c233);
        $r->c234 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c234, $other->c234);
        $r->c235 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c235, $other->c235);
        $r->c236 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c236, $other->c236);
        $r->c237 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c237, $other->c237);
        $r->c238 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c238, $other->c238);
        $r->c239 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c239, $other->c239);
        $r->c240 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c240, $other->c240);
        $r->c241 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c241, $other->c241);
        $r->c242 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c242, $other->c242);
        $r->c243 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c243, $other->c243);
        $r->c244 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c244, $other->c244);
        $r->c245 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c245, $other->c245);
        $r->c246 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c246, $other->c246);
        $r->c247 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c247, $other->c247);
        $r->c248 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c248, $other->c248);
        $r->c249 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c249, $other->c249);
        $r->c250 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c250, $other->c250);
        $r->c251 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c251, $other->c251);
        $r->c252 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c252, $other->c252);
        $r->c253 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c253, $other->c253);
        $r->c254 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c254, $other->c254);
        $r->c255 = ParagonIE_Sodium_Core_MLDSA_Field::sub($this->c255, $other->c255);
        return $r;
    }

    /**
     * FIPS 204, Algorithm 45
     *
     * @throws SodiumException
     *
     * @param ParagonIE_Sodium_Core_MLDSA_Ntt $other
     * @return ParagonIE_Sodium_Core_MLDSA_Ntt
     * @psalm-suppress PossiblyUnusedMethod Covered by internal primitive tests.
     */
    public function mul($other)
    {
        $r = new ParagonIE_Sodium_Core_MLDSA_Ntt();
        $r->c0 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c0, $other->c0);
        $r->c1 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c1, $other->c1);
        $r->c2 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c2, $other->c2);
        $r->c3 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c3, $other->c3);
        $r->c4 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c4, $other->c4);
        $r->c5 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c5, $other->c5);
        $r->c6 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c6, $other->c6);
        $r->c7 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c7, $other->c7);
        $r->c8 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c8, $other->c8);
        $r->c9 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c9, $other->c9);
        $r->c10 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c10, $other->c10);
        $r->c11 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c11, $other->c11);
        $r->c12 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c12, $other->c12);
        $r->c13 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c13, $other->c13);
        $r->c14 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c14, $other->c14);
        $r->c15 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c15, $other->c15);
        $r->c16 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c16, $other->c16);
        $r->c17 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c17, $other->c17);
        $r->c18 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c18, $other->c18);
        $r->c19 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c19, $other->c19);
        $r->c20 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c20, $other->c20);
        $r->c21 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c21, $other->c21);
        $r->c22 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c22, $other->c22);
        $r->c23 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c23, $other->c23);
        $r->c24 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c24, $other->c24);
        $r->c25 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c25, $other->c25);
        $r->c26 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c26, $other->c26);
        $r->c27 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c27, $other->c27);
        $r->c28 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c28, $other->c28);
        $r->c29 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c29, $other->c29);
        $r->c30 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c30, $other->c30);
        $r->c31 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c31, $other->c31);
        $r->c32 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c32, $other->c32);
        $r->c33 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c33, $other->c33);
        $r->c34 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c34, $other->c34);
        $r->c35 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c35, $other->c35);
        $r->c36 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c36, $other->c36);
        $r->c37 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c37, $other->c37);
        $r->c38 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c38, $other->c38);
        $r->c39 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c39, $other->c39);
        $r->c40 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c40, $other->c40);
        $r->c41 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c41, $other->c41);
        $r->c42 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c42, $other->c42);
        $r->c43 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c43, $other->c43);
        $r->c44 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c44, $other->c44);
        $r->c45 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c45, $other->c45);
        $r->c46 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c46, $other->c46);
        $r->c47 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c47, $other->c47);
        $r->c48 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c48, $other->c48);
        $r->c49 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c49, $other->c49);
        $r->c50 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c50, $other->c50);
        $r->c51 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c51, $other->c51);
        $r->c52 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c52, $other->c52);
        $r->c53 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c53, $other->c53);
        $r->c54 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c54, $other->c54);
        $r->c55 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c55, $other->c55);
        $r->c56 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c56, $other->c56);
        $r->c57 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c57, $other->c57);
        $r->c58 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c58, $other->c58);
        $r->c59 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c59, $other->c59);
        $r->c60 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c60, $other->c60);
        $r->c61 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c61, $other->c61);
        $r->c62 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c62, $other->c62);
        $r->c63 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c63, $other->c63);
        $r->c64 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c64, $other->c64);
        $r->c65 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c65, $other->c65);
        $r->c66 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c66, $other->c66);
        $r->c67 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c67, $other->c67);
        $r->c68 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c68, $other->c68);
        $r->c69 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c69, $other->c69);
        $r->c70 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c70, $other->c70);
        $r->c71 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c71, $other->c71);
        $r->c72 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c72, $other->c72);
        $r->c73 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c73, $other->c73);
        $r->c74 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c74, $other->c74);
        $r->c75 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c75, $other->c75);
        $r->c76 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c76, $other->c76);
        $r->c77 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c77, $other->c77);
        $r->c78 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c78, $other->c78);
        $r->c79 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c79, $other->c79);
        $r->c80 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c80, $other->c80);
        $r->c81 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c81, $other->c81);
        $r->c82 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c82, $other->c82);
        $r->c83 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c83, $other->c83);
        $r->c84 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c84, $other->c84);
        $r->c85 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c85, $other->c85);
        $r->c86 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c86, $other->c86);
        $r->c87 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c87, $other->c87);
        $r->c88 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c88, $other->c88);
        $r->c89 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c89, $other->c89);
        $r->c90 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c90, $other->c90);
        $r->c91 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c91, $other->c91);
        $r->c92 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c92, $other->c92);
        $r->c93 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c93, $other->c93);
        $r->c94 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c94, $other->c94);
        $r->c95 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c95, $other->c95);
        $r->c96 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c96, $other->c96);
        $r->c97 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c97, $other->c97);
        $r->c98 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c98, $other->c98);
        $r->c99 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c99, $other->c99);
        $r->c100 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c100, $other->c100);
        $r->c101 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c101, $other->c101);
        $r->c102 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c102, $other->c102);
        $r->c103 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c103, $other->c103);
        $r->c104 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c104, $other->c104);
        $r->c105 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c105, $other->c105);
        $r->c106 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c106, $other->c106);
        $r->c107 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c107, $other->c107);
        $r->c108 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c108, $other->c108);
        $r->c109 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c109, $other->c109);
        $r->c110 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c110, $other->c110);
        $r->c111 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c111, $other->c111);
        $r->c112 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c112, $other->c112);
        $r->c113 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c113, $other->c113);
        $r->c114 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c114, $other->c114);
        $r->c115 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c115, $other->c115);
        $r->c116 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c116, $other->c116);
        $r->c117 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c117, $other->c117);
        $r->c118 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c118, $other->c118);
        $r->c119 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c119, $other->c119);
        $r->c120 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c120, $other->c120);
        $r->c121 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c121, $other->c121);
        $r->c122 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c122, $other->c122);
        $r->c123 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c123, $other->c123);
        $r->c124 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c124, $other->c124);
        $r->c125 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c125, $other->c125);
        $r->c126 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c126, $other->c126);
        $r->c127 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c127, $other->c127);
        $r->c128 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c128, $other->c128);
        $r->c129 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c129, $other->c129);
        $r->c130 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c130, $other->c130);
        $r->c131 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c131, $other->c131);
        $r->c132 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c132, $other->c132);
        $r->c133 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c133, $other->c133);
        $r->c134 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c134, $other->c134);
        $r->c135 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c135, $other->c135);
        $r->c136 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c136, $other->c136);
        $r->c137 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c137, $other->c137);
        $r->c138 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c138, $other->c138);
        $r->c139 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c139, $other->c139);
        $r->c140 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c140, $other->c140);
        $r->c141 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c141, $other->c141);
        $r->c142 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c142, $other->c142);
        $r->c143 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c143, $other->c143);
        $r->c144 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c144, $other->c144);
        $r->c145 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c145, $other->c145);
        $r->c146 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c146, $other->c146);
        $r->c147 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c147, $other->c147);
        $r->c148 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c148, $other->c148);
        $r->c149 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c149, $other->c149);
        $r->c150 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c150, $other->c150);
        $r->c151 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c151, $other->c151);
        $r->c152 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c152, $other->c152);
        $r->c153 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c153, $other->c153);
        $r->c154 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c154, $other->c154);
        $r->c155 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c155, $other->c155);
        $r->c156 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c156, $other->c156);
        $r->c157 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c157, $other->c157);
        $r->c158 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c158, $other->c158);
        $r->c159 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c159, $other->c159);
        $r->c160 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c160, $other->c160);
        $r->c161 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c161, $other->c161);
        $r->c162 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c162, $other->c162);
        $r->c163 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c163, $other->c163);
        $r->c164 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c164, $other->c164);
        $r->c165 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c165, $other->c165);
        $r->c166 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c166, $other->c166);
        $r->c167 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c167, $other->c167);
        $r->c168 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c168, $other->c168);
        $r->c169 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c169, $other->c169);
        $r->c170 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c170, $other->c170);
        $r->c171 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c171, $other->c171);
        $r->c172 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c172, $other->c172);
        $r->c173 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c173, $other->c173);
        $r->c174 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c174, $other->c174);
        $r->c175 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c175, $other->c175);
        $r->c176 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c176, $other->c176);
        $r->c177 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c177, $other->c177);
        $r->c178 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c178, $other->c178);
        $r->c179 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c179, $other->c179);
        $r->c180 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c180, $other->c180);
        $r->c181 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c181, $other->c181);
        $r->c182 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c182, $other->c182);
        $r->c183 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c183, $other->c183);
        $r->c184 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c184, $other->c184);
        $r->c185 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c185, $other->c185);
        $r->c186 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c186, $other->c186);
        $r->c187 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c187, $other->c187);
        $r->c188 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c188, $other->c188);
        $r->c189 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c189, $other->c189);
        $r->c190 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c190, $other->c190);
        $r->c191 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c191, $other->c191);
        $r->c192 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c192, $other->c192);
        $r->c193 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c193, $other->c193);
        $r->c194 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c194, $other->c194);
        $r->c195 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c195, $other->c195);
        $r->c196 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c196, $other->c196);
        $r->c197 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c197, $other->c197);
        $r->c198 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c198, $other->c198);
        $r->c199 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c199, $other->c199);
        $r->c200 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c200, $other->c200);
        $r->c201 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c201, $other->c201);
        $r->c202 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c202, $other->c202);
        $r->c203 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c203, $other->c203);
        $r->c204 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c204, $other->c204);
        $r->c205 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c205, $other->c205);
        $r->c206 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c206, $other->c206);
        $r->c207 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c207, $other->c207);
        $r->c208 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c208, $other->c208);
        $r->c209 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c209, $other->c209);
        $r->c210 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c210, $other->c210);
        $r->c211 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c211, $other->c211);
        $r->c212 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c212, $other->c212);
        $r->c213 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c213, $other->c213);
        $r->c214 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c214, $other->c214);
        $r->c215 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c215, $other->c215);
        $r->c216 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c216, $other->c216);
        $r->c217 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c217, $other->c217);
        $r->c218 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c218, $other->c218);
        $r->c219 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c219, $other->c219);
        $r->c220 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c220, $other->c220);
        $r->c221 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c221, $other->c221);
        $r->c222 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c222, $other->c222);
        $r->c223 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c223, $other->c223);
        $r->c224 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c224, $other->c224);
        $r->c225 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c225, $other->c225);
        $r->c226 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c226, $other->c226);
        $r->c227 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c227, $other->c227);
        $r->c228 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c228, $other->c228);
        $r->c229 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c229, $other->c229);
        $r->c230 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c230, $other->c230);
        $r->c231 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c231, $other->c231);
        $r->c232 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c232, $other->c232);
        $r->c233 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c233, $other->c233);
        $r->c234 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c234, $other->c234);
        $r->c235 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c235, $other->c235);
        $r->c236 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c236, $other->c236);
        $r->c237 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c237, $other->c237);
        $r->c238 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c238, $other->c238);
        $r->c239 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c239, $other->c239);
        $r->c240 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c240, $other->c240);
        $r->c241 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c241, $other->c241);
        $r->c242 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c242, $other->c242);
        $r->c243 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c243, $other->c243);
        $r->c244 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c244, $other->c244);
        $r->c245 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c245, $other->c245);
        $r->c246 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c246, $other->c246);
        $r->c247 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c247, $other->c247);
        $r->c248 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c248, $other->c248);
        $r->c249 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c249, $other->c249);
        $r->c250 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c250, $other->c250);
        $r->c251 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c251, $other->c251);
        $r->c252 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c252, $other->c252);
        $r->c253 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c253, $other->c253);
        $r->c254 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c254, $other->c254);
        $r->c255 = ParagonIE_Sodium_Core_MLDSA_Field::mul($this->c255, $other->c255);
        return $r;
    }
    #[ReturnTypeWillChange]
    /**
     * @param mixed $offset
     * @return bool
     */
    #[\Override]
    public function offsetExists($offset)
    {
        return $offset >= 0 && $offset < 256;
    }
    #[ReturnTypeWillChange]
    /**
     * @param mixed $offset
     * @return int
     */
    #[\Override]
    public function offsetGet($offset)
    {
        return $this->{'c' . $offset};
    }
    #[ReturnTypeWillChange]
    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    #[\Override]
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            throw new SodiumException('Cannot append to a polynomial');
        }
        $this->{'c' . $offset} = $value;
    }
    #[ReturnTypeWillChange]
    /**
     * @param mixed $offset
     *
     * @return void
     */
    #[\Override]
    public function offsetUnset($offset)
    {
        $this->{'c' . $offset} = 0;
    }
}
