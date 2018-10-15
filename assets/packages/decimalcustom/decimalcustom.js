
/**
 * Helper Object to get closer to phps mathematical functions
 */
Decimal.asNum = {
    abs : function() {
        try {
            return Decimal.abs.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    acos : function() {
        try {
            return Decimal.acos.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    acosh : function() {
        try {
            return Decimal.acosh.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    add : function() {
        try {
            return Decimal.add.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    asin : function() {
        try {
            return Decimal.asin.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    asinh : function() {
        try {
            return Decimal.asinh.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    atan : function() {
        try {
            return Decimal.atan.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    atanh : function() {
        try {
            return Decimal.atanh.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    atan2 : function() {
        try {
            return Decimal.atan2.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    cbrt : function() {
        try {
            return Decimal.cbrt.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    ceil : function() {
        try {
            return Decimal.ceil.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    clone : function() {
        try {
            return Decimal.clone.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    cos : function() {
        try {
            return Decimal.cos.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    cosh : function() {
        try {
            return Decimal.cosh.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    div : function() {
        try {
            return Decimal.div.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    exp : function() {
        try {
            return Decimal.exp.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    floor : function() {
        try {
            return Decimal.floor.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    hypot : function() {
        try {
            return Decimal.hypot.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    isDecimal : function() {
        try {
            return Decimal.isDecimal.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    ln : function() {
        try {
            return Decimal.ln.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    log : function() {
        try {
            return Decimal.log.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    log2 : function() {
        try {
            return Decimal.log2.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    log10 : function() {
        try {
            return Decimal.log10.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    max : function() {
        try {
            return Decimal.max.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    min : function() {
        try {
            return Decimal.min.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    mod : function() {
        try {
            return Decimal.mod.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    mul : function() {
        try {
            return Decimal.mul.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    noConflict : function() {
        try {
            return Decimal.noConflict.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    pow : function() {
        try {
            return Decimal.pow.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    random : function() {
        try {
            return Decimal.random.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    round : function() {
        try {
            return Decimal.round.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    set : function() {
        try {
            return Decimal.set.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    sign : function() {
        try {
            return Decimal.sign.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    sin : function() {
        try {
            return Decimal.sin.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    sinh : function() {
        try {
            return Decimal.sinh.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    sqrt : function() {
        try {
            return Decimal.sqrt.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    sub : function() {
        try {
            return Decimal.sub.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    tan : function() {
        try {
            return Decimal.tan.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    tanh : function() {
        try {
            return Decimal.tanh.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    },
    trunc : function() {
        try {
            return Decimal.trunc.apply(Decimal, arguments).toNumber();
        } catch(e) {
            console.ls.warn('DecimalIssue', e);
            return false;
        }
    }
};
