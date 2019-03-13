<template>
    <td @click="clicked">{{printValue()}}</td>
</template>

<script>
    import {eventBus} from '../app';
    export default {
        props: [
            'corX', 'corY', 'myTurn', 'mySide'
        ],
        data() {
            return {
                value: undefined
            }
        },
        methods:{
            clicked(){
                if (this.value === undefined && this.myTurn === true) {
                    this.value = this.mySide;
                    eventBus.$emit('myStep',[this.corX, this.corY]);
                }
            },
            printValue(){
                if (this.value === true){
                    return 'X';
                }
                else if(this.value === false){
                    return 'O';
                }
                return '';
            }
        },
        created(){
            eventBus.$on('enemyStep', (data) =>{
                if (data[0] !== this.corX || data[1] !== this.corY)
                    return;
                this.value = !this.mySide;
            });
        }
    }
</script>
