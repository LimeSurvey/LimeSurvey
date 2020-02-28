<template>
    <div 
        class="typeCounter--container"
        :style="{width: size+'px', height: size+'px'}"
    >
        <svg :height="size" :width="size" >
            <circle 
                class="typeCounter--circle" 
                :cx="size" 
                :cy="size" 
                :r="(size/2)-(strokeSize+1)"
                :stroke-width="strokeSize"
                :stroke-dasharray="circumference"
                :stroke="strokeColor"
                :style="gradiantValue"
            />
        </svg>
        <div class="typeCounter--innerCircle">
            {{valid ? (maxValue - countable) : 'X'}}
        </div>
  </div>
</template>

<script>
export default {
    name: "type-counter",
    props: {
        valid: {type: Boolean, required: true},
        countable: {type: Number, required: true},
        maxValue: {type: Number, required: true},
        size: {type: Number, default: 32},
        strokeSize: {type: Number, default: 2}
    },
    data(){
        return {
            mainColor: '#888888',
            mainDarkerColor: '#777777',
            mainWarningColor: '#aa7777',
            mainDangerColor: '#ff7777'
        }
    },
    computed: {
        circumference(){
            return Math.ceil((this.size-4)*Math.PI);
        },
        gradiantValue() {
            return this.countable > 0 && this.valid
              ? {'stroke-dashoffset': (this.circumference-((this.countable/this.maxValue)*this.circumference))}
              : {'stroke-dashoffset': 0};
        },
        strokeColor() {
            if (this.countable == this.maxValue || this.countable == 0 || !this.valid) {
                return this.mainDangerColor;
            }

            if (this.countable > Math.floor(this.maxValue*0.75)) {
                return this.mainWarningColor;
            }

            if (this.countable > (this.maxValue/2)) {
                return this.mainDarkerColor;
            }

            return this.mainColor;
        }
    },
    mounted() {
        this.mainColor = getComputedStyle(document.documentElement)
            .getPropertyValue('--LS-admintheme-lightbasecolor');
        this.mainDarkerColor = getComputedStyle(document.documentElement)
            .getPropertyValue('--LS-admintheme-basecolor');
        this.mainWarningColor = getComputedStyle(document.documentElement)
            .getPropertyValue('--LS-admintheme-hovercolor'); 
        this.mainDangerColor = getComputedStyle(document.documentElement)
            .getPropertyValue('--LS-admintheme-dangercolor');
    }
}
</script>

<style lang="scss" scoped>
.typeCounter--container {
    position: absolute;
    padding: 0;
    right: 0;
    top: 0;
    border-top-right-radius: 4px;
    border-bottom-right-radius: 4px;
    background: #f5f5f5;
    margin: 1px;
}
.typeCounter--circle  {
    fill-opacity: 0;

    .typeCounter--innerCircle {
        border-radius: 100px;
        height: 20px;
        width: 20px;
        font-size: 12px;
        box-sizing: border-box;
    }
}

svg, .typeCounter--circle, .typeCounter--innerCircle {
	position: absolute;
	top: 50%;
	left: 50%;
	transform: translate(-50%,-50%);
}



</style>