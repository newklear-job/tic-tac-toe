<template>
    <table id='board'>
        <tr>
            <element-component :corX="1" :corY="1" :myTurn="myTurn" :mySide="mySide"></element-component>
            <element-component :corX="1" :corY="2" :myTurn="myTurn" :mySide="mySide"></element-component>
            <element-component :corX="1" :corY="3" :myTurn="myTurn" :mySide="mySide"></element-component>
        </tr>
        <tr>
            <element-component :corX="2" :corY="1" :myTurn="myTurn" :mySide="mySide"></element-component>
            <element-component :corX="2" :corY="2" :myTurn="myTurn" :mySide="mySide"></element-component>
            <element-component :corX="2" :corY="3" :myTurn="myTurn" :mySide="mySide"></element-component>
        </tr>
        <tr>
            <element-component :corX="3" :corY="1" :myTurn="myTurn" :mySide="mySide"></element-component>
            <element-component :corX="3" :corY="2" :myTurn="myTurn" :mySide="mySide"></element-component>
            <element-component :corX="3" :corY="3" :myTurn="myTurn" :mySide="mySide"></element-component>
        </tr>
    </table>
</template>

<script>
    import element from './element.vue';
    export default {
        props:[
            'myTurn', 'mySide'
        ],
        components: {
            'element-component': element
        }
    }
</script>

<style>
    * {
        margin: 0;
        padding: 0;
    }
    table {
        border-collapse: collapse;
        border-spacing: 0;
    }
    #board {
        padding: 0px;
        margin: 0 auto;
        margin-top: 25px;
    }
    #board tr td {
        width: 80px;
        height: 80px;
        border: 1px solid #1c1c1c;
        font-family: Helvetica;
        font-size: 30px;
        text-align: center;
    }
    #board tr td:hover {
        background: #e4e4e4;
        cursor: pointer;
    }
</style>